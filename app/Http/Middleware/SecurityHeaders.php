<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/*
 * Header keamanan HTTP untuk semua respons (didaftarkan global di bootstrap/app.php, jadi halaman 404 ikut).
 * Header ini meminta browser menutup celah yang tidak bisa ditutup dari kode aplikasi saja.
 */
class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $headers = [
            // Situs tidak boleh dimuat di dalam iframe situs lain (mencegah clickjacking: tombol kita ditumpuk
            // tampilan palsu sehingga pengunjung tanpa sadar menekan Keluar/Hapus).
            'X-Frame-Options' => 'SAMEORIGIN',
            // Browser tidak boleh "menebak" jenis file, mis. menjalankan gambar upload sebagai skrip.
            'X-Content-Type-Options' => 'nosniff',
            // Situs lain yang ditautkan hanya melihat domain kita, bukan URL lengkap (mis. ?search= atau token).
            'Referrer-Policy' => 'strict-origin-when-cross-origin',
            // Fitur perangkat yang tidak dipakai situs ini dimatikan.
            'Permissions-Policy' => 'camera=(), microphone=(), geolocation=(), payment=()',
        ];

        // HSTS: setelah sekali dibuka lewat HTTPS, browser selalu memakai HTTPS selama 1 tahun. Hanya dikirim di
        // HTTPS (di HTTP diabaikan browser, dan di localhost bisa mengunci pengembangan ke HTTPS).
        if ($request->isSecure()) {
            $headers['Strict-Transport-Security'] = 'max-age=31536000';
        }

        foreach ($headers as $name => $value) {
            $response->headers->set($name, $value);
        }

        return $response;
    }
}
