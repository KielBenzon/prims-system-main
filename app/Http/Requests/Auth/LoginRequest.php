<?php

namespace App\Http\Requests\Auth;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use App\Services\SupabaseService;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Log;

class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ];
    }

    /**
     * Attempt to authenticate the request's credentials.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        try {
            // Try standard Laravel authentication first
            if (Auth::attempt($this->only('email', 'password'), $this->boolean('remember'))) {
                RateLimiter::clear($this->throttleKey());
                return;
            }
        } catch (Exception $e) {
            Log::error('Database authentication failed, trying Supabase API: ' . $e->getMessage());
            
            // Fallback to Supabase REST API authentication
            try {
                $supabaseService = new SupabaseService();
                $user = $supabaseService->getUserByEmail($this->input('email'));
                
                if ($user && Hash::check($this->input('password'), $user['password'])) {
                    // Create a temporary user object for authentication
                    $userModel = new User();
                    $userModel->id = $user['id'];
                    $userModel->name = $user['name'];
                    $userModel->email = $user['email'];
                    $userModel->password = $user['password'];
                    $userModel->role = $user['role'];
                    $userModel->exists = true;
                    
                    Auth::login($userModel, $this->boolean('remember'));
                    RateLimiter::clear($this->throttleKey());
                    return;
                }
            } catch (Exception $supabaseException) {
                Log::error('Supabase authentication also failed: ' . $supabaseException->getMessage());
            }
        }
        
        RateLimiter::hit($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => trans('auth.failed'),
        ]);
    }

    /**
     * Ensure the login request is not rate limited.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Get the rate limiting throttle key for the request.
     */
    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->string('email')).'|'.$this->ip());
    }
}
