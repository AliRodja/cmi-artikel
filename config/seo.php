<?php

/*
 * Pengaturan SEO situs (dipakai partials/seo & App\Support\Seo).
 * URL lengkap di meta tag, sitemap, dan JSON-LD dibentuk dari APP_URL — ganti APP_URL ke domain asli saat deploy.
 */

return [
    // Deskripsi bawaan untuk halaman yang tidak mengisi @section('description'). Idealnya 120–160 karakter.
    'description' => 'Artikel kesehatan jantung dari Klinik Utama CMI: kenali gejala, faktor risiko, pencegahan, '
        .'dan perawatan penyakit jantung.',

    // Gambar pratinjau saat link dibagikan (WhatsApp, Facebook, X) bila artikel tidak punya gambar. 1200×630 px.
    'image' => 'images/og-default.png',
];
