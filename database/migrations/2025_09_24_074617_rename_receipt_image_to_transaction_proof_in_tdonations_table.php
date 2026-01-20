<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasColumn('tdonations', 'receipt_image')) {
            Schema::table('tdonations', function (Blueprint $table) {
                $table->renameColumn('receipt_image', 'transaction_proof');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('tdonations', 'transaction_proof')) {
            Schema::table('tdonations', function (Blueprint $table) {
                $table->renameColumn('transaction_proof', 'receipt_image');
            });
        }
    }
};
