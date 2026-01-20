<?php

// database/migrations/xxxx_xx_xx_add_transaction_proof_to_donations_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('tdonations', function (Blueprint $table) {
            $table->string('transactions')->nullable()->after('amount');
        });
    }

    public function down(): void
    {
        Schema::table('tdonations', function (Blueprint $table) {
            $table->dropColumn('transaction_proof');
        });
    }
};
