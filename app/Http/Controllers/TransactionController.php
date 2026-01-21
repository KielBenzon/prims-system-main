<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Payment;
use App\Models\Donation;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

use App\Models\Transaction;

class TransactionController extends Controller
{
    /**
     * Show filtered transactions list (with search & date range).
     */
    public function index(Request $request)
    {
        $start_date = $request->input('start_date');
        $end_date   = $request->input('end_date');

        $transactions = $this->executeWithFallback(function () use ($request, $start_date, $end_date) {
            $query = Transaction::query();

            // Apply date filter
            if ($start_date && $end_date) {
                $query->whereBetween('created_at', [
                    $start_date . ' 00:00:00',
                    $end_date . ' 23:59:59'
                ]);
            }

            // Apply search
            if ($request->filled('search')) {
                $query->where(function ($q) use ($request) {
                    $q->where('full_name', 'like', '%' . $request->search . '%')
                      ->orWhere('transaction_id', 'like', '%' . $request->search . '%')
                      ->orWhere('transaction_type', 'like', '%' . $request->search . '%');
                });
            }

            return $query->orderBy('created_at', 'desc')->get();
        }, collect([]));

        return view('transactions.index', compact('transactions', 'start_date', 'end_date'));
    }

    /**
     * Generate printable report.
     */
    public function report(Request $request)
    {
        $start_date = $request->input('start_date');
        $end_date = $request->input('end_date');

        // Default empty data
        $transactions = collect([]);
        $total_transactions = 0;
        $total_amount = 0;

        // Check if report is generated
        $report_generated = false;

        if ($start_date && $end_date) {
            $report_generated = true;

            $transactions = $this->executeWithFallback(function () use ($start_date, $end_date) {
                return DB::table('transactions')
                    ->whereBetween('created_at', [
                        $start_date . ' 00:00:00',
                        $end_date . ' 23:59:59'
                    ])
                    ->get();
            }, collect([]));

            $total_transactions = $transactions->count();
            $total_amount = $transactions->sum('amount');
        }

        return view('admin.transaction_report', compact(
            'transactions',
            'total_transactions',
            'total_amount',
            'start_date',
            'end_date',
            'report_generated'
        ));
    }


    /**
     * Generate report with filters.
     */
    public function generate(Request $request)
    {
        $start_date   = $request->input('start_date');
        $end_date     = $request->input('end_date');
        $report_type  = $request->input('report_type', 'all'); // default to 'all'
        $parishioner_name = $request->input('parishioner_name');
        $report_generated = false;

        $transactionsData = collect(); // start empty

        // Only fetch transactions if user has selected start_date and end_date
        if ($start_date && $end_date) {

            $report_generated = true;

            // Try Supabase REST API directly
            $supabaseUrl = env('SUPABASE_URL');
            $supabaseKey = env('SUPABASE_SERVICE_ROLE_KEY');

            // Fetch donations via Supabase (build URL with proper date filters)
            try {
                $donationsUrl = $supabaseUrl . '/rest/v1/tdonations?' 
                    . 'donation_date=gte.' . $start_date 
                    . '&donation_date=lte.' . $end_date 
                    . '&status=eq.Received';
                
                // Add name filter if provided
                if ($parishioner_name) {
                    $donationsUrl .= '&donor_name=ilike.*' . urlencode($parishioner_name) . '*';
                }
                
                $donationsUrl .= '&select=transaction_url,donor_name,amount,donation_date,status';
                
                $donationsResponse = \Illuminate\Support\Facades\Http::withHeaders([
                    'apikey' => $supabaseKey,
                    'Authorization' => 'Bearer ' . $supabaseKey,
                ])->get($donationsUrl);

                $donations = collect($donationsResponse->json() ?? []);
            } catch (\Exception $e) {
                $donations = collect([]);
            }

            // Fetch payments via Supabase (build URL with proper date filters)
            try {
                $paymentsUrl = $supabaseUrl . '/rest/v1/tpayments?' 
                    . 'payment_date=gte.' . $start_date 
                    . '&payment_date=lte.' . $end_date 
                    . '&payment_status=eq.Paid';
                
                // Add name filter if provided
                if ($parishioner_name) {
                    $paymentsUrl .= '&name=ilike.*' . urlencode($parishioner_name) . '*';
                }
                
                $paymentsUrl .= '&select=transaction_id,name,amount,payment_date,payment_status';
                
                $paymentsResponse = \Illuminate\Support\Facades\Http::withHeaders([
                    'apikey' => $supabaseKey,
                    'Authorization' => 'Bearer ' . $supabaseKey,
                ])->get($paymentsUrl);

                $payments = collect($paymentsResponse->json() ?? []);
            } catch (\Exception $e) {
                $payments = collect([]);
            }

            // Merge donations
            $transactionsData = $transactionsData->merge($donations->map(function ($d) {
                // Handle both object and array formats
                $d = is_array($d) ? (object) $d : $d;
                return [
                    'transaction_id' => $d->transaction_url ?? $d->transaction_id ?? '',
                    'full_name' => $d->donor_name ?? '',
                    'amount' => $d->amount ?? 0,
                    'date' => Carbon::parse($d->donation_date ?? now()),
                    'transaction_type' => 'Donation',
                ];
            }));

            // Merge payments
            $transactionsData = $transactionsData->merge($payments->map(function ($p) {
                // Handle both object and array formats
                $p = is_array($p) ? (object) $p : $p;
                return [
                    'transaction_id' => $p->transaction_id ?? '',
                    'full_name' => $p->name ?? '',
                    'amount' => $p->amount ?? 0,
                    'date' => Carbon::parse($p->payment_date ?? now()),
                    'transaction_type' => 'Payment',
                ];
            }));

            // Filter by report type if needed
            if ($report_type === 'donations') {
                $transactionsData = $transactionsData->where('transaction_type', 'Donation')->values();
            } elseif ($report_type === 'payments') {
                $transactionsData = $transactionsData->where('transaction_type', 'Payment')->values();
            }
        }

        $total_transactions = $transactionsData->count();
        $total_amount = $transactionsData->sum('amount');

        return view('admin.transaction_report', [
            'transactions' => $transactionsData,
            'total_transactions' => $total_transactions,
            'total_amount' => $total_amount,
            'start_date' => $start_date,
            'end_date' => $end_date,
            'report_generated' => $report_generated,
            'report_type' => $report_type,
            'parishioner_name' => $parishioner_name,
        ]);
    }

}