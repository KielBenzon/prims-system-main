<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('transactions', function (Blueprint $table) {
            if (!Schema::hasColumn('transactions', 'full_name')) {
                $table->string('full_name')->nullable();
            }
            if (!Schema::hasColumn('transactions', 'transaction_type')) {
                $table->string('transaction_type')->nullable();
            }
            if (!Schema::hasColumn('transactions', 'transaction_id')) {
                $table->string('transaction_id')->nullable();
            }
            if (!Schema::hasColumn('transactions', 'date_time')) {
                $table->timestamp('date_time')->nullable()->after('status');
            }
        });
    }

    public function down()
    {
        Schema::table('transactions', function (Blueprint $table) {
            if (Schema::hasColumn('transactions', 'full_name')) {
                $table->dropColumn('full_name');
            }
            if (Schema::hasColumn('transactions', 'transaction_type')) {
                $table->dropColumn('transaction_type');
            }
            if (Schema::hasColumn('transactions', 'transaction_id')) {
                $table->dropColumn('transaction_id');
            }
            if (Schema::hasColumn('transactions', 'date_time')) {
                $table->dropColumn('date_time');
            }
        });
    }
};