@props(['article'])

{{-- Badge jenis artikel. Artikel lama tanpa jenis tidak menampilkan apa pun. --}}
@if ($article->category_label)
    <span {{ $attributes->class([
        'badge',
        'bg-amber-50 text-amber-700' => $article->category === 'diabetes',
        'bg-red-50 text-red-700' => $article->category === 'jantung',
        'bg-violet-50 text-violet-700' => $article->category === 'kanker',
        'bg-teal-50 text-teal-700' => $article->category === 'ginjal',
    ]) }}>{{ $article->category_label }}</span>
@endif
