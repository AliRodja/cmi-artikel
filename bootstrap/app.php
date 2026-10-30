<?php

use App\Http\Middleware\EnsureUserCanWrite;
use App\Http\Middleware\EnsureUserIsAdmin;
use App\Http\Middleware\SecurityHeaders;
use App\Http\Middleware\SetLocale;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Symfony\Component\HttpKernel\Exception\HttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'admin' => EnsureUserIsAdmin::class,
            'writer' => EnsureUserCanWrite::class,
        ]);

        $middleware->append(SecurityHeaders::class);

        // Setiap sesi menyimpan sidik password. Bila password diganti (Kelola Akun, reset lewat email, atau admin),
        // sesi di perangkat lain tidak cocok lagi dan otomatis keluar; sesi yang mengganti tetap login.
        // Cookie "ingat saya" juga dicek karena ikut memuat sidik password.
        $middleware->web(append: [AuthenticateSession::class, SetLocale::class]);

        // Bahasa dipasang tepat setelah sesi dimulai, sebelum route model binding: halaman 404 artikel Draft
        // (binding gagal) pun tampil dalam bahasa pilihan.
        $middleware->appendToPriorityList(after: StartSession::class, append: SetLocale::class);

        // User yang sudah login lalu membuka /login atau /register: pembaca ke beranda, lainnya ke dashboard.
        $middleware->redirectUsersTo(fn (Request $request) => $request->user()?->canWrite() ? route('dashboard') : route('home'));

        // Tamu yang menekan Simpan (Baca Nanti): diarahkan ke Masuk dengan pop-up penjelasan. Kembali ke artikelnya
        // setelah login sudah otomatis: untuk request POST, redirect()->guest() bawaan Laravel menyimpan halaman
        // sebelumnya (artikel tadi) sebagai tujuan redirect()->intended() di LoginController.
        $middleware->redirectGuestsTo(function (Request $request) {
            if ($request->routeIs('bookmarks.toggle')) {
                $request->session()->flash('error', __('Artikel belum tersimpan. Silakan masuk terlebih dahulu.'));
            }

            return route('login');
        });
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );

        // 419 = token CSRF kedaluwarsa (halaman dibuka terlalu lama / sudah keluar di tab lain). Daripada halaman
        // error "Page Expired", kembalikan ke halaman sebelumnya (dengan token baru) disertai pop-up gagal.
        $exceptions->render(function (HttpException $e, Request $request) {
            if ($e->getStatusCode() !== 419 || $request->expectsJson()) {
                return null;
            }

            $message = $request->routeIs('logout')
                ? __('Anda belum keluar karena sesi halaman sudah kedaluwarsa. Silakan coba lagi.')
                : __('Permintaan belum diproses karena halaman sudah terlalu lama dibuka. Silakan coba lagi.');

            return back()
                ->withInput($request->except(['_token', 'password', 'password_confirmation']))
                ->with('error', $message);
        });
    })->create();
