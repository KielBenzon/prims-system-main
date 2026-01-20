<?php

namespace App\Http\Controllers;

use App\Constant\MyConstant;
use App\Models\Document;
use App\Models\Donation;
use App\Models\Payment;
use App\Models\Mail;
use App\Models\Priest;
use App\Models\Request;
use App\Services\RequestService;
use App\Services\useValidator;
use Illuminate\Http\Request as RequestFacades;

class AdminController extends Controller
{
    public function index()
    {
        try {
            // Use executeWithFallback for all database operations
            $documents = $this->executeWithFallback(function () {
                return Document::count();
            }, 0);
            
            $donations = $this->executeWithFallback(function () {
                return Donation::count();
            }, 0);
            
            $payment = $this->executeWithFallback(function () {
                return Payment::count();
            }, 0);
            
            $mails = $this->executeWithFallback(function () {
                return Mail::count();
            }, 0);
            
            $priests = $this->executeWithFallback(function () {
                return Priest::count();
            }, 0);
            
            $requests = $this->executeWithFallback(function () {
                return Request::all();
            }, collect([]));
            
            // If database failed, try direct Supabase API call
            if ($requests->isEmpty()) {
                try {
                    $supabaseUrl = env('SUPABASE_URL', 'https://lruvxbhfiogqolwztovs.supabase.co');
                    $supabaseKey = env('SUPABASE_ANON_KEY', 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6ImxydXZ4YmhmaW9ncW9sd3p0b3ZzIiwicm9sZSI6ImFub24iLCJpYXQiOjE3MzY1NTU0MjIsImV4cCI6MjA1MjEzMTQyMn0.J7Wkej_K8_cY5lZ0F9SqYIgVEYtFP0O9IkJBhVKQJEA');
                    
                    $url = $supabaseUrl . '/rest/v1/trequests';
                    
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
                    \Illuminate\Support\Facades\Log::error('AdminController: Supabase API failed: ' . $e->getMessage());
                    $requests = collect([]);
                }
            }
            
        } catch (\Exception $e) {
            // Ultimate fallback with logging
            \Illuminate\Support\Facades\Log::error('AdminController dashboard error: ' . $e->getMessage());
            
            $documents = 0;
            $donations = 0;
            $payment = 0;
            $mails = 0;
            $priests = 0;
            $requests = collect([]);
            
            session()->flash('db_error', 'Database connection temporarily unavailable.');
        }

        // Status counts for requests
        $pending = $requests->where('status', 'Pending')->count();
        $approved = $requests->where('status', 'Approved')->count();
        $declined = $requests->where('status', 'Declined')->count();
        $completed = $requests->where('status', 'Completed')->count();

        // Calculate monthly donation total with fallback
        $monthlyTotal = $this->executeWithFallback(function () {
            return Donation::whereMonth('donation_date', now()->month)
                         ->whereYear('donation_date', now()->year)
                         ->sum('amount');
        }, 0.00);
        
        // If database failed for donations, try Supabase API
        if ($monthlyTotal == 0.00) {
            try {
                $supabaseUrl = env('SUPABASE_URL', 'https://lruvxbhfiogqolwztovs.supabase.co');
                $supabaseKey = env('SUPABASE_ANON_KEY', 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6ImxydXZ4YmhmaW9ncW9sd3p0b3ZzIiwicm9sZSI6ImFub24iLCJpYXQiOjE3MzY1NTU0MjIsImV4cCI6MjA1MjEzMTQyMn0.J7Wkej_K8_cY5lZ0F9SqYIgVEYtFP0O9IkJBhVKQJEA');
                
                $currentMonth = now()->month;
                $currentYear = now()->year;
                
                $url = $supabaseUrl . '/rest/v1/tdonations';
                
                $response = \Illuminate\Support\Facades\Http::withHeaders([
                    'apikey' => $supabaseKey,
                    'Authorization' => 'Bearer ' . $supabaseKey,
                    'Content-Type' => 'application/json'
                ])->get($url);
                
                if ($response->successful()) {
                    $donations = collect($response->json());
                    $monthlyTotal = $donations
                        ->filter(function($donation) use ($currentMonth, $currentYear) {
                            $donationDate = \Carbon\Carbon::parse($donation['donation_date']);
                            return $donationDate->month == $currentMonth && $donationDate->year == $currentYear;
                        })
                        ->sum('amount');
                }
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('AdminController: Donation Supabase API failed: ' . $e->getMessage());
            }
        }

        // Calculate monthly payment total with fallback
        $monthlyPayment = $this->executeWithFallback(function () {
            return Payment::whereMonth('payment_date', now()->month)
                         ->whereYear('payment_date', now()->year)
                         ->sum('amount');
        }, 0.00);
        
        // If database failed for payments, try Supabase API
        if ($monthlyPayment == 0.00) {
            try {
                $supabaseUrl = env('SUPABASE_URL', 'https://lruvxbhfiogqolwztovs.supabase.co');
                $supabaseKey = env('SUPABASE_ANON_KEY', 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6ImxydXZ4YmhmaW9ncW9sd3p0b3ZzIiwicm9sZSI6ImFub24iLCJpYXQiOjE3MzY1NTU0MjIsImV4cCI6MjA1MjEzMTQyMn0.J7Wkej_K8_cY5lZ0F9SqYIgVEYtFP0O9IkJBhVKQJEA');
                
                $currentMonth = now()->month;
                $currentYear = now()->year;
                
                $url = $supabaseUrl . '/rest/v1/tpayments';
                
                $response = \Illuminate\Support\Facades\Http::withHeaders([
                    'apikey' => $supabaseKey,
                    'Authorization' => 'Bearer ' . $supabaseKey,
                    'Content-Type' => 'application/json'
                ])->get($url);
                
                if ($response->successful()) {
                    $payments = collect($response->json());
                    $monthlyPayment = $payments
                        ->filter(function($payment) use ($currentMonth, $currentYear) {
                            $paymentDate = \Carbon\Carbon::parse($payment['payment_date']);
                            return $paymentDate->month == $currentMonth && $paymentDate->year == $currentYear;
                        })
                        ->sum('amount');
                }
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('AdminController: Payment Supabase API failed: ' . $e->getMessage());
            }
        }

        // Pass all the necessary data to the view
        return view('admin.dashboard', compact(
            'documents', 'donations', 'mails', 'priests', 'requests', 
            'pending', 'approved', 'declined', 'completed', 'monthlyTotal', 'monthlyPayment'
        ));
    }

    public function requestBaptismal(RequestFacades $request)
    {
        $result = (new RequestService(new useValidator))
            ->requestBaptismal($request);

        if ($result['error_code'] !== MyConstant::SUCCESS_CODE) {
            return response()->json([
                'error_code' => $result['error_code'],
                'message' => $result['message'],
            ], $result['status_code']);
        }

        return redirect()->back()->with([
            'error_code' => $result['error_code'],
            'message' => $result['message'],
        ]);
    }
}
