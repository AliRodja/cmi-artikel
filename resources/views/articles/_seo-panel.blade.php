{{--
    Optimasi SEO artikel (opsional) di form tulis/edit: kata kunci utama, judul & deskripsi untuk hasil Google,
    pratinjau tampilan di Google, dan checklist ala Yoast. Perilakunya di resources/js/seo-panel.js.
    Variabel: $article (null saat menulis artikel baru).
--}}
@php
    $checks = [
        'has-keyword' => 'Kata kunci utama sudah diisi',
        'keyword-title' => 'Kata kunci ada di judul (SEO)',
        'keyword-description' => 'Kata kunci ada di deskripsi (SEO)',
        'keyword-intro' => 'Kata kunci ada di paragraf pembuka artikel',
        'keyword-url' => 'Kata kunci ada di alamat (URL) artikel',
        'title-length' => 'Panjang judul 30–60 karakter',
        'description-length' => 'Panjang deskripsi 120–160 karakter',
    ];
@endphp

<section class="card p-6" data-seo-panel data-url-base="{{ url('/artikel') }}" data-slug="{{ $article?->slug }}">
    <x-form-step number="4" title="Optimasi SEO (opsional)" class="mb-0"
                 subtitle="Atur tampilan artikel di hasil pencarian Google. Kosongkan untuk memakai judul & ringkasan artikel." />

    {{-- Pratinjau hasil Google (diperbarui langsung oleh JS). --}}
    <div class="mt-5 rounded-xl border border-gray-200 bg-white p-4" aria-live="polite">
        <p class="text-xs font-semibold tracking-wider text-gray-500 uppercase">Pratinjau di Google</p>
        <p class="mt-3 truncate text-sm text-gray-700" data-seo-preview-url></p>
        <p class="mt-1 truncate text-xl leading-snug text-[#1a0dab]" data-seo-preview-title></p>
        <p class="mt-1 line-clamp-2 text-sm leading-relaxed text-gray-600" data-seo-preview-description></p>
    </div>

    <div class="mt-5">
        <label for="focus_keyword" class="form-label">Kata kunci utama</label>
        <div class="relative">
            <x-icon name="tag" size="size-4" class="pointer-events-none absolute top-1/2 left-3.5 -translate-y-1/2 text-gray-500" />
            <input type="text" id="focus_keyword" name="focus_keyword" maxlength="100"
                   value="{{ old('focus_keyword', $article?->focus_keyword) }}" placeholder="mis. gejala serangan jantung"
                   @class(['form-input pl-10', 'form-input-error' => $errors->has('focus_keyword')])>
        </div>
        <p class="mt-1 text-xs text-gray-500">Frasa yang kemungkinan diketik pembaca di Google untuk menemukan artikel ini.</p>
        @error('focus_keyword')
            <p class="form-error">{{ $message }}</p>
        @enderror
    </div>

    <div class="mt-4">
        <div class="flex items-baseline justify-between gap-3">
            <label for="seo_title" class="form-label">Judul SEO</label>
            <span class="text-xs font-medium" data-seo-counter="seo_title" data-min="30" data-max="60"></span>
        </div>
        <input type="text" id="seo_title" name="seo_title" maxlength="70"
               value="{{ old('seo_title', $article?->seo_title) }}" placeholder="Kosongkan untuk memakai judul artikel"
               @class(['form-input', 'form-input-error' => $errors->has('seo_title')])>
        @error('seo_title')
            <p class="form-error">{{ $message }}</p>
        @enderror
    </div>

    <div class="mt-4">
        <div class="flex items-baseline justify-between gap-3">
            <label for="seo_description" class="form-label">Deskripsi SEO</label>
            <span class="text-xs font-medium" data-seo-counter="seo_description" data-min="120" data-max="160"></span>
        </div>
        <textarea id="seo_description" name="seo_description" rows="3" maxlength="160"
                  placeholder="Kosongkan untuk memakai awal isi artikel. Tulis 1–2 kalimat yang menjawab pertanyaan pembaca."
                  @class(['form-input', 'form-input-error' => $errors->has('seo_description')])>{{ old('seo_description', $article?->seo_description) }}</textarea>
        @error('seo_description')
            <p class="form-error">{{ $message }}</p>
        @enderror
    </div>

    <div class="mt-5 rounded-xl bg-gray-50 p-4">
        <p class="flex items-center justify-between gap-3 text-sm font-semibold text-gray-900">
            Checklist SEO <span data-seo-score></span>
        </p>
        <ul class="mt-3 space-y-2 text-sm">
            @foreach ($checks as $key => $label)
                <li class="flex items-start gap-2" data-check="{{ $key }}">
                    <x-icon name="check-circle" size="size-4" class="mt-0.5 text-green-700" data-pass hidden />
                    <x-icon name="exclamation-triangle" size="size-4" class="mt-0.5 text-amber-600" data-fail />
                    <span class="text-gray-700">{{ $label }} <span class="text-gray-500" data-detail></span></span>
                </li>
            @endforeach
        </ul>
        <p class="mt-3 text-xs text-gray-500">
            Checklist membantu artikel lebih mudah ditemukan, tetapi bukan jaminan peringkat: Google juga menilai kualitas isi,
            kredibilitas situs, dan kebutuhan pembaca.
            @if ($article)
                Alamat (URL) artikel tidak berubah setelah dibuat agar tautan yang sudah dibagikan tetap berfungsi.
            @endif
        </p>
    </div>
</section>
