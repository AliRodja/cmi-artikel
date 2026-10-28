<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Gelar / profesi penulis, mis. "Dokter Spesialis Jantung (Sp.JP)". Tampil di bawah nama penulis
     * pada artikel, agar pembaca tahu siapa yang menulis informasi kesehatan tersebut.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('profession', 100)->nullable()->after('name');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('profession');
        });
    }
};
