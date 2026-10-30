<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class LocaleController extends Controller
{
    /**
     * Simpan bahasa pilihan di sesi lalu kembali ke halaman sebelumnya. POST (bukan link GET) karena mengubah
     * keadaan, sehingga ikut dilindungi CSRF.
     */
    public function __invoke(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'locale' => ['required', Rule::in(array_keys(config('app.supported_locales')))],
        ]);

        $request->session()->put('locale', $validated['locale']);

        return back();
    }
}
