<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;
use App\Services\SupabaseService;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        try {
            // Test database connection before validation
            DB::connection()->getPdo();
            
            // Standard validation with database unique check
            $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class],
                'password' => ['required', 'confirmed', Rules\Password::defaults()],
                'role' => ['required', 'string'],
            ]);
            
        } catch (Exception $e) {
            Log::error('Database connection failed during registration: ' . $e->getMessage());
            
            // Fallback validation without unique check when database is unavailable
            $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'string', 'lowercase', 'email', 'max:255'],
                'password' => ['required', 'confirmed', Rules\Password::defaults()],
                'role' => ['required', 'string'],
            ]);
            
            // Check for existing email manually using direct query or API
            try {
                $existingUser = User::where('email', $request->email)->first();
                if ($existingUser) {
                    return back()->withErrors(['email' => 'The email has already been taken.'])->withInput();
                }
            } catch (Exception $checkException) {
                Log::warning('Database check failed, trying Supabase API: ' . $checkException->getMessage());
                
                // Fallback to Supabase REST API
                $supabaseService = new SupabaseService();
                if ($supabaseService->userExists($request->email)) {
                    return back()->withErrors(['email' => 'The email has already been taken.'])->withInput();
                }
            }
        }

        try {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => $request->role,
            ]);

            event(new Registered($user));
            Auth::login($user);

            return redirect(route('dashboard', absolute: false));
            
        } catch (Exception $e) {
            Log::error('User creation failed via Eloquent: ' . $e->getMessage());
            
            // Fallback to Supabase REST API for user creation
            try {
                $supabaseService = new SupabaseService();
                $userData = [
                    'name' => $request->name,
                    'email' => $request->email,
                    'password' => Hash::make($request->password),
                    'role' => $request->role,
                ];
                
                $createdUser = $supabaseService->createUser($userData);
                
                if ($createdUser) {
                    // Create a temporary user object for authentication
                    $user = new User();
                    $user->id = $createdUser[0]['id'];
                    $user->name = $createdUser[0]['name'];
                    $user->email = $createdUser[0]['email'];
                    $user->role = $createdUser[0]['role'];
                    $user->exists = true;
                    
                    event(new Registered($user));
                    Auth::login($user);
                    
                    return redirect(route('dashboard', absolute: false));
                } else {
                    throw new Exception('Supabase user creation failed');
                }
                
            } catch (Exception $supabaseException) {
                Log::error('Supabase user creation also failed: ' . $supabaseException->getMessage());
                return back()->withErrors([
                    'email' => 'Registration failed due to database connection issues. Please try again later.',
                ])->withInput();
            }
        }
    }
}
