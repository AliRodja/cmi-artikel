@props(['title', 'subtitle' => null])

{{--
    Kartu halaman autentikasi (masuk, daftar, lupa & reset password).
    Slot opsional "aside": panel biru di sisi kiri (hanya layar lebar), mis. sambutan di halaman Masuk.
--}}
<div @class(['mx-auto w-full py-4', 'max-w-md' => ! isset($aside), 'max-w-5xl' => isset($aside)])>
    <div @class(['card overflow-hidden', 'lg:grid lg:grid-cols-[5fr_6fr]' => isset($aside)])>
        @isset($aside)
            <aside class="relative hidden overflow-hidden bg-brand-950 p-10 text-white lg:flex lg:flex-col">
                <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_80%_10%,var(--color-brand-700),transparent_50%)] opacity-70"></div>
                <div class="relative flex h-full flex-col">{{ $aside }}</div>
            </aside>
        @endisset

        <div>
            <div @class(['h-1.5 bg-brand-600', 'lg:hidden' => isset($aside)])></div>
            <div @class(['p-6 sm:p-8', 'lg:p-12' => isset($aside)])>
                {{-- Di layar lebar logo sudah tampil di panel kiri (aside). --}}
                <x-logo :class="isset($aside) ? 'mb-6 lg:hidden' : 'mb-6'" />
                <h1 class="text-2xl font-extrabold tracking-tight text-gray-900">{{ $title }}</h1>
                @if ($subtitle)
                    <p class="mt-1 text-sm text-gray-500">{{ $subtitle }}</p>
                @endif

                <div class="mt-6">
                    {{ $slot }}
                </div>
            </div>
        </div>
    </div>

    @isset($footer)
        <p class="mt-5 text-center text-sm text-gray-600">{{ $footer }}</p>
    @endisset
</div>
