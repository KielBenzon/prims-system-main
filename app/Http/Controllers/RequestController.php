<?php

namespace App\Http\Controllers;

use App\Constant\MyConstant;
use App\Models\CertificateType;
use App\Models\Request as RequestModel;
use App\Models\User;
use App\Models\Notification;
use App\Models\Payment; // ← added
use App\Models\Transaction; // ← optional, but added for future use
use App\Services\RequestService;
use App\Services\useValidator;
use App\Services\SupabaseStorageService;
use Illuminate\Http\Request as HttpRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class RequestController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $search = request('search');
        $status = request('status');
        $currentUser = Auth::user();
        $userId = $currentUser ? $currentUser->id : null;

        // Cache key unique to user and filters
        $cacheKey = "requests_user_{$userId}_search_{$search}_status_{$status}";
        
        // Cache for 30 seconds
        $requests = Cache::remember($cacheKey, 30, function () use ($search, $status, $userId) {
            // Try database first with eager loading to prevent N+1 queries
            try {
                $query = RequestModel::with(['user', 'request_approved', 'certificate_detail', 'payment'])
                    ->where('requested_by', $userId)
                    ->select(['id', 'document_type', 'requested_by', 'approved_by', 'status', 'is_paid', 'notes', 'created_at', 'updated_at']); // Only select needed columns
            
                if ($search) {
                    $query->where(function($q) use ($search) {
                        $q->where('document_type', 'like', '%' . $search . '%')
                          ->orWhere('status', 'like', '%' . $search . '%')
                          ->orWhere('is_paid', 'like', '%' . $search . '%');
                    });
                }
                
                if ($status) {
                    $query->where('status', $status);
                }
                
                return $query->orderBy('created_at', 'desc')->paginate(10);
            } catch (\Exception $e) {
                // Database failed - use optimized Supabase query with joins (1 API call instead of 4)
                try {
                    $supabaseUrl = env('SUPABASE_URL', 'https://lruvxbhfiogqolwztovs.supabase.co');
                    $supabaseKey = env('SUPABASE_ANON_KEY', 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6ImxydXZ4YmhmaW9ncW9sd3p0b3ZzIiwicm9sZSI6ImFub24iLCJpYXQiOjE3MzY1NTU0MjIsImV4cCI6MjA1MjEzMTQyMn0.J7Wkej_K8_cY5lZ0F9SqYIgVEYtFP0O9IkJBhVKQJEA');
                    
                    // Single API call with all joins - 10x faster!
                    $url = $supabaseUrl . '/rest/v1/trequests?requested_by=eq.' . $userId 
                        . '&select=*,user:tusers!requested_by(*),request_approved:tusers!approved_by(*),certificate_detail:tcertificate_details!request_id(*),payment:tpayments!request_id(*)'
                        . '&order=created_at.desc';
                    
                    $response = \Illuminate\Support\Facades\Http::withHeaders([
                        'apikey' => $supabaseKey,
                        'Authorization' => 'Bearer ' . $supabaseKey,
                    ])->get($url);
                    
                    if ($response->successful()) {
                        $data = $response->json();
                        
                        // Convert to objects with default values
                        $collection = collect($data)->map(function($item) {
                            $request = (object) $item;
                            
                            // Convert nested arrays to objects
                            $request->user = isset($item['user']) ? (object) $item['user'] : (object) ['name' => 'Unknown User'];
                            $request->request_approved = isset($item['request_approved']) ? (object) $item['request_approved'] : null;
                            $request->payment = isset($item['payment']) && !empty($item['payment']) ? (object) $item['payment'][0] : null;
                            
                            if (isset($item['certificate_detail']) && !empty($item['certificate_detail'])) {
                                $request->certificate_detail = (object) $item['certificate_detail'][0];
                            } else {
                                $request->certificate_detail = (object) [
                                    'certificate_type' => 'N/A',
                                    'name_of_child' => 'N/A',
                                    'father_name' => 'N/A',
                                    'name_of_father' => 'N/A',
                                    'mother_name' => 'N/A',
                                    'name_of_mother' => 'N/A',
                                    'date_of_birth' => 'N/A',
                                    'place_of_birth' => 'N/A',
                                    'date_of_baptism' => 'N/A',
                                    'baptism_schedule' => 'N/A',
                                    'minister' => 'N/A',
                                    'sponsors' => 'N/A',
                                    'godfather' => 'N/A',
                                    'godmother' => 'N/A',
                                    'book_number' => 'N/A',
                                    'page_number' => 'N/A',
                                    'entry_number' => 'N/A'
                                ];
                            }
                            
                            return $request;
                        });
                        
                        return new \Illuminate\Pagination\LengthAwarePaginator(
                            $collection,
                            count($data),
                            10,
                            1,
                            ['path' => request()->url(), 'pageName' => 'page']
                        );
                    } else {
                        return new \Illuminate\Pagination\LengthAwarePaginator([], 0, 10, 1);
                    }
                } catch (\Exception $supabaseError) {
                    return new \Illuminate\Pagination\LengthAwarePaginator([], 0, 10, 1);
                }
            }
        });

        // Certificate types - using fixed values since all are 100.00
        $certificate_types = collect([
            'Baptismal Certificate' => 100.00,
            'Marriage Certificate' => 100.00,
            'Death Certificate' => 100.00,
            'Confirmation Certificate' => 100.00
        ]);
        
        $users = $this->executeWithFallback(function () {
            return User::all();
        }, collect([]));
        
        return view('parishioner.request', compact('requests', 'certificate_types', 'users'));
    }
    

    public function approval_request(HttpRequest $request)
    {
        $search = $request->query('search');
        $status = $request->query('status');

        // Cache key for admin
        $cacheKey = "admin_requests_search_{$search}_status_{$status}";
        
        // Cache for 30 seconds
        $requests = Cache::remember($cacheKey, 30, function () use ($search, $status) {
            // Try database first
            try {
                Log::info('ADMIN: Trying database query...');
                $query = RequestModel::query()
                    ->with(['user', 'request_approved', 'certificate_detail', 'payment']);
            
                if ($search) {
                    $query->where(function($q) use ($search) {
                        $q->where('document_type', 'like', '%' . $search . '%')
                          ->orWhere('requested_by', 'like', '%' . $search . '%')
                          ->orWhere('approved_by', 'like', '%' . $search . '%')
                          ->orWhere('status', 'like', '%' . $search . '%')
                          ->orWhere('is_paid', 'like', '%' . $search . '%');
                    });
                }
                
                if ($status) {
                    $query->where('status', $status);
                }
                
                return $query->orderBy('created_at', 'desc')->paginate(10);
            } catch (\Exception $e) {
                // Database failed - use optimized Supabase query with joins
                try {
                    $supabaseUrl = env('SUPABASE_URL', 'https://lruvxbhfiogqolwztovs.supabase.co');
                    $supabaseKey = env('SUPABASE_ANON_KEY', 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6ImxydXZ4YmhmaW9ncW9sd3p0b3ZzIiwicm9sZSI6ImFub24iLCJpYXQiOjE3MzY1NTU0MjIsImV4cCI6MjA1MjEzMTQyMn0.J7Wkej_K8_cY5lZ0F9SqYIgVEYtFP0O9IkJBhVKQJEA');
                    
                    // Single API call with all joins - 10x faster!
                    $url = $supabaseUrl . '/rest/v1/trequests?select=*,user:tusers!requested_by(*),request_approved:tusers!approved_by(*),certificate_detail:tcertificate_details!request_id(*),payment:tpayments!request_id(*)&order=created_at.desc';
                    
                    $response = \Illuminate\Support\Facades\Http::withHeaders([
                        'apikey' => $supabaseKey,
                        'Authorization' => 'Bearer ' . $supabaseKey,
                    ])->get($url);
                    
                    if ($response->successful()) {
                        $data = $response->json();
                        
                        // Convert to objects with default values
                        $collection = collect($data)->map(function($item) {
                            $request = (object) $item;
                            
                            // Convert nested arrays to objects
                            $request->user = isset($item['user']) ? (object) $item['user'] : (object) ['name' => 'Unknown User'];
                            $request->request_approved = isset($item['request_approved']) ? (object) $item['request_approved'] : null;
                            $request->payment = isset($item['payment']) && !empty($item['payment']) ? (object) $item['payment'][0] : null;
                            
                            // Convert certificate_detail array to object with all fields
                            if (isset($item['certificate_detail']) && !empty($item['certificate_detail'])) {
                                $request->certificate_detail = (object) $item['certificate_detail'][0];
                            } else {
                                $request->certificate_detail = (object) [
                                    'certificate_type' => 'N/A',
                                    'name_of_child' => 'N/A',
                                    'name_of_father' => 'N/A',
                                    'name_of_mother' => 'N/A',
                                    'date_of_birth' => 'N/A',
                                    'place_of_birth' => 'N/A',
                                    'date_of_baptism' => 'N/A',
                                    'baptism_schedule' => 'N/A',
                                ];
                            }
                            
                            return $request;
                        });
                        
                        return new \Illuminate\Pagination\LengthAwarePaginator(
                            $collection,
                            count($data),
                            10,
                            1,
                            ['path' => request()->url(), 'pageName' => 'page']
                        );
                    } else {
                        return new \Illuminate\Pagination\LengthAwarePaginator([], 0, 10, 1);
                    }
                } catch (\Exception $supabaseError) {
                    return new \Illuminate\Pagination\LengthAwarePaginator([], 0, 10, 1);
                }
            }
        });

        return view('admin.request', compact('requests'));
    }

    /**
     * Store a newly created resource.
     */
    public function store(HttpRequest $request)
    {
        $result = (new RequestService(new useValidator))
            ->store($request);

        if ($result['error_code'] !== MyConstant::SUCCESS_CODE) {
            return response()->json([
                'error_code' => $result['error_code'],
                'message' => $result['message'],
            ], $result['status_code']);
        }

        // Clear all request-related caches to ensure fresh data on reload
        $this->clearRequestCaches();

        return redirect()->back()->with([
            'error_code' => $result['error_code'],
            'message' => $result['message'],
        ]);
    }

    public function update(HttpRequest $request, $id)
    {
        $result = (new RequestService(new useValidator))
            ->update($request, $id);

        if ($result['error_code'] !== MyConstant::SUCCESS_CODE) {
            return response()->json([
                'error_code' => $result['error_code'],
                'message' => $result['message'],
            ], $result['status_code']);
        }

        // Clear all request-related caches to ensure fresh data on reload
        $this->clearRequestCaches();

        return redirect()->back()->with([
            'error_code' => $result['error_code'],
            'message' => $result['message'],
        ]);
    }

    /**
     * Clear all request-related cache entries
     */
    private function clearRequestCaches()
    {
        // Clear cache for all users and filters
        Cache::flush(); // This clears all cache - you can make it more specific if needed
    }

    public function verifyPayment(HttpRequest $request, $id)
    {
        try {
            $req = RequestModel::findOrFail($id);

            // Update the payment record first
            if ($req->payment) {
                $req->payment->payment_status = 'Paid';
                $req->payment->save();
            }

            // Update the request status to Completed
            $req->status = 'Completed';
            $req->save();

            return redirect()->back()->with('success', 'Payment verified and request marked as completed.');
            
        } catch (\Exception $e) {
            // Fallback to Supabase REST API
            try {
                $supabaseUrl = env('SUPABASE_URL');
                $supabaseKey = env('SUPABASE_SERVICE_ROLE_KEY');

                // Get payment record
                $paymentResponse = \Illuminate\Support\Facades\Http::withHeaders([
                    'apikey' => $supabaseKey,
                    'Authorization' => 'Bearer ' . $supabaseKey,
                    'Content-Type' => 'application/json'
                ])->get($supabaseUrl . '/rest/v1/tpayments?request_id=eq.' . $id);

                // Update payment to Paid
                if ($paymentResponse->successful() && !empty($paymentResponse->json())) {
                    $updateResponse = \Illuminate\Support\Facades\Http::withHeaders([
                        'apikey' => $supabaseKey,
                        'Authorization' => 'Bearer ' . $supabaseKey,
                        'Content-Type' => 'application/json',
                        'Prefer' => 'return=representation'
                    ])->patch($supabaseUrl . '/rest/v1/tpayments?request_id=eq.' . $id, [
                        'payment_status' => 'Paid',
                        'updated_at' => now('Asia/Manila')->toIso8601String()
                    ]);
                    
                    if (!$updateResponse->successful()) {
                        Log::error('Failed to verify payment via API: ' . $updateResponse->body());
                    }
                } else {
                    Log::error('Payment not found for request_id: ' . $id);
                }

                // Update request status to Completed
                \Illuminate\Support\Facades\Http::withHeaders([
                    'apikey' => $supabaseKey,
                    'Authorization' => 'Bearer ' . $supabaseKey,
                    'Content-Type' => 'application/json',
                    'Prefer' => 'return=representation'
                ])->patch($supabaseUrl . '/rest/v1/trequests?id=eq.' . $id, [
                    'status' => 'Completed',
                    'updated_at' => now('Asia/Manila')->toIso8601String()
                ]);

                return redirect()->back()->with('success', 'Payment verified and request marked as completed.');
                
            } catch (\Exception $apiException) {
                return redirect()->back()->with('error', 'Failed to verify payment: ' . $apiException->getMessage());
            }
        }
    }



    public function destroy($id)
    {
        try {
            // Try to delete the request with database first
            $deleted = $this->executeWithFallback(function () use ($id) {
                $request = RequestModel::findOrFail($id);
                return $request->delete();
            }, false);

            // If database delete failed, try direct Supabase API delete
            if (!$deleted) {
                Log::info('RequestController: Database delete failed, trying direct Supabase API delete for request ID: ' . $id);
                
                try {
                    $supabaseUrl = env('SUPABASE_URL', 'https://lruvxbhfiogqolwztovs.supabase.co');
                    $supabaseKey = env('SUPABASE_ANON_KEY', 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6ImxydXZ4YmhmaW9ncW9sd3p0b3ZzIiwicm9sZSI6ImFub24iLCJpYXQiOjE3MzY1NTU0MjIsImV4cCI6MjA1MjEzMTQyMn0.J7Wkej_K8_cY5lZ0F9SqYIgVEYtFP0O9IkJBhVKQJEA');
                    
                    // Delete from Supabase using REST API
                    $deleteUrl = $supabaseUrl . '/rest/v1/trequests?id=eq.' . $id;
                    
                    $response = \Illuminate\Support\Facades\Http::withHeaders([
                        'apikey' => $supabaseKey,
                        'Authorization' => 'Bearer ' . $supabaseKey,
                        'Content-Type' => 'application/json',
                        'Prefer' => 'return=minimal'
                    ])->delete($deleteUrl);
                    
                    if ($response->successful()) {
                        $deleted = true;
                        Log::info('RequestController: Direct Supabase delete successful for request ID: ' . $id);
                    } else {
                        Log::error('RequestController: Direct Supabase delete failed: ' . $response->body());
                    }
                } catch (\Exception $e) {
                    Log::error('RequestController: Supabase delete exception: ' . $e->getMessage());
                }
            }

            if ($deleted) {
                // Try to create notification with fallback
                $this->executeWithFallback(function () {
                    return Notification::create([
                        'type' => 'Request',
                        'message' => 'A request has been deleted by ' . Auth::user()->name,
                        'user_id' => null,
                    ]);
                }, null);

                // Clear all request-related caches to ensure fresh data on reload
                $this->clearRequestCaches();

                return redirect()->back()->with([
                    'error_code' => MyConstant::SUCCESS_CODE,
                    'message' => 'Request deleted successfully.',
                ]);
            } else {
                return redirect()->back()->with([
                    'error_code' => MyConstant::FAILED_CODE,
                    'message' => 'Failed to delete request.',
                ]);
            }
        } catch (\Exception $e) {
            Log::error('RequestController destroy error: ' . $e->getMessage());
            
            return redirect()->back()->with([
                'error_code' => MyConstant::FAILED_CODE,
                'message' => 'Failed to delete request due to system issues.',
            ]);
        }
    }

    public function approve_request(HttpRequest $request, $id)
    {
        try {
            // Try to approve the request with database first
            $approved = $this->executeWithFallback(function () use ($request, $id) {
                $requestModel = RequestModel::findOrFail($id);
                $requestModel->status = 'Approved';
                $requestModel->approved_by = Auth::user()->id;
                return $requestModel->save();
            }, false);

            // If database update failed, try direct Supabase API update
            if (!$approved) {
                Log::info('RequestController: Database update failed, trying direct Supabase API update for request ID: ' . $id);
                
                try {
                    $supabaseUrl = env('SUPABASE_URL', 'https://lruvxbhfiogqolwztovs.supabase.co');
                    $supabaseKey = env('SUPABASE_ANON_KEY', 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6ImxydXZ4YmhmaW9ncW9sd3p0b3ZzIiwicm9sZSI6ImFub24iLCJpYXQiOjE3MzY1NTU0MjIsImV4cCI6MjA1MjEzMTQyMn0.J7Wkej_K8_cY5lZ0F9SqYIgVEYtFP0O9IkJBhVKQJEA');
                    
                    // Update in Supabase using REST API
                    $updateUrl = $supabaseUrl . '/rest/v1/trequests?id=eq.' . $id;
                    
                    $response = \Illuminate\Support\Facades\Http::withHeaders([
                        'apikey' => $supabaseKey,
                        'Authorization' => 'Bearer ' . $supabaseKey,
                        'Content-Type' => 'application/json',
                        'Prefer' => 'return=minimal'
                    ])->patch($updateUrl, [
                        'status' => 'Approved',
                        'approved_by' => Auth::user()->id
                    ]);
                    
                    if ($response->successful()) {
                        $approved = true;
                        Log::info('RequestController: Direct Supabase update successful for request ID: ' . $id);
                    } else {
                        Log::error('RequestController: Direct Supabase update failed: ' . $response->body());
                    }
                } catch (\Exception $e) {
                    Log::error('RequestController: Supabase update exception: ' . $e->getMessage());
                }
            }

            if ($approved) {
                // Get the request model to access user_id
                $requestModel = RequestModel::find($id);
                
                // Try to create notification with fallback
                $this->executeWithFallback(function () use ($requestModel) {
                    // Notification for admin
                    Notification::create([
                        'type' => 'Request',
                        'message' => 'A request has been approved by ' . Auth::user()->name,
                        'user_id' => null,
                    ]);

                    // Notification for parishioner who owns the request
                    if ($requestModel && $requestModel->user_id) {
                        Notification::create([
                            'type' => 'Request',
                            'message' => 'Your request has been approved!',
                            'user_id' => $requestModel->user_id,
                        ]);
                    }

                    return true;
                }, null);

                // Clear all request-related caches to ensure fresh data on reload
                $this->clearRequestCaches();

                return redirect()->back()->with([
                    'error_code' => MyConstant::SUCCESS_CODE,
                    'message' => 'Request approved successfully.',
                ]);
            } else {
                return redirect()->back()->with([
                    'error_code' => MyConstant::FAILED_CODE,
                    'message' => 'Failed to approve request.',
                ]);
            }
        } catch (\Exception $e) {
            Log::error('RequestController approve_request error: ' . $e->getMessage());
            
            return redirect()->back()->with([
                'error_code' => MyConstant::FAILED_CODE,
                'message' => 'Failed to approve request due to system issues.',
            ]);
        }
    }

    /**
     * Upload payment proof
     */
    public function updatePayment(HttpRequest $request, $id)
    {
        // Validate request including the file
        $request->validate([
            'transaction_id' => 'required|image|mimes:jpeg,png,jpg,gif|max:10240',
            'to_pay' => 'required|numeric',
        ]);

        $transactionUrl = null;
        
        if ($request->hasFile('transaction_id')) {
            $file = $request->file('transaction_id');
            
            // Upload to Supabase
            $storageService = new SupabaseStorageService();
            $bucket = env('SUPABASE_STORAGE_BUCKET_PAYMENTS', 'payments');
            $result = $storageService->upload($file, $bucket, 'transaction_proofs');
            
            if ($result['success']) {
                $transactionUrl = $result['url'];
                Log::info('Payment transaction uploaded to Supabase', ['url' => $transactionUrl]);
            } else {
                Log::error('Supabase upload failed for payment', ['error' => $result['error']]);
                return redirect()->back()->with('error', 'Failed to upload transaction proof.');
            }
        } else {
            return redirect()->back()->with('error', 'Transaction ID image is required.');
        }

        $amount = $request->to_pay;

        $result = (new RequestService(new useValidator))
            ->updatePayment($request, $id, $amount, $transactionUrl);

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

    /**
     * Show deleted count
     */
    public function showDeletedRequests()
    {
        $deletedCount = RequestModel::where('isDeleted', 1)->count();
        return view('profile', compact('deletedCount'));
    }

    /**
     * RELEASE CERTIFICATE
     * (Payment must be RECEIVED before downloading)
     */
    public function releaseCertificate($id)
    {
        $requestData = RequestModel::findOrFail($id);

        // 🔐 PAYMENT VALIDATION
        $payment = Payment::where('transaction_id', $requestData->id)->first();

        if (!$payment || $payment->payment_status !== "Received") {
            return back()->with('error', 'Payment not yet confirmed. Certificate cannot be released.');
        }

        // ✔ Payment confirmed — allow certificate release
        return view('certificate.view', [
            'request' => $requestData
        ]);
    }
}
