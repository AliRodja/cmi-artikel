<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * - medical_reviewer_id: dokter yang meninjau isi artikel (diisi admin). Bila akunnya dihapus, artikel tetap
     *   ada tanpa keterangan peninjau (nullOnDelete), sama seperti user_id.
     * - sources: sumber rujukan, satu per baris (teks biasa; tautan http/https dijadikan link saat tampil).
     */
    public function up(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            $table->foreignId('medical_reviewer_id')->nullable()->after('user_id')->constrained('users')->nullOnDelete();
            $table->text('sources')->nullable()->after('content');
        });
    }

    public function down(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            $table->dropConstrainedForeignId('medical_reviewer_id');
            $table->dropColumn('sources');
        });
    }
};
