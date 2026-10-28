<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 * Migration baru (bukan mengedit create_users_table) karena database sudah berisi
 * data real: cukup `php artisan migrate`, tanpa migrate:fresh.
 * User lama otomatis menjadi "penulis"; admin ditunjuk lewat `php artisan user:role`.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role', 20)->default('penulis')->after('email');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('role');
        });
    }
};
