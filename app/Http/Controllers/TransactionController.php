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
        $report_generated = false;

        $transactionsData = collect(); // start empty

        // Only fetch transactions if user has selected start_date and end_date
        if ($start_date && $end_date) {

            $report_generated = true;

            // Fetch donations
            $donations = $this->executeWithFallback(function () use ($start_date, $end_date) {
                return DB::table('tdonations')
                    ->select('transaction_id', 'donor_name', 'amount', 'donation_date')
                    ->whereBetween('donation_date', [$start_date, $end_date])
                    ->get();
            }, collect([]));

            // Fetch payments
            $payments = $this->executeWithFallback(function () use ($start_date, $end_date) {
                return DB::table('tpayments')
                    ->select('transaction_id', 'name', 'amount', 'payment_date')
                    ->whereBetween('payment_date', [$start_date, $end_date])
                    ->get();
            }, collect([]));

            // Merge donations
            $transactionsData = $transactionsData->merge($donations->map(function ($d) {
                return [
                    'transaction_id' => $d->transaction_id,
                    'full_name' => $d->donor_name, // exact donor name
                    'amount' => $d->amount,
                    'date' => Carbon::parse($d->donation_date),
                    'transaction_type' => 'Donation',
                ];
            }));

            // Merge payments
            $transactionsData = $transactionsData->merge($payments->map(function ($p) {
                return [
                    'transaction_id' => $p->transaction_id,
                    'full_name' => $p->name, // exact payer name
                    'amount' => $p->amount,
                    'date' => Carbon::parse($p->payment_date),
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
        ]);
    }

}