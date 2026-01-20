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
        Schema::create('trequests', function (Blueprint $table) {
            $table->id();
            $table->string('document_type');
            $table->foreignId('requested_by')
                ->constrained('tusers')
                ->onDelete('cascade');
            $table->foreignId('approved_by')
                ->nullable()
                ->constrained('tusers')
                ->onDelete('cascade');
            $table->string('status');
            $table->string('is_paid');
            $table->boolean('is_deleted')->default(false); // Changed to boolean with a default value of false
            $table->text('notes')->nullable();
            $table->string('file')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trequests');
    }
};
