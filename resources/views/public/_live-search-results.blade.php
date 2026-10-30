{{--
    Hasil live search (PublicArticleController@liveSearch), ditempel ke panel cari navbar oleh
    resources/js/live-search.js. role="option" + id dipakai untuk navigasi panah (aria-activedescendant).
--}}
@if ($results->isEmpty())
    <div class="px-4 py-10 text-center">
        <x-icon name="magnifying-glass" size="size-7" class="text-gray-300" />
        <p class="mt-3 text-sm font-semibold text-gray-900">{{ __('Tidak ada artikel untuk ":term"', ['term' => $term]) }}</p>
        <p class="mt-1 text-sm text-gray-500">{{ __('Coba kata kunci lain, misalnya nama penyakit atau gejalanya.') }}</p>
    </div>
@else
    <p class="px-2 text-xs font-semibold tracking-wider text-gray-500 uppercase">{{ trans_choice(':count artikel ditemukan', $total) }}</p>
    <ul id="live-search-list" role="listbox" aria-label="{{ __('Hasil pencarian') }}" class="mt-2 space-y-1">
        @foreach ($results as $article)
            <li id="live-search-option-{{ $loop->index }}" role="option" aria-selected="false"
                class="rounded-lg transition aria-selected:bg-brand-50">
                <a href="{{ route('public.articles.show', $article) }}" tabindex="-1"
                   class="flex items-center gap-3 rounded-lg p-2 hover:bg-brand-50">
                    <x-article-thumb :article="$article" class="size-12" />
                    <div class="min-w-0 flex-1">
                        <p class="line-clamp-1 text-sm font-semibold text-gray-900">
                            <x-highlight :text="$article->title" :term="$term" />
                        </p>
                        <p class="mt-1 flex items-center gap-2 text-xs text-gray-500">
                            {{ $article->created_at->translatedFormat('d M Y') }}
                        </p>
                    </div>
                    <x-icon name="chevron-right" size="size-3" class="text-gray-300" />
                </a>
            </li>
        @endforeach
    </ul>
    <a href="{{ route('home', ['search' => $term]) }}#daftar-artikel"
       class="mt-3 flex items-center justify-center gap-1.5 rounded-lg border border-gray-200 px-4 py-2.5 text-sm font-medium text-brand-700 transition hover:bg-brand-50">
        {{ trans_choice('Lihat semua :count hasil untuk ":term"', $total, ['term' => $term]) }} <x-icon name="chevron-right" size="size-3" />
    </a>
@endif
