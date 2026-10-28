<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/*
 * Menjaga halaman yang seluruh isinya khusus admin (antrean review, laporan).
 * Bedanya dengan policy: middleware memeriksa SIAPA user-nya sebelum masuk
 * controller; policy memeriksa hubungan user dengan SATU artikel tertentu.
 */
class EnsureUserIsAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        abort_unless($request->user()?->isAdmin(), 403, 'Halaman ini khusus admin.');

        return $next($request);
    }
}
