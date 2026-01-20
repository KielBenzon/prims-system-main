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
        Schema::create('tmail', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('sender');
            $table->string('recipient');
            $table->string('subject');
            $table->enum('priority', ['Very High', 'High', 'Normal', 'Low']);
            $table->enum('status', ['Undelivered', 'Delivered']);
            $table->date('date');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tmail');
    }
};
