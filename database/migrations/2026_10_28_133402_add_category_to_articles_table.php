<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Jenis artikel (diabetes / jantung / kanker / ginjal, lihat Article::CATEGORIES).
     * Nullable karena artikel lama belum punya jenis; form tulis/edit mewajibkannya.
     */
    public function up(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            $table->string('category', 20)->nullable()->after('content')->index();
        });
    }

    public function down(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            $table->dropIndex(['category']);
            $table->dropColumn('category');
        });
    }
};
