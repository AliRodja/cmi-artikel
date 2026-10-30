<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\View\View;

class ForgotPasswordController extends Controller
{
    public function create(): View
    {
        return view('auth.forgot-password');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $status = Password::sendResetLink($request->only('email'));

        // Jawaban selalu sama, baik email terdaftar, tidak terdaftar (INVALID_USER), maupun baru saja diminta
        // (RESET_THROTTLED). Kalau berbeda, orang bisa mengecek apakah email dokter/pasien tertentu punya akun.
        // Status lain (mis. gagal kirim) tetap ditampilkan sebagai error.
        if (! in_array($status, [Password::RESET_LINK_SENT, Password::INVALID_USER, Password::RESET_THROTTLED], true)) {
            return back()->withInput($request->only('email'))->withErrors(['email' => __($status)]);
        }

        // Email ikut dibawa (withInput) agar halaman bisa menampilkan alamatnya dan tombol kirim ulang.
        return back()->withInput($request->only('email'))->with('status', __(Password::RESET_LINK_SENT));
    }
}
