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
    Schema::table('tusers', function (Blueprint $table) {
        $table->rememberToken(); // adds a VARCHAR(100) nullable remember_token column
    });
}

public function down(): void
{
    Schema::table('tusers', function (Blueprint $table) {
        $table->dropColumn('remember_token');
    });
}

};
