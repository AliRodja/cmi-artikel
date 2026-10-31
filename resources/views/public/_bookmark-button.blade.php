{{--
    Tombol Simpan / Tersimpan (Baca Nanti) di halaman artikel publik. Dipakai di rel kiri (ikon saja)
    dan di kotak penulis (dengan label). Variabel: $class (class tombol), $withLabel (opsional),
    $article & $isBookmarked dari public/show.
    Untuk pengguna yang login, aksi dijalankan via Livewire tanpa muat ulang halaman.
--}}
<livewire:bookmark-button
    :article="$article"
    :is-bookmarked="$isBookmarked"
    :button-class="$class"
    :with-label="$withLabel ?? false"
/>
