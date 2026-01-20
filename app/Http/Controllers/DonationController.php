<?php

namespace App\Http\Controllers;

use App\Constant\MyConstant;
use App\Http\Requests\DonationRequest;
use App\Models\Donation;
use App\Models\Payment;
use App\Models\Notification;
use App\Services\DonationService;
use Illuminate\Support\Facades\Auth;
use App\Services\useValidator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;



class DonationController extends Controller
{
    protected $useValidator;

    public function __construct(useValidator $useValidator)
    {
        $this->useValidator = $useValidator;
    }

    public function index(Request $request)
    {
        $search = $request->query('search');
        $filter = $request->query('filter'); // Check if filter is set

        try {
            $donations = Donation::query()
                ->when($filter === 'monthly', function ($query) {
                    $query->whereBetween('donation_date', [now()->startOfMonth(), now()->endOfMonth()]);
                })
                ->when($search, function ($query, $search) {
                    return $query->where('donor_name', 'like', '%' . $search . '%')
                                 ->orWhere('amount', 'like', '%' . $search . '%')
                                 ->orWhere('donation_date', 'like', '%' . $search . '%');
                })
                ->orderBy('created_at', 'desc')
                ->paginate(10);
        } catch (\Exception $e) {
            Log::warning('Admin donation query failed, using Supabase fallback', ['error' => $e->getMessage()]);
            
            // Supabase fallback
            try {
                $supabaseUrl = env('SUPABASE_URL');
                $supabaseKey = env('SUPABASE_ANON_KEY');
                
                // Get all donations from Supabase
                $url = $supabaseUrl . '/rest/v1/tdonations?select=*&order=created_at.desc';
                
                $response = \Illuminate\Support\Facades\Http::withHeaders([
                    'apikey' => $supabaseKey,
                    'Authorization' => 'Bearer ' . $supabaseKey,
                ])->get($url);
                
                if ($response->successful()) {
                    $data = collect($response->json())->map(function($item) {
                        $donation = (object) $item;
                        // Add default values for fields that don't exist in Supabase
                        $donation->status = $donation->status ?? 'Pending';
                        // Map transaction_url to transaction_id for blade compatibility
                        $donation->transaction_id = $donation->transaction_url ?? $donation->transaction_id ?? null;
                        $donation->note = $donation->note ?? null;
                        return $donation;
                    });
                    
                    // Apply filter for monthly if needed
                    if ($filter === 'monthly') {
                        $data = $data->filter(function($donation) {
                            if (isset($donation->donation_date)) {
                                $donationDate = \Carbon\Carbon::parse($donation->donation_date);
                                return $donationDate->month == now()->month && 
                                       $donationDate->year == now()->year;
                            }
                            return false;
                        });
                    }
                    
                    // Apply search filter if provided
                    if ($search) {
                        $data = $data->filter(function($donation) use ($search) {
                            return stripos($donation->donor_name ?? '', $search) !== false ||
                                   stripos((string)($donation->amount ?? ''), $search) !== false ||
                                   stripos($donation->donation_date ?? '', $search) !== false;
                        });
                    }
                    
                    // Manual pagination
                    $page = request()->get('page', 1);
                    $perPage = 10;
                    $donations = new \Illuminate\Pagination\LengthAwarePaginator(
                        $data->forPage($page, $perPage),
                        $data->count(),
                        $perPage,
                        $page,
                        ['path' => request()->url()]
                    );
                    
                    Log::info('Admin donations loaded from Supabase successfully', ['count' => $data->count()]);
                } else {
                    throw new \Exception('Supabase API failed');
                }
            } catch (\Exception $supabaseError) {
                Log::error('Supabase fallback failed', ['error' => $supabaseError->getMessage()]);
                $donations = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 10, 1);
            }
        }

