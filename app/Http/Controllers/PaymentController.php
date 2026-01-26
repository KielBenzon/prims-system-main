<?php

namespace App\Http\Controllers;

use App\Constant\MyConstant;
use App\Models\Payment;
use App\Models\Notification;
use App\Models\Transaction;
use App\Models\Request as RequestModel;
use App\Services\useValidator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class PaymentController extends Controller
{
    protected $useValidator;

    public function __construct(useValidator $useValidator)
    {
        $this->useValidator = $useValidator;
    }

    /**
     * Admin payment index with search & filter
     */
    public function index(Request $request)
    {
        $search = $request->input('search');

        $payments = $this->executeWithFallback(function () use ($search) {
            return Payment::when($search, function ($query, $search) {
                    return $query->where('full_name', 'like', "%{$search}%")
                                 ->orWhere('name', 'like', "%{$search}%")
                                 ->orWhere('transaction_id', 'like', "%{$search}%")
                                 ->orWhere('payment_date', 'like', "%{$search}%");
                })
                ->orderBy('created_at', 'desc')
                ->get();
        }, collect([]));

        // If database failed, try Supabase API
        if ($payments->isEmpty()) {
            try {
                $supabaseUrl = env('SUPABASE_URL');
                $supabaseKey = env('SUPABASE_SERVICE_ROLE_KEY');
                
                $response = \Illuminate\Support\Facades\Http::withHeaders([
                    'apikey' => $supabaseKey,
                    'Authorization' => 'Bearer ' . $supabaseKey,
                    'Content-Type' => 'application/json'
                ])->get($supabaseUrl . '/rest/v1/tpayments?order=created_at.desc');
                
                if ($response->successful()) {
                    $payments = collect($response->json())->map(function($item) {
                        return (object) $item;
                    });
                }
            } catch (\Exception $e) {
                Log::error('PaymentController: Supabase API failed: ' . $e->getMessage());
            }
        }

        $transactions = $payments->map(function ($p) {
            $fullName = $p->full_name ?? $p->name ?? ($p->firstname ?? null) ?? ($p->first_name ?? null);
            if (empty($fullName) && isset($p->user) && isset($p->user->name)) {
                $fullName = $p->user->name;
            }

            $date = $p->payment_date ?? $p->created_at ?? now();
            $txType = $p->transaction_type ?? $p->type ?? $p->payment_type ?? 'Payment';

            return (object) [
                'full_name'        => $fullName ?? '—',
                'amount'           => $p->amount ?? 0,
                'date_time'        => Carbon::parse($date)->format('Y-m-d H:i:s'),
                'transaction_type' => $txType,
                'transaction_id'   => $p->transaction_id ?? '—',
                'proof_image'      => $p->proof_image ?? null,
            ];
        })->values();

        return view('admin.payment', compact('transactions'));
    }

    /**
     * Store new payment
     */
    public function store(Request $request)
    {
        $request->validate([
            'transaction_id' => 'required|string|max:255|unique:tpayments,transaction_id',
            'amount'         => 'required|numeric|min:0',
            'payment_date'   => 'required|date',
            'payment_method' => 'required|string|max:50',
            'payment_status' => 'required|string|max:50',
            'proof_image'    => 'nullable|image|mimes:jpeg,png,jpg,gif|max:10240',
        ]);

        try {
            $filename = null;

            // Handle file upload
            if ($request->hasFile('proof_image')) {
                $file = $request->file('proof_image');
                $extension = $file->getClientOriginalExtension();
                $filename = 'payment_' . uniqid() . '.' . $extension;

                $file->move(public_path('assets/transaction_report'), $filename);
            }

            $payment = $this->executeWithFallback(function () use ($request, $filename) {
                return Payment::create([
                    'request_id'      => $request->request_id,
                    'name'            => $request->name,
                    'amount'          => $request->amount,
                    'payment_date'    => $request->payment_date,
                    'payment_method'  => $request->payment_method,
                    'payment_status'  => $request->payment_status,
                    'transaction_id'  => $request->transaction_id,
                    'proof_image'     => $filename,
                ]);
            }, null);

            if ($payment) {
                // Insert into transactions table with error handling
                $this->executeWithFallback(function () use ($payment) {
                    return Transaction::create([
                        'transaction_id'   => $payment->transaction_id,
                        'user_id'          => Auth::id(),
                        'amount'           => $payment->amount,
                        'status'           => 'completed',
                        'transaction_type' => 'payment',
                    ]);
                }, null);

                $this->executeWithFallback(function () {
                    // Notification for admin
                    Notification::create([
                        'type'    => 'Payment',
                        'message' => 'A new payment was recorded by ' . Auth::user()->name,
                        'user_id' => null,
                    ]);

                    // Notification for parishioner who made the payment
                    Notification::create([
                        'type'    => 'Payment',
                        'message' => 'Your payment has been recorded successfully.',
                        'user_id' => Auth::id(),
                    ]);

                    return true;
                }, null);

                return redirect()->back()->with('success', 'Payment added successfully.');
            } else {
                return redirect()->back()->with('error', 'Failed to add payment due to database connection issues.');
            }

        } catch (\Exception $e) {
            Log::error('Payment Store Error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to add payment.');
        }
    }

    public function resubmit($requestId)
    {
        $request = $this->executeWithFallback(function () use ($requestId) {
            return RequestModel::findOrFail($requestId);
        }, null);

        if (!$request) {
            return redirect()->back()->with('error', 'Request not found due to database connection issues.');
        }

        // Ensure the request was declined
        if ($request->status !== 'Decline') {
            return redirect()->back()->with('error', 'Payment can only be resubmitted for declined requests.');
        }

        // Optionally, reset payment info so parishioner can pay again
        $request->is_paid = 0;
        $request->status = 'Pending';
        
        $updated = $this->executeWithFallback(function () use ($request) {
            return $request->save();
        }, false);

        if (!$updated) {
            return redirect()->back()->with('error', 'Failed to update request due to database connection issues.');
        }

        return view('parishioner.payment_resubmit', compact('request'));
    }


    /**
     * Update payment
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'amount'        => 'nullable|numeric|min:0',
            'to_pay'        => 'nullable|numeric|min:0',
            'number_copies' => 'nullable|integer|min:1',
            'payment_date'  => 'nullable|date',
            'proof_image'   => 'nullable|image|mimes:jpeg,png,jpg,gif|max:10240',
        ]);

        try {
            $payment = $this->executeWithFallback(function () use ($id) {
                return Payment::findOrFail($id);
            }, null);

            if (!$payment) {
                return redirect()->back()->with('error', 'Payment not found due to database connection issues.');
            }

            $payment->transaction_id = $request->transaction_id ?? $payment->transaction_id;
            $payment->amount         = $request->to_pay ?? $request->amount ?? 0;
            $payment->number_copies  = $request->number_copies ?? $payment->number_copies;
            $payment->payment_date   = $request->payment_date ?? now();
            $payment->payment_status = $request->payment_status ?? $payment->payment_status;

            // Handle proof image upload
            if ($request->hasFile('proof_image')) {
                $file = $request->file('proof_image');
                $extension = $file->getClientOriginalExtension();
                $filename = 'payment_' . uniqid() . '.' . $extension;

                $file->move(public_path('assets/transaction_report'), $filename);

                $payment->proof_image = $filename;
            }

            $updated = $this->executeWithFallback(function () use ($payment) {
                return $payment->save();
            }, false);

            if ($updated) {
                // ✅ Update transactions table with error handling
                $this->executeWithFallback(function () use ($payment) {
                    return Transaction::where('transaction_id', $payment->transaction_id)
                        ->update([
                            'status'           => 'completed',
                            'transaction_type' => 'payment',
                            'amount'           => $payment->amount,
                        ]);
                }, null);

                return redirect()->back()->with('success', 'Payment updated successfully.');
            } else {
                return redirect()->back()->with('error', 'Failed to update payment due to database connection issues.');
            }

        } catch (\Exception $e) {
            Log::error('Payment Update Error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to update payment.');
        }
    }
}
