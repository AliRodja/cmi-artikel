{{--
    Pilihan bahasa tampilan (Popover API, sama dengan partials/user-menu; posisi diatur resources/js/app.js).
    Tiap bahasa = tombol submit form POST /bahasa (LocaleController), lalu kembali ke halaman yang sama.
    Nama bahasa ditulis dalam bahasanya sendiri agar mudah dikenali, apa pun bahasa yang sedang aktif.
--}}
<div id="locale-menu" popover class="dropdown-panel w-64 overflow-hidden">
    <div class="h-0.5 bg-gradient-to-r from-brand-950 via-brand-600 to-amber-400" aria-hidden="true"></div>
    <p class="border-b border-brand-100/80 bg-brand-50/50 px-4 py-3 text-sm font-semibold text-brand-950">{{ __('Bahasa tampilan') }}</p>
    <form action="{{ route('locale.update') }}" method="POST" class="space-y-1 p-2">
        @csrf
        @foreach (config('app.supported_locales') as $code => $locale)
            <button type="submit" name="locale" value="{{ $code }}" lang="{{ $code }}"
                    @class(['drawer-link w-full cursor-pointer', 'drawer-link-active' => app()->getLocale() === $code])
                    @if (app()->getLocale() === $code) aria-current="true" @endif>
                <span class="w-7 rounded bg-gray-100 px-1.5 py-0.5 text-center text-xs font-bold text-brand-800">{{ $locale['short'] }}</span>
                <span class="flex-1 text-left">{{ $locale['label'] }}</span>
                @if (app()->getLocale() === $code)
                    <x-icon name="check" size="size-4" class="text-brand-700" />
                @endif
            </button>
        @endforeach
    </form>
    <p class="border-t border-gray-100 bg-gray-50/70 px-4 py-3 text-xs leading-relaxed text-gray-500">
        {{ __('Artikel tampil dalam Bahasa Inggris bila penulis sudah menyediakan terjemahannya.') }}
    </p>
</div>
