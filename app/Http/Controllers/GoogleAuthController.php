<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Throwable;

class GoogleAuthController extends Controller
{
    /**
     * Redirect the user to Google’s OAuth page.
     */
    public function redirect()
    {
        try {
            return Socialite::driver('google')->redirect();
        } catch (\Exception $e) {
            return redirect()->route('login')->withErrors(['error' => 'Google authentication is not properly configured.']);
        }
    }

    /**
     * Handle the callback from Google.
     */
    // public function callbackGoogle(){
    //     try {
    //         // dd(Socialite::driver('google')->user());

    //         $google_user = Socialite::driver('google')->stateless()->user();

    //         $user = User::where('google_id', $google_user->getId())->first();
    //         // dd($google_user);

    //         if (!$user) {
    //             $new_user = User::create([
    //                 'name' => $google_user->getName(),
    //                 'email' => $google_user->getEmail(),
    //                 'google_id' => $google_user->getId(),
    //                 // 'role' => ''
    //             ]);
    //             // dd($new_user);

    //             Auth::login($new_user);
    //             return redirect()->intended('dashboard');
    //         }else{
    //             // dd('else');
    //             Auth::login($user);
    //             return redirect()->intended('dashboard');
    //         }
    //     } catch (\Throwable $th) {
    //         dd('something went wrong! '. $th->getMessage());
    //     }
    // }

    public function callbackGoogle()
    {
        try {
            $google_user = Socialite::driver('google')->user();

            try {
                // Check if a user with the same email already exists
                $existingUser = User::where('email', $google_user->getEmail())->first();

            if ($existingUser) {
                // Update the google_id if it's not already set
                if (!$existingUser->google_id) {
                    $existingUser->google_id = $google_user->getId();
                    $existingUser->save();
                }

                Auth::login($existingUser);
            } else {
                // Create a new user if no matching email is found
                $new_user = User::create([
                    'name' => $google_user->getName(),
                    'email' => $google_user->getEmail(),
                    'google_id' => $google_user->getId(),
                    'password' => bcrypt(Str::random(16)), // optional fallback password
                    'role' => 'Parishioner' // or default role
                ]);

                Auth::login($new_user);
            }
        } catch (\Exception $e) {
            // Database connection error - redirect with error message
            return redirect()->route('login')->with('error', 'Database connection error. Please try again later or contact support.');
        }

        return redirect()->intended('dashboard');

    } catch (\Throwable $th) {
        return redirect()->route('login')->with('error', 'Google authentication failed: ' . $th->getMessage());
    }
}

}