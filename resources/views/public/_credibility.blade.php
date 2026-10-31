{{-- Tinjauan medis & sumber rujukan (dipakai halaman publik dan pratinjau di area admin). Variabel: $article. --}}
@if ($article->medicalReviewer)
    <div class="flex items-start gap-3.5 rounded-xl border border-emerald-200/80 bg-emerald-50/70 p-4">
        <span class="flex size-10 shrink-0 items-center justify-center rounded-full bg-white text-emerald-600 shadow-2xs ring-1 ring-emerald-200/70">
            <x-icon name="shield" size="size-5" />
        </span>
        <div>
            <p class="text-sm font-bold text-gray-900">{{ __('Ditinjau secara medis oleh :name', ['name' => $article->medicalReviewer->name]) }}</p>
            @if ($article->medicalReviewer->profession)
                <p class="text-sm font-medium text-gray-700">{{ $article->medicalReviewer->profession }}</p>
            @endif
            <p class="mt-1 text-xs text-gray-600">{{ __('Terakhir diperbarui') }} <x-date :value="$article->updated_at" format="d F Y" /></p>
        </div>
    </div>
@endif

@if ($article->source_list)
    <section class="rounded-xl border border-gray-200/90 bg-white p-4 sm:p-5">
        <h2 class="flex items-center gap-2 text-xs font-bold tracking-wider text-brand-700 uppercase">
            <x-icon name="document-text" size="size-4" class="text-brand-600" />
            {{ __('Sumber rujukan') }}
        </h2>
        <ol class="mt-3 list-decimal space-y-2 pl-5 text-sm leading-relaxed text-gray-700">
            @foreach ($article->source_list as $source)
                <li><x-source-line :line="$source" /></li>
            @endforeach
        </ol>
    </section>
@endif
