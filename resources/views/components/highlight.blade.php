@props(['text', 'term'])

@php
    // Pecah teks di setiap kata kunci (huruf besar/kecil diabaikan). PREG_SPLIT_DELIM_CAPTURE ikut menyimpan
    // potongan yang cocok di indeks ganjil, jadi bisa dibungkus <mark> sambil tetap memakai {{ }} (aman XSS).
    $parts = $term === '' ? [$text] : preg_split('/('.preg_quote($term, '/').')/iu', $text, -1, PREG_SPLIT_DELIM_CAPTURE);
@endphp
{{-- Satu directive per baris: PHP menelan baris baru setelah ?>, jadi tidak ada spasi tambahan di teks. --}}
@foreach ($parts as $index => $part)
@if ($index % 2 === 1)<mark class="rounded-sm bg-amber-100 px-0.5 text-inherit">{{ $part }}</mark>@else{{ $part }}@endif
@endforeach
