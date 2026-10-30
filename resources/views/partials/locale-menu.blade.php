{{--
    Pilihan bahasa tampilan (Popover API, sama dengan partials/user-menu; posisi diatur resources/js/app.js).
    Tiap bahasa = tombol submit form POST /bahasa (LocaleController), lalu kembali ke halaman yang sama.
    Nama bahasa ditulis dalam bahasanya sendiri agar mudah dikenali, apa pun bahasa yang sedang aktif.
--}}
<div id="locale-menu" popover class="dropdown-panel w-64">
    <p class="border-b border-gray-100 px-4 py-3 text-sm font-semibold text-gray-900">{{ __('Bahasa tampilan') }}</p>
    <form action="{{ route('locale.update') }}" method="POST" class="p-2">
        @csrf
        @foreach (config('app.supported_locales') as $code => $locale)
            <button type="submit" name="locale" value="{{ $code }}" lang="{{ $code }}"
                    @class(['drawer-link w-full cursor-pointer', 'drawer-link-active' => app()->getLocale() === $code])
                    @if (app()->getLocale() === $code) aria-current="true" @endif>
                <span class="w-7 text-xs font-bold text-gray-500">{{ $locale['short'] }}</span>
                <span class="flex-1 text-left">{{ $locale['label'] }}</span>
                @if (app()->getLocale() === $code)
                    <x-icon name="check" size="size-4" class="text-brand-600" />
                @endif
            </button>
        @endforeach
    </form>
    <p class="border-t border-gray-100 px-4 py-3 text-xs leading-relaxed text-gray-500">
        {{ __('Isi artikel tetap ditulis dalam Bahasa Indonesia.') }}
    </p>
</div>
