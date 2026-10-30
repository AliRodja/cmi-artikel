{{--
    Langkah 5 form tulis/edit: versi Bahasa Inggris yang diterjemahkan sendiri oleh penulis (opsional).
    Tampil untuk pengunjung yang memilih English; kosong = mereka tetap melihat versi Indonesia.
    Tertutup bila belum ada terjemahan, terbuka otomatis bila sudah terisi atau ada error validasi.
    Variabel: $article (null saat menulis artikel baru).
--}}
@php
    $englishFields = ['title_en', 'content_en', 'seo_title_en', 'seo_description_en'];
    $isOpen = $article?->hasEnglish()
        || collect($englishFields)->contains(fn ($field) => filled(old($field)) || $errors->has($field));
@endphp

<details class="card group p-6" @if ($isOpen) open @endif>
    <summary class="flex cursor-pointer list-none items-start justify-between gap-3 [&::-webkit-details-marker]:hidden">
        <x-form-step number="5" :title="__('Versi Bahasa Inggris (opsional)')" class="mb-0"
                     :subtitle="__('Terjemahkan sendiri artikel ini agar pengunjung yang memilih English bisa membacanya. Kosongkan bila belum ada.')" />
        <span class="mt-1.5 shrink-0 text-gray-500 transition group-open:rotate-180">
            <x-icon name="chevron-down" size="size-4" />
        </span>
    </summary>

    <div class="mt-5 space-y-4">
        <div>
            <label for="title_en" class="form-label">{{ __('Judul (Bahasa Inggris)') }}</label>
            <input type="text" id="title_en" name="title_en" lang="en" maxlength="255"
                   value="{{ old('title_en', $article?->title_en) }}"
                   placeholder="{{ __('mis. Heart Attack Symptoms That Are Often Ignored') }}"
                   @class(['form-input', 'form-input-error' => $errors->has('title_en')])>
            @error('title_en')
                <p class="form-error">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="content_en-editor" class="form-label">{{ __('Isi artikel (Bahasa Inggris)') }}</label>
            {{-- Editor Trix kedua; HTML-nya disanitasi di Article::setContentEnAttribute, sama seperti isi Indonesia. --}}
            <input type="hidden" id="content_en" name="content_en" value="{{ old('content_en', $article?->content_en_html) }}">
            <trix-editor id="content_en-editor" input="content_en" lang="en" placeholder="{{ __('Tulis terjemahan isi artikel...') }}"
                         @class(['article-editor article-content', 'article-editor-error' => $errors->has('content_en')])></trix-editor>
            @error('content_en')
                <p class="form-error">{{ $message }}</p>
            @enderror
            <p class="mt-1 text-xs text-gray-500">{{ __('Judul dan isi Bahasa Inggris wajib diisi berpasangan. Gambar dan sumber rujukan memakai yang sama.') }}</p>
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <label for="seo_title_en" class="form-label">{{ __('Judul SEO (Bahasa Inggris)') }}</label>
                <input type="text" id="seo_title_en" name="seo_title_en" lang="en" maxlength="70"
                       value="{{ old('seo_title_en', $article?->seo_title_en) }}"
                       placeholder="{{ __('Kosongkan untuk memakai judul Bahasa Inggris') }}"
                       @class(['form-input', 'form-input-error' => $errors->has('seo_title_en')])>
                @error('seo_title_en')
                    <p class="form-error">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label for="seo_description_en" class="form-label">{{ __('Deskripsi SEO (Bahasa Inggris)') }}</label>
                <textarea id="seo_description_en" name="seo_description_en" lang="en" rows="2" maxlength="160"
                          placeholder="{{ __('Kosongkan untuk memakai awal isi Bahasa Inggris') }}"
                          @class(['form-input', 'form-input-error' => $errors->has('seo_description_en')])>{{ old('seo_description_en', $article?->seo_description_en) }}</textarea>
                @error('seo_description_en')
                    <p class="form-error">{{ $message }}</p>
                @enderror
            </div>
        </div>
    </div>
</details>
