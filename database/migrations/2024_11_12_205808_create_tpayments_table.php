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
        Schema::create('tpayments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('request_id')
                ->constrained('trequests')
                ->onDelete('cascade');
            $table->string('name', 50); // Required field, no nullable constraint
            $table->decimal('amount', 10, 2);
            $table->date('payment_date');
            $table->string('payment_method', 50); // Required field, no nullable constraint
            $table->string('payment_status', 50); // Added length constraint
            $table->string('transaction_id', 100)->unique(); // Added uniqueness constraint
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tpayments');
    }
};
