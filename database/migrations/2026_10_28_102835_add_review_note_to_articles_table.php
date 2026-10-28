<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 * Status artikel kini: 0 = Draft, 1 = Published, 2 = Menunggu Review.
 * Kolom status (tinyInteger) sudah muat nilai 2, jadi hanya perlu kolom catatan
 * dari admin saat artikel dikembalikan ke penulis.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            $table->text('review_note')->nullable()->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            $table->dropColumn('review_note');
        });
    }
};
