<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TransactionsTableSeeder extends Seeder
{
    public function run()
    {
        // clear existing transactions
        DB::table('transactions')->truncate();

        // ✅ sync donations
        $donations = DB::table('tdonations')->get();
        foreach ($donations as $donation) {
            DB::table('transactions')->insert([
                'transaction_id'   => $donation->transaction_id ?? $donation->id, 
                'user_id'          => $donation->user_id ?? null,
                'full_name'        => $donation->donor_name,
                'amount'           => $donation->amount,
                'status'           => $donation->status ?? 'completed',
                'transaction_type' => 'donation',
                'created_at'       => $donation->created_at ?? Carbon::now(),
                'updated_at'       => $donation->updated_at ?? Carbon::now(),
            ]);
        }

        // ✅ sync payments
        $payments = DB::table('tpayments')->get();
        foreach ($payments as $payment) {
            DB::table('transactions')->insert([
                'transaction_id'   => $payment->transaction_id,
                'user_id'          => null,
                'full_name'        => $payment->name,
                'amount'           => $payment->amount,
                'status'           => $payment->payment_status,
                'transaction_type' => 'payment',
                'created_at'       => $payment->created_at ?? Carbon::now(),
                'updated_at'       => $payment->updated_at ?? Carbon::now(),
            ]);
        }
    }
}
