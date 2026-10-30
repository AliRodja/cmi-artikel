<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 * Versi Bahasa Inggris artikel, diterjemahkan sendiri oleh penulis (opsional). Kosong = pengunjung yang memilih
 * English tetap melihat versi Indonesia. Batas panjang SEO sama dengan kolom Indonesia.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            $table->string('title_en')->nullable()->after('title');
            $table->longText('content_en')->nullable()->after('content');
            $table->string('seo_title_en', 70)->nullable()->after('seo_title');
            $table->string('seo_description_en', 160)->nullable()->after('seo_description');
        });
    }

    public function down(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            $table->dropColumn(['title_en', 'content_en', 'seo_title_en', 'seo_description_en']);
        });
    }
};
