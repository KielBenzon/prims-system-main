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
        $announcements = Announcement::all();
    } catch (\Exception $e) {
        // Temporary fallback while database connection is being fixed
        $announcements = collect([]); // Empty collection
        
        // Log the error for debugging
        Log::error('Database connection error in HomeController: ' . $e->getMessage());
        
        // Optional: Add a flash message for debugging
        session()->flash('db_error', 'Database connection temporarily unavailable. Using fallback data.');
    }

    // Ipakita ang public homepage kahit naka-login
    return view('welcome', compact('announcements'));
}
}
