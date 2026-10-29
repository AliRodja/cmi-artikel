<?php

/*
 * Kontak & identitas Klinik Utama CMI yang tampil di situs (footer, tombol konsultasi).
 *
 * Data klinik diisi lewat .env (lihat .env.example): CLINIC_ADDRESS, CLINIC_HOURS, CLINIC_EMAIL,
 * CLINIC_MAPS_URL, CLINIC_ACCREDITATION. Yang kosong otomatis tidak ditampilkan di footer.
 *
 * Tombol "Konsultasi" membuka chat WhatsApp customer service dengan pesan yang sudah terisi.
 * http_build_query() meng-encode teksnya (spasi → "+", baris baru → "%0A"), menghasilkan URL
 * api.whatsapp.com yang sama persis dengan link resmi CMI.
 */

$whatsappNumber = '6282121590000';

return [
    // Artikel baru masuk daftar "Artikel Terpopuler" setelah dilihat minimal sebanyak ini (pengunjung unik).
    'popular_min_views' => 5,

    'whatsapp_number' => $whatsappNumber,
    'whatsapp_display' => '0821-2159-0000',

    'whatsapp_consultation_url' => 'https://api.whatsapp.com/send/?'.http_build_query([
        'phone' => $whatsappNumber,
        'text' => "Halo, dari website cmihospital.com, Saya ingin konsultasi.\nNama: \nAlamat: \nKeluhan: ",
        'app_absent' => 0,
    ]),

    // Media sosial resmi CMI, disalin dari footer cmihospital.com (dicek 2026-10-29). Urutan = urutan ikon di footer.
    'social' => [
        ['label' => 'Facebook', 'icon' => 'facebook', 'url' => 'https://www.facebook.com/cmihospitals'],
        ['label' => 'Instagram', 'icon' => 'instagram', 'url' => 'https://www.instagram.com/cmihospitals/'],
        ['label' => 'X', 'icon' => 'x-twitter', 'url' => 'https://twitter.com/cmihospitals/'],
        ['label' => 'TikTok', 'icon' => 'tiktok', 'url' => 'https://www.tiktok.com/@cmihospitals'],
        ['label' => 'YouTube', 'icon' => 'youtube', 'url' => 'https://www.youtube.com/c/CMIhospital-com'],
    ],

    'address' => env('CLINIC_ADDRESS'),
    'hours' => env('CLINIC_HOURS'),
    'email' => env('CLINIC_EMAIL'),
    'maps_url' => env('CLINIC_MAPS_URL'),
    'accreditation' => env('CLINIC_ACCREDITATION'),
];
