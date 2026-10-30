{{--
    Tombol Simpan / Tersimpan (Baca Nanti) di halaman artikel publik. Dipakai di rel kiri (ikon saja)
    dan di kotak penulis (dengan label). Variabel: $class (class tombol), $withLabel (opsional),
    $article & $isBookmarked dari public/show.
    Tamu juga mengirim form ini: middleware auth mengarahkannya ke Masuk dengan pop-up penjelasan,
    lalu kembali ke artikel ini setelah login (lihat redirectGuestsTo di bootstrap/app.php).
--}}
@php($withLabel ??= false)

<form action="{{ route('bookmarks.toggle', $article) }}" method="POST">
    @csrf
    <button type="submit" aria-pressed="{{ $isBookmarked ? 'true' : 'false' }}"
            title="{{ $isBookmarked ? __('Hapus dari Baca Nanti') : __('Simpan ke Baca Nanti') }}"
            @class([$class, 'text-brand-700' => $isBookmarked, 'text-gray-600 hover:text-brand-700' => ! $isBookmarked])>
        <x-icon :name="$isBookmarked ? 'bookmark-solid' : 'bookmark'" :size="$withLabel ? 'size-3' : 'size-4'" />
        @if ($withLabel)
            {{ $isBookmarked ? __('Tersimpan') : __('Simpan') }}
        @else
            <span class="sr-only">{{ $isBookmarked ? __('Tersimpan di Baca Nanti') : __('Simpan ke Baca Nanti') }}</span>
        @endif
    </button>
</form>
