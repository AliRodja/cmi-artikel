@use('App\Models\Article')

@php
    $article ??= null;
    $isAdmin = auth()->user()->isAdmin();

    $labels = [
        Article::STATUS_DRAFT => ['label' => __('Draft'), 'hint' => $isAdmin ? __('Disimpan, belum tampil di situs publik.') : __('Simpan dulu, belum dikirim ke admin.')],
        Article::STATUS_REVIEW => $isAdmin
            ? ['label' => __('Menunggu Review'), 'hint' => __('Biarkan tetap di antrean review.')]
            : ['label' => __('Ajukan ke Admin'), 'hint' => __('Admin meninjau dulu sebelum artikel tayang.')],
        Article::STATUS_PUBLISHED => ['label' => __('Published'), 'hint' => __('Langsung tampil untuk semua pengunjung.')],
    ];

    // Pilihan status mengikuti aturan yang sama dengan validasi di ArticleRequest.
    $statusOptions = collect(Article::allowedStatusesFor(auth()->user(), $article))
        ->map(fn (int $value) => ['value' => (string) $value] + $labels[$value]);

    $currentStatus = (string) old('status', $article?->status ?? Article::STATUS_DRAFT);
    if (! $statusOptions->contains('value', $currentStatus)) {
        $currentStatus = (string) Article::STATUS_DRAFT;
    }
@endphp

@php
    // Kelengkapan artikel (diperbarui langsung oleh resources/js/article-form.js).
    $completeness = [
        'title' => __('Judul jelas (minimal 10 karakter)'),
        'content' => __('Isi minimal 300 kata'),
        'image' => __('Gambar utama'),
        'sources' => __('Sumber rujukan'),
        'keyword' => __('Kata kunci SEO'),
    ];
@endphp

