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
        Schema::table('trequests', function (Blueprint $table) {
            // Add indexes on frequently queried columns
            $table->index('requested_by', 'idx_trequests_requested_by');
            $table->index('approved_by', 'idx_trequests_approved_by');
            $table->index('status', 'idx_trequests_status');
            $table->index('is_paid', 'idx_trequests_is_paid');
            $table->index('document_type', 'idx_trequests_document_type');
            $table->index('created_at', 'idx_trequests_created_at');
            
            // Composite index for common query patterns
            $table->index(['requested_by', 'status'], 'idx_trequests_user_status');
            $table->index(['requested_by', 'created_at'], 'idx_trequests_user_created');
        });

        Schema::table('tcertificate_details', function (Blueprint $table) {
            // Add index on foreign key
            $table->index('request_id', 'idx_certificate_details_request_id');
        });

        Schema::table('tpayments', function (Blueprint $table) {
            // Add index on foreign key
            $table->index('request_id', 'idx_payments_request_id');
            $table->index('payment_status', 'idx_payments_status');
        });

        Schema::table('tusers', function (Blueprint $table) {
            // Add indexes for user lookups
            $table->index('email', 'idx_users_email');
            $table->index('role', 'idx_users_role');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('trequests', function (Blueprint $table) {
            $table->dropIndex('idx_trequests_requested_by');
            $table->dropIndex('idx_trequests_approved_by');
            $table->dropIndex('idx_trequests_status');
            $table->dropIndex('idx_trequests_is_paid');
            $table->dropIndex('idx_trequests_document_type');
            $table->dropIndex('idx_trequests_created_at');
            $table->dropIndex('idx_trequests_user_status');
            $table->dropIndex('idx_trequests_user_created');
        });

        Schema::table('tcertificate_details', function (Blueprint $table) {
            $table->dropIndex('idx_certificate_details_request_id');
        });

        Schema::table('tpayments', function (Blueprint $table) {
            $table->dropIndex('idx_payments_request_id');
            $table->dropIndex('idx_payments_status');
        });

        Schema::table('tusers', function (Blueprint $table) {
            $table->dropIndex('idx_users_email');
            $table->dropIndex('idx_users_role');
        });
    }
};
