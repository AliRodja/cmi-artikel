<?php

/*
 * Kontak Klinik Utama CMI yang tampil di situs.
 * Tombol "Konsultasi segera" di halaman artikel membuka chat WhatsApp customer service
 * dengan pesan yang sudah terisi. http_build_query() meng-encode teksnya
 * (spasi → "+", baris baru → "%0A"), menghasilkan URL api.whatsapp.com yang sama persis
 * dengan link resmi CMI.
 */

return [
    'whatsapp_consultation_url' => 'https://api.whatsapp.com/send/?'.http_build_query([
        'phone' => '6282121590000',
        'text' => "Halo, dari website cmihospital.com, Saya ingin konsultasi.\nNama: \nAlamat: \nKeluhan: ",
        'app_absent' => 0,
    ]),
];
