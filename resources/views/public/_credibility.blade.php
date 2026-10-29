{{-- Tinjauan medis & sumber rujukan (dipakai halaman publik dan pratinjau di area admin). Variabel: $article. --}}
@if ($article->medicalReviewer)
    <div class="flex items-start gap-3 rounded-xl bg-brand-50 p-4">
        <span class="flex size-10 shrink-0 items-center justify-center rounded-full bg-white text-brand-600">
            <x-icon name="shield" size="size-5" />
        </span>
        <div>
            <p class="text-sm font-semibold text-gray-900">Ditinjau secara medis oleh {{ $article->medicalReviewer->name }}</p>
            @if ($article->medicalReviewer->profession)
                <p class="text-sm text-gray-700">{{ $article->medicalReviewer->profession }}</p>
            @endif
            <p class="mt-1 text-xs text-gray-600">Terakhir diperbarui <x-date :value="$article->updated_at" format="d F Y" /></p>
        </div>
    </div>
@endif

@if ($article->source_list)
    <section>
        <h2 class="text-sm font-bold tracking-wider text-gray-600 uppercase">Sumber rujukan</h2>
        <ol class="mt-3 list-decimal space-y-2 pl-5 text-sm leading-relaxed text-gray-700">
            @foreach ($article->source_list as $source)
                <li><x-source-line :line="$source" /></li>
            @endforeach
        </ol>
    </section>
@endif
