<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Number;
use Symfony\Component\HttpFoundation\Response;

/*
 * Memakai bahasa pilihan pengunjung (disimpan di sesi oleh LocaleController) di seluruh situs, termasuk area
 * penulis/admin. Tanpa pilihan atau pilihan tidak dikenal → APP_LOCALE (id).
 */
class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        // (string): belum pernah memilih = null, dan PHP 8.5 menolak null sebagai kunci array.
        $locale = (string) $request->session()->get('locale');

        if (! array_key_exists($locale, config('app.supported_locales'))) {
            $locale = config('app.locale');
        }

        app()->setLocale($locale);
        // Pemisah ribuan ikut bahasa (Number::format): 1.234 (id) / 1,234 (en). Tanggal Carbon sudah otomatis.
        Number::useLocale($locale);

        return $next($request);
    }
}
