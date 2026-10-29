@props(['article' => null, 'category' => null])

@php
    // Bisa menerima model Article, atau kode jenis saja (mis. baris hasil DB::table di Laporan Artikel,
    // yang bukan model sehingga tidak punya accessor category_label).
    $category ??= $article?->category;
    $label = $category ? (\App\Models\Article::CATEGORIES[$category] ?? null) : null;
@endphp

{{-- Badge jenis artikel. Artikel lama tanpa jenis tidak menampilkan apa pun. --}}
@if ($label)
    <span {{ $attributes->class([
        'badge',
        'bg-amber-50 text-amber-700' => $category === 'diabetes',
        'bg-red-50 text-red-700' => $category === 'jantung',
        'bg-violet-50 text-violet-700' => $category === 'kanker',
        'bg-teal-50 text-teal-700' => $category === 'ginjal',
    ]) }}>{{ $label }}</span>
@endif
