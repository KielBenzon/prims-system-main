<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Donation;
use App\Models\Payment;

class ReportController extends Controller
{
    // Show initial page with all transactions
    public function index()
    {
        $donations = $this->executeWithFallback(function () {
            return Donation::all();
        }, collect([]));
        
        $payments = $this->executeWithFallback(function () {
            return Payment::all();
        }, collect([]));

        return view('admin.report', [
            'transactions' => [
                'donations' => $donations,
                'payments' => $payments,
            ],
            'start_date' => null,
            'end_date' => null,
            'report_type' => 'all',
        ]);
    }

    // Generate filtered report
    public function generate(Request $request)
    {
        $validated = $request->validate([
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'report_type' => 'required|in:payments,donations,all',
        ]);

        $startDate = $validated['start_date'];
        $endDate = $validated['end_date'];
        $type = $validated['report_type'];

        if ($startDate && $endDate) {
            $donations = Donation::whereBetween('created_at', [$startDate, $endDate])->get();
            $payments = Payment::whereBetween('created_at', [$startDate, $endDate])->get();
        } else {
            // No date filters, fetch all
            $donations = Donation::all();
            $payments = Payment::all();
            $startDate = null;
            $endDate = null;
        }

        $transactions = [
            'donations' => $donations,
            'payments' => $payments,
        ];

        return view('admin.report', [
            'transactions' => $transactions,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'report_type' => $type,
        ]);
    }
}