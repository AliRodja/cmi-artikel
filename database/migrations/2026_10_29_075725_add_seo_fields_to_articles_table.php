<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Optimasi SEO per artikel (semua opsional):
     * - focus_keyword: kata kunci utama yang ditargetkan penulis (untuk checklist di form & JSON-LD "keywords").
     * - seo_title: judul di hasil Google (<title>), bila ingin berbeda dari judul artikel. Google memotong ±60 karakter.
     * - seo_description: penjelasan di bawah judul pada hasil Google (meta description). Ideal 120–160 karakter.
     * Kosong = memakai judul & ringkasan artikel.
     */
    public function up(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            $table->string('focus_keyword', 100)->nullable()->after('sources');
            $table->string('seo_title', 70)->nullable()->after('focus_keyword');
            $table->string('seo_description', 160)->nullable()->after('seo_title');
        });
    }

    public function down(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            $table->dropColumn(['focus_keyword', 'seo_title', 'seo_description']);
        });
    }
};
