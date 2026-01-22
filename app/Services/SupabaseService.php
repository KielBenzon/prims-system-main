<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class SupabaseService
{
    private $supabaseUrl;
    private $supabaseKey;
    
    public function __construct()
    {
        $this->supabaseUrl = env('SUPABASE_URL');
        $this->supabaseKey = env('SUPABASE_SERVICE_ROLE_KEY') ?: env('SUPABASE_ANON_KEY');
    }
    
    public function query($table, $select = '*', $filters = [])
    {
        $url = $this->supabaseUrl . "/rest/v1/$table?select=$select";
        
        // Add filters
        foreach ($filters as $key => $value) {
            $url .= "&$key=eq.$value";
        }
        
        $response = Http::timeout(30)
            ->withHeaders([
                'apikey' => $this->supabaseKey,
                'Authorization' => 'Bearer ' . $this->supabaseKey,
                'Content-Type' => 'application/json'
            ])
            ->get($url);
        
        if ($response->successful()) {
            return $response->json();
        }
        
        throw new \Exception("Supabase query failed: HTTP " . $response->status() . " - " . $response->body());
    }
    
    public function insert($table, $data)
    {
        $url = $this->supabaseUrl . "/rest/v1/$table";
        
        $response = Http::timeout(30)
            ->withHeaders([
                'apikey' => $this->supabaseKey,
                'Authorization' => 'Bearer ' . $this->supabaseKey,
                'Content-Type' => 'application/json',
                'Prefer' => 'return=representation'
            ])
            ->post($url, $data);
        
        if ($response->status() === 201) {
            return $response->json();
        }
        
        throw new \Exception("Supabase insert failed: HTTP " . $response->status() . " - " . $response->body());
    }
    
    /**
     * Check if a user already exists by email
     */
    public function userExists($email)
    {
        try {
            $users = $this->query('tusers', 'id', ['email' => $email]);
            return count($users) > 0;
        } catch (\Exception $e) {
            Log::error('Supabase userExists check failed: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Create a new user via Supabase REST API
     */
    public function createUser($userData)
    {
        try {
            return $this->insert('tusers', $userData);
        } catch (\Exception $e) {
            Log::error('Supabase createUser failed: ' . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Get user by email
     */
    public function getUserByEmail($email)
    {
        try {
            $users = $this->query('tusers', '*', ['email' => $email]);
            return count($users) > 0 ? $users[0] : null;
        } catch (\Exception $e) {
            Log::error('Supabase getUserByEmail failed: ' . $e->getMessage());
            return null;
        }
    }
}