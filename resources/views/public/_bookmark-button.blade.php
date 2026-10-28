{{--
    Tombol Simpan / Tersimpan (Baca Nanti) di halaman artikel publik. Dipakai di rel kiri (ikon saja)
    dan di kotak penulis (dengan label). Tamu diarahkan ke halaman Masuk.
    Variabel: $class (class tombol), $withLabel (opsional), $article & $isBookmarked dari public/show.
--}}
@php($withLabel ??= false)

@auth
    <form action="{{ route('bookmarks.toggle', $article) }}" method="POST">
        @csrf
        <button type="submit" aria-pressed="{{ $isBookmarked ? 'true' : 'false' }}"
                title="{{ $isBookmarked ? 'Hapus dari Baca Nanti' : 'Simpan ke Baca Nanti' }}"
                @class([$class, 'text-brand-700' => $isBookmarked, 'text-gray-600 hover:text-brand-700' => ! $isBookmarked])>
            <x-icon :name="$isBookmarked ? 'bookmark-solid' : 'bookmark'" :size="$withLabel ? 'size-3' : 'size-4'" />
            @if ($withLabel)
                {{ $isBookmarked ? 'Tersimpan' : 'Simpan' }}
            @else
                <span class="sr-only">{{ $isBookmarked ? 'Tersimpan di Baca Nanti' : 'Simpan ke Baca Nanti' }}</span>
            @endif
        </button>
    </form>
@else
    <a href="{{ route('login') }}" title="Masuk untuk menyimpan artikel" @class([$class, 'text-gray-600 hover:text-brand-700'])>
        <x-icon name="bookmark" :size="$withLabel ? 'size-3' : 'size-4'" />
        @if ($withLabel)
            Simpan
        @else
            <span class="sr-only">Simpan ke Baca Nanti</span>
        @endif
    </a>
@endauth
