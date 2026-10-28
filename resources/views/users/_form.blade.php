@use('App\Models\User')

@php
    $user ??= null;
    // Admin tidak boleh mengubah perannya sendiri; aturan yang sama dicek lagi di UserRequest.
    $canChangeRole = ! $user || auth()->user()->can('changeRole', $user);

    $roleOptions = [
        User::ROLE_PEMBACA => ['label' => 'Pembaca', 'hint' => 'Membaca, berkomentar, dan menyimpan artikel.'],
        User::ROLE_PENULIS => ['label' => 'Penulis', 'hint' => 'Menulis artikel dan mengajukannya ke admin.'],
        User::ROLE_ADMIN => ['label' => 'Admin', 'hint' => 'Akses penuh: review, laporan, kelola pengguna.'],
    ];
    $currentRole = old('role', $user?->role ?? User::ROLE_PEMBACA);
@endphp

<div class="grid items-start gap-6 lg:grid-cols-3">
    <div class="min-w-0 space-y-6 lg:col-span-2">
        <section class="card p-6">
            <h2 class="mb-4 font-bold text-gray-900">Data Akun</h2>
            <x-input name="name" label="Nama" :value="$user?->name" required maxlength="255" autocomplete="off" />
            <x-input name="email" label="Email" type="email" :value="$user?->email" required maxlength="255" autocomplete="off" />
        </section>

        <section class="card p-6">
            <h2 class="font-bold text-gray-900">{{ $user ? 'Ganti Password' : 'Password' }}</h2>
            <p class="mt-1 mb-4 text-xs text-gray-500">
                {{ $user ? 'Kosongkan bila password tidak ingin diganti.' : 'Minimal 8 karakter. Sampaikan ke pengguna agar segera menggantinya lewat Lupa Password.' }}
            </p>
            <div class="grid gap-x-4 sm:grid-cols-2">
                <x-input name="password" label="{{ $user ? 'Password baru' : 'Password' }}" type="password"
                         autocomplete="new-password" :required="! $user" />
                <x-input name="password_confirmation" label="Konfirmasi password" type="password"
                         autocomplete="new-password" :required="! $user" />
            </div>
        </section>
    </div>

    <aside class="min-w-0 space-y-6 lg:sticky lg:top-24">
        <section class="card p-5">
            <h2 class="font-bold text-gray-900">Peran</h2>

            @if ($canChangeRole)
                <fieldset class="mt-4 space-y-2">
                    <legend class="sr-only">Peran</legend>
                    @foreach ($roleOptions as $value => $option)
                        <label class="flex cursor-pointer gap-3 rounded-lg border border-gray-200 p-3 transition has-checked:border-brand-500 has-checked:bg-brand-50/60">
                            <input type="radio" name="role" value="{{ $value }}" @checked($currentRole === $value)
                                   class="mt-0.5 accent-brand-600">
                            <span>
                                <span class="block text-sm font-semibold text-gray-900">{{ $option['label'] }}</span>
                                <span class="block text-xs text-gray-500">{{ $option['hint'] }}</span>
                            </span>
                        </label>
                    @endforeach
                </fieldset>
            @else
                <div class="mt-4 rounded-lg border border-gray-200 bg-gray-50 p-3">
                    <x-role-badge :user="$user" />
                    <p class="mt-2 text-xs text-gray-500">Anda tidak dapat mengubah peran akun Anda sendiri, agar situs selalu punya admin.</p>
                </div>
            @endif
            @error('role')
                <p class="form-error">{{ $message }}</p>
            @enderror

            <div class="mt-5 flex flex-col gap-2">
                <button type="submit" class="btn btn-primary w-full">{{ $submitLabel }}</button>
                <a href="{{ route('users.index') }}" class="btn btn-secondary w-full">Batal</a>
            </div>
        </section>
    </aside>
</div>
