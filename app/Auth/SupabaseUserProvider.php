<?php

namespace App\Auth;

use Illuminate\Auth\EloquentUserProvider;
use Illuminate\Contracts\Auth\Authenticatable;
use App\Services\SupabaseService;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Exception;

class SupabaseUserProvider extends EloquentUserProvider
{
    protected $supabaseService;

    public function __construct($hasher, $model)
    {
        parent::__construct($hasher, $model);
        $this->supabaseService = new SupabaseService();
    }

    /**
     * Retrieve a user by their unique identifier.
     */
    public function retrieveById($identifier)
    {
        try {
            // Try standard Eloquent first
            return parent::retrieveById($identifier);
        } catch (Exception $e) {
            Log::warning('Database retrieval failed, using Supabase API: ' . $e->getMessage());
            
            try {
                // Fallback to Supabase API
                $users = $this->supabaseService->query('tusers', '*', ['id' => $identifier]);
                
                if (!empty($users)) {
                    $userData = $users[0];
                    $user = new User();
                    $user->id = $userData['id'];
                    $user->name = $userData['name'];
                    $user->email = $userData['email'];
                    $user->password = $userData['password'];
                    $user->role = $userData['role'];
                    $user->exists = true;
                    return $user;
                }
            } catch (Exception $supabaseException) {
                Log::error('Supabase user retrieval failed: ' . $supabaseException->getMessage());
            }
            
            return null;
        }
    }

    /**
     * Retrieve a user by their unique identifier and "remember me" token.
     */
    public function retrieveByToken($identifier, $token)
    {
        try {
            return parent::retrieveByToken($identifier, $token);
        } catch (Exception $e) {
            Log::warning('Database token retrieval failed: ' . $e->getMessage());
            
            // For now, fallback to basic retrieval
            // Remember tokens would need special handling in Supabase
            return $this->retrieveById($identifier);
        }
    }

    /**
     * Retrieve a user by the given credentials.
     */
    public function retrieveByCredentials(array $credentials)
    {
        try {
            return parent::retrieveByCredentials($credentials);
        } catch (Exception $e) {
            Log::warning('Database credential retrieval failed, using Supabase API: ' . $e->getMessage());
            
            try {
                if (isset($credentials['email'])) {
                    $user = $this->supabaseService->getUserByEmail($credentials['email']);
                    
                    if ($user) {
                        $userModel = new User();
                        $userModel->id = $user['id'];
                        $userModel->name = $user['name'];
                        $userModel->email = $user['email'];
                        $userModel->password = $user['password'];
                        $userModel->role = $user['role'];
                        $userModel->exists = true;
                        return $userModel;
                    }
                }
            } catch (Exception $supabaseException) {
                Log::error('Supabase credential retrieval failed: ' . $supabaseException->getMessage());
            }
            
            return null;
        }
    }

    /**
     * Validate a user against the given credentials.
     */
    public function validateCredentials(Authenticatable $user, array $credentials)
    {
        return $this->hasher->check($credentials['password'], $user->getAuthPassword());
    }
}