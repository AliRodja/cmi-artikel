<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/*
 * Menjaga area penulis (dashboard & kelola artikel): hanya admin dan penulis.
 * Pembaca tetap bisa membaca, berkomentar, dan menyimpan artikel di halaman publik.
 */
class EnsureUserCanWrite
{
    public function handle(Request $request, Closure $next): Response
    {
        abort_unless($request->user()?->canWrite(), 403, 'Halaman ini khusus penulis dan admin.');

        return $next($request);
    }
}
