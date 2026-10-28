<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Total kunjungan unik per artikel. Disimpan sebagai angka (bukan COUNT(*) dari article_views
     * setiap kali halaman dibuka) agar daftar "Artikel Terpopuler" cukup ORDER BY view_count.
     */
    public function up(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            $table->unsignedInteger('view_count')->default(0)->after('status')->index();
        });
    }

    public function down(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            $table->dropIndex(['view_count']);
            $table->dropColumn('view_count');
        });
    }
};
