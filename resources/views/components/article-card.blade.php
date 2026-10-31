@props(['article'])

<article {{ $attributes->class('group card flex flex-col overflow-hidden border-gray-200/90 transition duration-300 hover:-translate-y-0.5 hover:border-brand-300 hover:shadow-lg') }}>
    <a href="{{ route('public.articles.show', $article) }}" class="block aspect-[16/10] overflow-hidden bg-brand-950">
        @if ($article->image_url)
            <img src="{{ $article->image_url }}" alt="{{ $article->display_title }}" loading="lazy"
                 class="size-full object-cover transition duration-500 group-hover:scale-105">
        @else
            <div class="flex size-full items-center justify-center bg-gradient-to-br from-brand-800 to-brand-950">
                <span class="text-4xl font-extrabold text-white/15">{{ Str::upper(Str::substr($article->display_title, 0, 1)) }}</span>
            </div>
        @endif
    </a>

    <div class="flex flex-1 flex-col p-5 sm:p-6">
        <div class="flex flex-wrap items-center justify-between gap-2 text-xs font-medium text-gray-500">
            <span class="inline-flex items-center gap-1.5">
                <x-icon name="calendar" size="size-3" class="text-brand-600" />
                {{ $article->created_at->translatedFormat('d F Y') }}
            </span>
            @if ($article->medical_reviewer_id)
                <span class="inline-flex items-center gap-1 rounded-full bg-brand-50 px-2.5 py-0.5 text-xs font-medium text-brand-800 ring-1 ring-brand-200/70">
                    <x-icon name="shield" size="size-3" class="text-brand-700" />
                    {{ __('Ditinjau Medis') }}
                </span>
            @endif
        </div>

        <h3 class="mt-2.5 line-clamp-2 font-article text-lg leading-snug font-bold text-gray-900 transition group-hover:text-brand-700">
            <a href="{{ route('public.articles.show', $article) }}">{{ $article->display_title }}</a>
        </h3>
        <p class="mt-2 line-clamp-3 flex-1 text-sm leading-relaxed text-gray-600">{{ $article->display_excerpt }}</p>

        <div class="mt-5 flex items-center gap-3 border-t border-gray-100 pt-4">
            <x-avatar :name="$article->user?->name" />
            <div class="min-w-0 flex-1 leading-tight">
                <p class="truncate text-sm font-semibold text-gray-900">{{ $article->user?->name ?? __('Anonim') }}</p>
                @if ($article->user?->profession)
                    <p class="mt-0.5 truncate text-xs text-gray-500">{{ $article->user->profession }}</p>
                @endif
            </div>
        </div>
    </div>
</article>
