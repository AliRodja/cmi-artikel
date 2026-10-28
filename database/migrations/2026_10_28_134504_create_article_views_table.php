<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Riwayat kunjungan halaman artikel publik (dicatat App\Support\ArticleViewCounter).
     * "visitor" = "user:{id}" untuk user login, atau "guest:{hash sesi}" untuk tamu.
     * Sesi di-hash sha256, jadi ID sesi asli (yang bisa dipakai membajak sesi) tidak tersimpan.
     */
    public function up(): void
    {
        Schema::create('article_views', function (Blueprint $table) {
            $table->id();
            $table->foreignId('article_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('visitor', 80);
            $table->timestamp('viewed_at')->useCurrent();

            // Cek "pengunjung ini sudah dihitung?" & hitung kunjungan per periode.
            $table->index(['article_id', 'visitor']);
            $table->index(['article_id', 'viewed_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('article_views');
    }
};
