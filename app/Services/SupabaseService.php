<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

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
        
        $headers = [
            'apikey: ' . $this->supabaseKey,
            'Authorization: Bearer ' . $this->supabaseKey,
            'Content-Type: application/json'
        ];
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode === 200) {
            return json_decode($response, true);
        }
        
        throw new \Exception("Supabase query failed: HTTP $httpCode - $response");
    }
    
    public function insert($table, $data)
    {
        $url = $this->supabaseUrl . "/rest/v1/$table";
        
        $headers = [
            'apikey: ' . $this->supabaseKey,
            'Authorization: Bearer ' . $this->supabaseKey,
            'Content-Type: application/json',
            'Prefer: return=representation'
        ];
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode === 201) {
            return json_decode($response, true);
        }
        
        throw new \Exception("Supabase insert failed: HTTP $httpCode - $response");
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