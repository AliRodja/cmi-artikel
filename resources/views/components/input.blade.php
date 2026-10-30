@props(['name', 'label', 'type' => 'text', 'value' => null, 'icon' => null, 'hint' => null])

{{--
    Kolom isian standar: label + input + pesan error.
    - icon: ikon di sisi kiri kolom (nama dari components/icon), mis. "envelope".
    - hint: teks bantuan kecil di bawah kolom (disembunyikan saat ada pesan error).
    - type="password": otomatis mendapat tombol tampilkan/sembunyikan password dan peringatan Caps Lock
      (perilakunya di resources/js/app.js).
--}}
<div class="mb-4">
    <label for="{{ $name }}" class="form-label">{{ $label }}</label>
    <div class="relative">
        @if ($icon)
            <x-icon :name="$icon" size="size-4" class="pointer-events-none absolute top-1/2 left-3.5 -translate-y-1/2 text-gray-500" />
        @endif
        <input type="{{ $type }}"
               id="{{ $name }}"
               name="{{ $name }}"
               value="{{ $type === 'password' ? '' : old($name, $value) }}"
               {{ $attributes->class(['form-input', 'pl-10' => $icon, 'pr-11' => $type === 'password', 'form-input-error' => $errors->has($name)]) }}>
        @if ($type === 'password')
            <button type="button" data-password-toggle aria-controls="{{ $name }}" aria-pressed="false" aria-label="{{ __('Tampilkan password') }}"
                    data-label-show="{{ __('Tampilkan password') }}" data-label-hide="{{ __('Sembunyikan password') }}"
                    class="absolute inset-y-0 right-0 flex w-11 cursor-pointer items-center justify-center rounded-r-lg text-gray-500 transition hover:text-gray-700">
                <x-icon name="eye" size="size-4" data-password-show />
                <x-icon name="eye-slash" size="size-4" data-password-hide hidden />
            </button>
        @endif
    </div>
    @if ($type === 'password')
        <p id="{{ $name }}-capslock" class="mt-1 flex items-center gap-1.5 text-xs font-medium text-amber-700" hidden>
            <x-icon name="exclamation-triangle" size="size-3" /> {{ __('Caps Lock aktif') }}
        </p>
    @endif
    @error($name)
        <p class="form-error">{{ $message }}</p>
    @else
        @if ($hint)
            <p id="{{ $name }}-hint" class="mt-1 text-xs text-gray-500">{{ $hint }}</p>
        @endif
    @enderror
</div>
