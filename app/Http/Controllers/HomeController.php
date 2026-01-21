<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class HomeController extends Controller
{
    public function index()
{
    try {
        // Kunin ang lahat ng announcements
        $announcements = Announcement::with('priest')->orderBy('created_at', 'desc')->get();
    } catch (\Exception $e) {
        // Fallback to Supabase REST API
        $announcements = collect([]);
        
        try {
            $supabaseUrl = env('SUPABASE_URL');
            $supabaseKey = env('SUPABASE_SERVICE_ROLE_KEY');
            
            $response = \Illuminate\Support\Facades\Http::withHeaders([
                'apikey' => $supabaseKey,
                'Authorization' => 'Bearer ' . $supabaseKey,
            ])->get($supabaseUrl . '/rest/v1/tannouncements?select=*,priest:tpriests(*)&order=created_at.desc');
            
            if ($response->successful()) {
                $announcements = collect($response->json())->map(function ($announcement) {
                    $obj = (object) $announcement;
                    // Convert priest array to object
                    if (isset($announcement['priest'])) {
                        $obj->priest = (object) $announcement['priest'];
                    }
                    return $obj;
                });
            }
        } catch (\Exception $apiException) {
            Log::error('Failed to fetch announcements from Supabase: ' . $apiException->getMessage());
        }
        
        // Log the error for debugging
        Log::error('Database connection error in HomeController: ' . $e->getMessage());
    }

    // Ipakita ang public homepage kahit naka-login
    return view('welcome', compact('announcements'));
}
}
