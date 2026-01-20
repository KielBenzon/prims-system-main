<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\Donation;
use App\Models\Mail;
use App\Models\Priest;
use App\Models\Request;
use Illuminate\Support\Facades\Auth;

class ParishionerController extends Controller
{
    public function index()
    {
        $currentUser = Auth::user();
        
        try {
            $documents = $this->executeWithFallback(function () {
                return Document::count();
            }, 0);
            $donations = $this->executeWithFallback(function () {
                return Donation::count();
            }, 0);
            $mails = $this->executeWithFallback(function () {
                return Mail::count();
            }, 0);
            $priests = $this->executeWithFallback(function () {
                return Priest::count();
            }, 0);
            
            // Filter requests by current user only
            $requests = $this->executeWithFallback(function () use ($currentUser) {
                return Request::where('requested_by', $currentUser->id)->get();
            }, collect([]));
            
            // If database failed, try direct Supabase API call
            if ($requests->isEmpty()) {
                try {
                    $supabaseUrl = env('SUPABASE_URL', 'https://lruvxbhfiogqolwztovs.supabase.co');
                    $supabaseKey = env('SUPABASE_ANON_KEY', 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6ImxydXZ4YmhmaW9ncW9sd3p0b3ZzIiwicm9sZSI6ImFub24iLCJpYXQiOjE3MzY1NTU0MjIsImV4cCI6MjA1MjEzMTQyMn0.J7Wkej_K8_cY5lZ0F9SqYIgVEYtFP0O9IkJBhVKQJEA');
                    
                    $url = $supabaseUrl . '/rest/v1/trequests?requested_by=eq.' . $currentUser->id;
                    
                    $response = \Illuminate\Support\Facades\Http::withHeaders([
                        'apikey' => $supabaseKey,
                        'Authorization' => 'Bearer ' . $supabaseKey,
                        'Content-Type' => 'application/json',
                        'Prefer' => 'return=representation'
                    ])->get($url);
                    
                    if ($response->successful()) {
                        $data = $response->json();
                        $requests = collect($data);
                    }
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::error('ParishionerController: Supabase API failed: ' . $e->getMessage());
                    $requests = collect([]);
                }
            }
        } catch (\Exception $e) {
            // Fallback data while database connection is being fixed
            $documents = 0;
            $donations = 0;
            $mails = 0;
            $priests = 0;
            $requests = collect([]);
            
            session()->flash('db_error', 'Database connection temporarily unavailable.');
        }

        // Now these counts will be accurate for the current user
        $pending = $requests->where('status', 'Pending')->count();
        $approved = $requests->where('status', 'Approved')->count();
        $declined = $requests->where('status', 'Declined')->count();
        $completed = $requests->where('status', 'Completed')->count();

        // Calculate the monthly total donation amount with fallback - for current user only
        $monthlyTotal = 0.00;
        try {
            $monthlyTotal = Donation::where('donor_email', $currentUser->email)
                         ->whereMonth('created_at', now()->month)
                         ->whereYear('created_at', now()->year)
                         ->sum('amount');
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning('Database donation sum failed, trying Supabase', ['error' => $e->getMessage()]);
        }
        
        // Always try Supabase API if database returns 0 or failed
        if ($monthlyTotal == 0.00 || $monthlyTotal === null) {
            try {
                $supabaseUrl = env('SUPABASE_URL');
                $supabaseKey = env('SUPABASE_ANON_KEY');
                
                $currentMonth = now()->month;
                $currentYear = now()->year;
                
                // Query donations by user_id
                $url = $supabaseUrl . '/rest/v1/tdonations?user_id=eq.' . $currentUser->id;
                
                $response = \Illuminate\Support\Facades\Http::withHeaders([
                    'apikey' => $supabaseKey,
                    'Authorization' => 'Bearer ' . $supabaseKey,
                    'Content-Type' => 'application/json'
                ])->get($url);
                
                if ($response->successful()) {
                    $donations = collect($response->json());
                    
                    $filteredDonations = $donations->filter(function($donation) use ($currentMonth, $currentYear) {
                        if (isset($donation['created_at'])) {
                            $createdAt = \Carbon\Carbon::parse($donation['created_at']);
                            return $createdAt->month == $currentMonth && $createdAt->year == $currentYear;
                        }
                        return false;
                    });
                    
                    $monthlyTotal = $filteredDonations->sum('amount');
                    
                    \Illuminate\Support\Facades\Log::info('ParishionerController: Monthly donation total from Supabase: ' . $monthlyTotal, [
                        'total_donations' => $donations->count(),
                        'filtered_count' => $filteredDonations->count(),
                        'user_id' => $currentUser->id
                    ]);
                }
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('ParishionerController: Donation Supabase API failed: ' . $e->getMessage());
            }
        }

        // Pass all the necessary data to the view
        return view('parishioner.dashboard', compact(
            'documents', 'donations', 'mails', 'priests', 'requests', 
            'pending', 'approved', 'declined', 'completed', 'monthlyTotal'
        ));
}

}