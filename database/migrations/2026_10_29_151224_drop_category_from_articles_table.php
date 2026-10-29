<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Situs dikhususkan untuk artikel kesehatan jantung, jadi fitur jenis artikel dihapus.
     * Artikel yang jenisnya bukan jantung dijadikan Draft lebih dulu: tidak tampil di situs publik,
     * tapi tidak hilang — penulis/admin bisa menyunting atau menghapusnya sendiri.
     */
    public function up(): void
    {
        DB::table('articles')
            ->whereNotNull('category')
            ->where('category', '!=', 'jantung')
            ->update(['status' => 0]);

        Schema::table('articles', function (Blueprint $table) {
            $table->dropIndex(['category']);
            $table->dropColumn('category');
        });
    }

    /** Kolom dikembalikan kosong; isi lamanya ada di backup SQL sebelum migration ini. */
    public function down(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            $table->string('category', 20)->nullable()->after('content')->index();
        });
    }
};
