<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // $path = database_path('schema.sql');
        // $sql = file_get_contents($path);
        // DB::unprepared($sql);

        // $password = 'password';
        // $hashed_password = password_hash($password, PASSWORD_BCRYPT);

        // DB::table('tusers')->insert([
        //     'name' => 'Admin',
        //     'email' => 'admin@email.com',
        //     'password' => $hashed_password,
        //     'role' => 'Admin',
        // ]);

        // DB::table('tusers')->insert([
        //     'name' => 'Parishioner',
        //     'email' => 'parishioner@email.com',
        //     'password' => $hashed_password,
        //     'role' => 'Parishioner',
        // ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // $path = database_path('schema.sql');
        // $sql = file_get_contents($path);
        // DB::unprepared($sql);
    }
};
