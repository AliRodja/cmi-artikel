<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

/**
 * Kelola Akun: setiap user (semua peran) mengubah data akunnya sendiri.
 * Selalu memakai $request->user(), tidak ada id di URL, jadi tidak mungkin mengubah akun orang lain.
 * Peran tidak bisa diubah di sini; itu wewenang admin di Kelola Pengguna (UserController).
 */
class ProfileController extends Controller
{
    public function edit(Request $request): View
    {
        $user = $request->user()->loadCount(['articles', 'comments', 'bookmarks']);

        return view('profile.edit', compact('user'));
    }

    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user)],
        ]);

        $user->update($validated);

        return back()->with('success', 'Data akun Anda berhasil diperbarui.');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            // Password lama wajib benar, agar orang yang meminjam perangkat yang masih login tidak bisa menggantinya.
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::min(8), 'different:current_password'],
        ], attributes: ['password' => 'password baru']);

        // Cast "hashed" di model User yang meng-hash password baru.
        $request->user()->update(['password' => $validated['password']]);

        return back()->with('success', 'Password Anda berhasil diganti.');
    }
}
