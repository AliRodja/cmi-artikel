<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 * Peran ketiga: "pembaca". Akun baru dari halaman Daftar kini menjadi pembaca;
 * penulis ditunjuk admin. Hanya nilai DEFAULT yang berubah, peran user yang
 * sudah ada tidak disentuh.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role', 20)->default('pembaca')->change();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role', 20)->default('penulis')->change();
        });
    }
};
