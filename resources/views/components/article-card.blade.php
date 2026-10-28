@props(['article'])

<article {{ $attributes->class('group card flex flex-col overflow-hidden transition hover:-translate-y-0.5 hover:shadow-lg') }}>
    <a href="{{ route('public.articles.show', $article) }}" class="block aspect-[16/10] overflow-hidden bg-brand-50">
        @if ($article->image_url)
            <img src="{{ $article->image_url }}" alt="{{ $article->title }}" loading="lazy"
                 class="size-full object-cover transition duration-300 group-hover:scale-105">
        @else
            <div class="flex size-full items-center justify-center bg-gradient-to-br from-brand-800 to-brand-950">
                <span class="text-4xl font-extrabold text-white/15">{{ Str::upper(Str::substr($article->title, 0, 1)) }}</span>
            </div>
        @endif
    </a>

    <div class="flex flex-1 flex-col p-5">
        <p class="text-xs font-medium text-gray-500">
            {{ $article->created_at->translatedFormat('d F Y') }} · {{ $article->reading_time }} menit baca
        </p>
        <h3 class="mt-2 line-clamp-2 text-lg leading-snug font-bold text-gray-900 group-hover:text-brand-700">
            <a href="{{ route('public.articles.show', $article) }}">{{ $article->title }}</a>
        </h3>
        <p class="mt-2 line-clamp-3 flex-1 text-sm leading-relaxed text-gray-600">{{ $article->excerpt }}</p>

        <div class="mt-5 flex items-center gap-2 border-t border-gray-100 pt-4">
            <x-avatar :name="$article->user?->name" />
            <span class="text-sm font-medium text-gray-700">{{ $article->user?->name ?? 'Anonim' }}</span>
        </div>
    </div>
</article>