        return view('admin.donation', compact('donations'));
    }


    public function parishionerIndex()
    {
        $search = request('search');
        
        try {
            $donations = Donation::query()
                ->when($search, function ($query, $search) {
                    return $query->where('donor_name', 'like', '%' . $search . '%')
                        ->orWhere('amount', 'like', '%' . $search . '%')
                        ->orWhere('donation_date', 'like', '%' . $search . '%');
                })
                ->orderBy('created_at', 'desc')
                ->paginate(10);
        } catch (\Exception $e) {
            Log::warning('Donation query failed, using Supabase fallback', ['error' => $e->getMessage()]);
            
            // Supabase fallback
            try {
                $currentUser = Auth::user();
                $supabaseUrl = env('SUPABASE_URL');
                $supabaseKey = env('SUPABASE_ANON_KEY');
                
                $url = $supabaseUrl . '/rest/v1/tdonations?user_id=eq.' . $currentUser->id . '&select=*&order=created_at.desc';
                
                $response = \Illuminate\Support\Facades\Http::withHeaders([
                    'apikey' => $supabaseKey,
                    'Authorization' => 'Bearer ' . $supabaseKey,
                ])->get($url);
                
                if ($response->successful()) {
                    $data = collect($response->json())->map(function($item) {
                        $donation = (object) $item;
                        // Add default values for fields that don't exist in Supabase
                        $donation->status = $donation->status ?? 'Pending';
                        // Map transaction_url to transaction_id for blade compatibility
                        $donation->transaction_id = $donation->transaction_url ?? $donation->transaction_id ?? null;
                        $donation->note = $donation->note ?? null;
                        return $donation;
                    });
                    
                    // Apply search filter if provided
                    if ($search) {
                        $data = $data->filter(function($donation) use ($search) {
                            return stripos($donation->donor_name, $search) !== false ||
                                   stripos($donation->amount, $search) !== false ||
                                   stripos($donation->donation_date ?? '', $search) !== false;
                        });
                    }
                    
                    // Manual pagination
                    $page = request()->get('page', 1);
                    $perPage = 10;
                    $donations = new \Illuminate\Pagination\LengthAwarePaginator(
                        $data->forPage($page, $perPage),
                        $data->count(),
                        $perPage,
                        $page,
                        ['path' => request()->url()]
                    );
                    
                    Log::info('Donations loaded from Supabase successfully');
                } else {
                    throw new \Exception('Supabase API failed');
                }
            } catch (\Exception $supabaseError) {
                Log::error('Supabase fallback failed', ['error' => $supabaseError->getMessage()]);
                $donations = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 10, 1);
            }
        }
        
        return view('parishioner.donation', compact('donations'));
    }
    // public function showDonations()
    // {
    //     $startOfMonth = now()->startOfMonth();
    //     $endOfMonth = now()->endOfMonth();

    //     $monthlyTotal = Donation::whereBetween('donation_date', [$startOfMonth, $endOfMonth])
    //         ->sum('amount');

    //     return view('admin.payment', compact('monthlyTotal'));
    // }

    public function showDonations()
    {
        $startOfMonth = now()->startOfMonth();
        $endOfMonth = now()->endOfMonth();

        // Calculate Monthly Total
        // $monthlyTotal = Donation::whereBetween('donation_date', [$startOfMonth, $endOfMonth])->sum('amount');
        $monthlyTotal = Donation::whereMonth('donation_date', now()->month)
                                 ->whereYear('donation_date', now()->year)
                                 ->sum('amount');

        // Fetch donations within the month
        $donations = Donation::whereBetween('donation_date', [$startOfMonth, $endOfMonth])->get();
        

        // Create transactions collection
        $transactions = $donations->map(function ($donation) {
            return [
                'full_name' => $donation->donor_name,
                'amount' => $donation->amount,
                'date_time' => $donation->donation_date,
                'transaction_type' => 'Donation',
                'transaction_id' => $donation->transaction_id,
            ];
        });

        // $transactions = $donationTransactions;
        return view('admin.payment', compact('monthlyTotal', 'transactions'));
    }
    public function showPayment()
    {
        $startOfMonth = now()->startOfMonth();
        $endOfMonth = now()->endOfMonth();

        // Calculate Monthly Total
        // $monthlyPayment = Payment::whereBetween('payment_date', [$startOfMonth, $endOfMonth])->sum('amount');
        $monthlyPayment = Payment::whereMonth('payment_date', now()->month)
                                 ->whereYear('payment_date', now()->year)
                                 ->sum('amount'); // Ensure 'amount' is the correct column name for payment
        

        // Fetch payments within the month
        $payments = Payment::whereBetween('payment_date', [$startOfMonth, $endOfMonth])->get();

        $transactions = $payments->map(function ($payment) {
            return [
                'full_name' => $payment->name,
                'amount' => $payment->amount,
                'date_time' => $payment->payment_date,
                'transaction_type' => 'Payment',
                'transaction_id' => $payment->transaction_id,
            ];
        });

        // $transactions = $paymentTransactions;
        return view('admin.payment', compact('monthlyPayment', 'transactions', 'payments'));
    }


    public function store(Request $request)
    {
        $result = (new DonationService(new useValidator))
            ->store($request);

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

    // Debug store function

    // public function store(Request $request)
    // {
    //     // Process the request with DonationService
    //     $result = (new DonationService(new useValidator))->store($request);

    //     if ($result['error_code'] !== MyConstant::SUCCESS_CODE) {
    //         return response()->json([
    //             'error_code' => $result['error_code'],
    //             'message' => $result['message'],
    //         ], $result['status_code']);
    //     }

    //     return redirect()->back()->with([
    //         'error_code' => $result['error_code'],
    //         'message' => $result['message'],
    //     ]);
    // }
// STORE BEFORE PRIEST
//     public function store(Request $request)
// {
//     // Validate request
//     $request->validate([
//         'transaction_id' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
//     ]);

//     // Check if file exists and process it
//     if ($request->hasFile('transaction_id')) {
//         $file = $request->file('transaction_id');
//         $filename = 'transaction_' . time() . '.' . $file->getClientOriginalExtension();
//         $file->move(public_path('assets/transactions'), $filename);

//         // Add the filename to request before passing it to DonationService
//         $request->merge(['transaction_id' => $filename]);
//     }

//     // Pass the updated request to DonationService
//     $result = (new DonationService(new UseValidator()))->store($request);

//     if ($result['error_code'] !== MyConstant::SUCCESS_CODE) {
//         return response()->json([
//             'error_code' => $result['error_code'],
//             'message' => $result['message'],
//         ], $result['status_code']);
//     }

//     return redirect()->back()->with([
//         'error_code' => $result['error_code'],
//         'message' => $result['message'],
//     ]);
    
// }


//     public function store(Request $request)
// {
//     // Pass the updated request to DonationService
//     $result = (new DonationService(new UseValidator()))->store($request);

//     if ($result['error_code'] !== MyConstant::SUCCESS_CODE) {
//         return response()->json([
//             'error_code' => $result['error_code'],
//             'message' => $result['message'],
//         ], $result['status_code']);
//     }

//     return redirect()->back()->with([
//         'error_code' => $result['error_code'],
//         'message' => $result['message'],
//     ]);
    
// }



    public function update(Request $request, $id)
    {
        $result = (new DonationService(new useValidator))
            ->update($request, $id);

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
    // update status donation
    public function updateStatus(Request $request, $id)
    {
        // Validate the request
        $request->validate([
            'status' => 'required|string|max:255',
        ]);

        try {
            $donation = Donation::findOrFail($id);
            
            // Update only the status field
            $donation->update([
                'status' => 'Received',
            ]);

            Notification::create([
                'type' => 'Donation',
                'message' => 'A donation was received by ' . Auth::user()->name,
                'is_read' => '0',
            ]);

            return redirect()->back()->with('success', 'Donation status updated successfully.');
            
        } catch (\Exception $e) {
            Log::warning('Database update failed, using Supabase fallback', ['error' => $e->getMessage()]);
            
            // Supabase fallback
            try {
                $supabaseUrl = env('SUPABASE_URL');
                $supabaseKey = env('SUPABASE_SERVICE_ROLE_KEY');
                
                // Update donation status in Supabase
                $response = \Illuminate\Support\Facades\Http::withHeaders([
                    'apikey' => $supabaseKey,
                    'Authorization' => 'Bearer ' . $supabaseKey,
                    'Content-Type' => 'application/json',
                    'Prefer' => 'return=minimal'
                ])->patch("{$supabaseUrl}/rest/v1/tdonations?id=eq.{$id}", [
                    'status' => 'Received'
                ]);
                
                if ($response->successful() || $response->status() === 204) {
                    Log::info('Donation status updated via Supabase', ['id' => $id]);
                    return redirect()->back()->with('success', 'Donation status updated successfully.');
                }
                
                throw new \Exception('Supabase update failed: ' . $response->body());
                
            } catch (\Exception $supabaseError) {
                Log::error('Supabase fallback failed', ['error' => $supabaseError->getMessage()]);
                return redirect()->back()->with('error', 'Failed to update donation status.');
            }
        }
    }
    
    public function destroy($id)
    {
        $result = (new DonationService(new useValidator))
            ->destroy($id);

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
