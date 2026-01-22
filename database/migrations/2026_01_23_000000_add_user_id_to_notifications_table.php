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
        Schema::table('tnotifications', function (Blueprint $table) {
            // Add user_id foreign key - which user this notification is FOR
            $table->unsignedBigInteger('user_id')->after('id')->nullable();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            
            // Change is_read to read_at timestamp for better tracking
            $table->timestamp('read_at')->nullable()->after('message');
            
            // Drop old is_read column
            $table->dropColumn('is_read');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tnotifications', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn(['user_id', 'read_at']);
            $table->boolean('is_read')->default(false);
        });
    }
};