{{-- data-text-words: templat penghitung kata untuk resources/js/article-form.js (ikut bahasa). --}}
<div class="grid items-start gap-6 lg:grid-cols-3" data-article-form data-text-words="{{ __(':count kata') }}">
    {{-- Kolom utama: langkah 1–4 --}}
    <div class="min-w-0 space-y-6 lg:col-span-2">
        <section class="card p-6">
            <x-form-step number="1" :title="__('Judul artikel')" :subtitle="__('Judul yang jelas membantu pembaca dan Google memahami isi artikel.')" />

            <div>
                <label for="title" class="form-label">{{ __('Judul') }}</label>
                <input type="text" id="title" name="title" value="{{ old('title', $article?->title) }}" required maxlength="255"
                       placeholder="{{ __('mis. Gejala Serangan Jantung yang Sering Diabaikan') }}"
                       @class(['form-input py-3 text-lg font-semibold', 'form-input-error' => $errors->has('title')])>
                @error('title')
                    <p class="form-error">{{ $message }}</p>
                @enderror
            </div>
        </section>

        <section class="card p-6">
            <x-form-step number="2" :title="__('Isi artikel')" :subtitle="__('Gunakan subjudul dan daftar agar mudah dibaca. Toolbar ikut menempel saat Anda menulis.')" />

            <label for="content-editor" class="sr-only">{{ __('Isi artikel') }}</label>
            {{-- Trix menulis HTML ke input tersembunyi ini; nilainya yang dikirim ke server. --}}
            <input type="hidden" id="content" name="content" value="{{ old('content', $article?->content_html) }}">
            <trix-editor id="content-editor" input="content" placeholder="{{ __('Mulai menulis isi artikel...') }}"
                         @class(['article-editor article-content', 'article-editor-error' => $errors->has('content')])></trix-editor>
            @error('content')
                <p class="form-error">{{ $message }}</p>
            @enderror
            <p class="mt-2 flex flex-wrap items-center justify-between gap-2 text-sm text-gray-600">
                <span data-word-count>{{ __(':count kata', ['count' => 0]) }}</span>
                <span class="text-gray-500">{{ __('Artikel kesehatan yang lengkap biasanya minimal 300 kata.') }}</span>
            </p>
        </section>

        <section class="card p-6">
            <x-form-step number="3" :title="__('Sumber rujukan')" :subtitle="__('Opsional, tetapi membuat pembaca lebih percaya. Tampil di akhir artikel.')" />

            <label for="sources" class="sr-only">{{ __('Sumber rujukan') }}</label>
            <textarea id="sources" name="sources" rows="4" maxlength="5000"
                      placeholder="{{ __('Satu sumber per baris, mis.') }}&#10;Kementerian Kesehatan RI, Pedoman Hipertensi 2024 https://kemkes.go.id/..."
                      @class(['form-input', 'form-input-error' => $errors->has('sources')])>{{ old('sources', $article?->sources) }}</textarea>
            <p class="mt-1 text-xs text-gray-500">{{ __('Satu sumber per baris. Tautan yang diawali http:// atau https:// otomatis bisa diklik.') }}</p>
            @error('sources')
                <p class="form-error">{{ $message }}</p>
            @enderror
        </section>

        @include('articles._seo-panel')
        @include('articles._english-version')
    </div>

    {{-- Sidebar: ikut menempel saat scroll (bisa di-scroll sendiri bila lebih tinggi dari layar). --}}
    <aside class="min-w-0 space-y-6 lg:sticky lg:top-24 lg:max-h-[calc(100vh-7rem)] lg:overflow-y-auto lg:pb-2">
        <section class="card p-5">
            <h2 class="font-bold text-gray-900">{{ __('Publikasi') }}</h2>
            @unless ($isAdmin)
                <p class="mt-1 text-sm text-gray-600">{{ __('Artikel tayang setelah disetujui admin.') }}</p>
            @endunless

            <fieldset class="mt-4 space-y-2">
                <legend class="sr-only">{{ __('Status') }}</legend>
                @foreach ($statusOptions as $option)
                    <label class="flex cursor-pointer gap-3 rounded-lg border border-gray-200 p-3 transition has-checked:border-brand-500 has-checked:bg-brand-50/60">
                        <input type="radio" name="status" value="{{ $option['value'] }}" @checked($currentStatus === $option['value'])
                               class="mt-0.5 accent-brand-600">
                        <span>
                            <span class="block text-sm font-semibold text-gray-900">{{ $option['label'] }}</span>
                            <span class="block text-xs text-gray-600">{{ $option['hint'] }}</span>
                        </span>
                    </label>
                @endforeach
            </fieldset>
            @error('status')
                <p class="form-error">{{ $message }}</p>
            @enderror

            @if ($isAdmin)
                <div class="mt-5">
                    @include('articles._reviewer-select', ['selected' => old('medical_reviewer_id', $article?->medical_reviewer_id)])
                </div>
            @endif

            <div class="mt-5 flex flex-col gap-2">
                <button type="submit" class="btn btn-primary w-full py-2.5">
                    <x-icon name="check" size="size-4" /> {{ $submitLabel }}
                </button>
                <a href="{{ $cancelUrl }}" class="btn btn-secondary w-full py-2.5">{{ __('Batal') }}</a>
            </div>
        </section>

        <section class="card p-5">
            <div class="flex items-center justify-between gap-3">
                <h2 class="font-bold text-gray-900">{{ __('Kelengkapan artikel') }}</h2>
                <span class="text-sm font-semibold text-gray-700" data-completeness-score></span>
            </div>
            <div class="mt-3 h-2 overflow-hidden rounded-full bg-gray-100">
                <div class="h-full rounded-full bg-brand-600 transition-all" style="width: 0%" data-completeness-bar></div>
            </div>
            <ul class="mt-4 space-y-2 text-sm">
                @foreach ($completeness as $key => $label)
                    <li class="flex items-center gap-2" data-completeness="{{ $key }}">
                        <x-icon name="check-circle" size="size-4" class="text-green-700" data-pass hidden />
                        <span class="flex size-4 shrink-0 items-center justify-center" data-fail><span class="size-3 rounded-full border-2 border-gray-300"></span></span>
                        <span class="text-gray-700">{{ $label }}</span>
                    </li>
                @endforeach
            </ul>
            <p class="mt-3 text-xs text-gray-500">{{ __('Hanya judul dan isi yang wajib. Sisanya membuat artikel lebih lengkap dan tepercaya.') }}</p>
        </section>

        <section class="card p-5">
            <h2 class="font-bold text-gray-900">{{ __('Gambar Utama') }}</h2>
            <p class="mt-1 text-sm text-gray-600">{{ __('Opsional · JPG, PNG, atau WebP · maks 2 MB') }}</p>

            @if ($article?->image_url)
                <div id="current-image" class="mt-4">
                    <img src="{{ $article->image_url }}" alt="{{ __('Gambar saat ini') }}" class="aspect-[16/10] w-full rounded-lg object-cover">
                    <label class="mt-2 flex items-center gap-2 text-sm text-gray-700">
                        <input type="checkbox" id="remove_image" name="remove_image" value="1" class="size-4 rounded border-gray-300 accent-red-600" @checked(old('remove_image'))>
                        {{ __('Hapus gambar ini') }}
                    </label>
                </div>
            @endif

            {{-- Input file transparan menutupi seluruh area, sehingga klik maupun drag & drop langsung diterima. --}}
            <div @class([
                'relative mt-4 rounded-lg border-2 border-dashed px-4 py-6 text-center transition hover:border-brand-400 hover:bg-brand-50/50',
                'border-red-400' => $errors->has('image'),
                'border-gray-300' => ! $errors->has('image'),
            ])>
                <input type="file" id="image" name="image" accept="image/jpeg,image/png,image/webp"
                       data-has-image="{{ $article?->image_url ? '1' : '0' }}"
                       class="absolute inset-0 size-full cursor-pointer opacity-0">
                <x-icon name="cloud-arrow-up" size="size-8" class="mx-auto text-gray-500" />
                <p class="mt-2 text-sm font-medium text-gray-700">
                    {{ $article?->image_url ? __('Ganti gambar') : __('Pilih atau seret gambar ke sini') }}
                </p>
                <p id="image-name" class="mt-1 truncate text-xs text-gray-500"></p>
            </div>
            @error('image')
                <p class="form-error">{{ $message }}</p>
            @enderror

            <img id="image-preview" alt="{{ __('Preview gambar baru') }}" class="mt-4 hidden aspect-[16/10] w-full rounded-lg object-cover">
        </section>
    </aside>
</div>

@push('scripts')
    @vite('resources/js/editor.js')
@endpush
