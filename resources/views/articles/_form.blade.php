@use('App\Models\Article')

@php
    $article ??= null;
    $isAdmin = auth()->user()->isAdmin();

    $labels = [
        Article::STATUS_DRAFT => ['label' => 'Draft', 'hint' => $isAdmin ? 'Disimpan, belum tampil di situs publik.' : 'Simpan dulu, belum dikirim ke admin.'],
        Article::STATUS_REVIEW => $isAdmin
            ? ['label' => 'Menunggu Review', 'hint' => 'Biarkan tetap di antrean review.']
            : ['label' => 'Ajukan ke Admin', 'hint' => 'Admin meninjau dulu sebelum artikel tayang.'],
        Article::STATUS_PUBLISHED => ['label' => 'Published', 'hint' => 'Langsung tampil untuk semua pengunjung.'],
    ];

    // Pilihan status mengikuti aturan yang sama dengan validasi di ArticleRequest.
    $statusOptions = collect(Article::allowedStatusesFor(auth()->user(), $article))
        ->map(fn (int $value) => ['value' => (string) $value] + $labels[$value]);

    $currentStatus = (string) old('status', $article?->status ?? Article::STATUS_DRAFT);
    if (! $statusOptions->contains('value', $currentStatus)) {
        $currentStatus = (string) Article::STATUS_DRAFT;
    }
@endphp

<div class="grid items-start gap-6 lg:grid-cols-3">
    {{-- Kolom utama: judul & isi --}}
    <div class="card min-w-0 p-6 lg:col-span-2">
        <div class="mb-5">
            <label for="title" class="form-label">Judul</label>
            <input type="text" id="title" name="title" value="{{ old('title', $article?->title) }}" required maxlength="255"
                   placeholder="Tulis judul artikel..."
                   @class(['form-input py-3 text-lg font-semibold', 'form-input-error' => $errors->has('title')])>
            @error('title')
                <p class="form-error">{{ $message }}</p>
            @enderror
        </div>

        <fieldset class="mb-5">
            <legend class="form-label">Jenis Artikel</legend>
            <div class="grid grid-cols-2 gap-2 sm:grid-cols-4">
                @foreach (Article::CATEGORIES as $value => $label)
                    <label class="flex cursor-pointer items-center gap-2 rounded-lg border border-gray-200 px-3 py-2.5 text-sm font-medium text-gray-700 transition has-checked:border-brand-500 has-checked:bg-brand-50/60 has-checked:text-brand-800">
                        <input type="radio" name="category" value="{{ $value }}" required
                               @checked(old('category', $article?->category) === $value) class="accent-brand-600">
                        {{ $label }}
                    </label>
                @endforeach
            </div>
            @error('category')
                <p class="form-error">{{ $message }}</p>
            @enderror
        </fieldset>

        <div>
            <label for="content-editor" class="form-label">Isi Artikel</label>
            {{-- Trix menulis HTML ke input tersembunyi ini; nilainya yang dikirim ke server. --}}
            <input type="hidden" id="content" name="content" value="{{ old('content', $article?->content_html) }}">
            <trix-editor id="content-editor" input="content" placeholder="Mulai menulis isi artikel..."
                         @class(['article-editor article-content', 'article-editor-error' => $errors->has('content')])></trix-editor>
            @error('content')
                <p class="form-error">{{ $message }}</p>
            @enderror
        </div>

        <div class="mt-5">
            <label for="sources" class="form-label">Sumber rujukan <span class="font-normal text-gray-500">(opsional)</span></label>
            <textarea id="sources" name="sources" rows="4" maxlength="5000"
                      placeholder="Satu sumber per baris, mis.&#10;Kementerian Kesehatan RI, Pedoman Hipertensi 2024 https://kemkes.go.id/..."
                      @class(['form-input', 'form-input-error' => $errors->has('sources')])>{{ old('sources', $article?->sources) }}</textarea>
            <p class="mt-1 text-xs text-gray-500">Satu sumber per baris. Tautan yang diawali http:// atau https:// otomatis bisa diklik.</p>
            @error('sources')
                <p class="form-error">{{ $message }}</p>
            @enderror
        </div>
    </div>

    {{-- Sidebar: publikasi & gambar --}}
    <aside class="min-w-0 space-y-6 lg:sticky lg:top-24">
        <section class="card p-5">
            <h2 class="font-bold text-gray-900">Publikasi</h2>
            @unless ($isAdmin)
                <p class="mt-1 text-xs text-gray-500">Artikel tayang setelah disetujui admin.</p>
            @endunless

            <fieldset class="mt-4 space-y-2">
                <legend class="sr-only">Status</legend>
                @foreach ($statusOptions as $option)
                    <label class="flex cursor-pointer gap-3 rounded-lg border border-gray-200 p-3 transition has-checked:border-brand-500 has-checked:bg-brand-50/60">
                        <input type="radio" name="status" value="{{ $option['value'] }}" @checked($currentStatus === $option['value'])
                               class="mt-0.5 accent-brand-600">
                        <span>
                            <span class="block text-sm font-semibold text-gray-900">{{ $option['label'] }}</span>
                            <span class="block text-xs text-gray-500">{{ $option['hint'] }}</span>
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
                <button type="submit" class="btn btn-primary w-full">{{ $submitLabel }}</button>
                <a href="{{ $cancelUrl }}" class="btn btn-secondary w-full">Batal</a>
            </div>
        </section>

        <section class="card p-5">
            <h2 class="font-bold text-gray-900">Gambar Utama</h2>
            <p class="mt-1 text-xs text-gray-500">Opsional · JPG, PNG, atau WebP · maks 2 MB</p>

            @if ($article?->image_url)
                <div id="current-image" class="mt-4">
                    <img src="{{ $article->image_url }}" alt="Gambar saat ini" class="aspect-[16/10] w-full rounded-lg object-cover">
                    <label class="mt-2 flex items-center gap-2 text-sm text-gray-600">
                        <input type="checkbox" name="remove_image" value="1" class="rounded border-gray-300 accent-red-600" @checked(old('remove_image'))>
                        Hapus gambar ini
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
                       class="absolute inset-0 size-full cursor-pointer opacity-0">
                <x-icon name="cloud-arrow-up" size="size-8" class="mx-auto text-gray-500" />
                <p class="mt-2 text-sm font-medium text-gray-700">
                    {{ $article?->image_url ? 'Ganti gambar' : 'Pilih atau seret gambar ke sini' }}
                </p>
                <p id="image-name" class="mt-1 truncate text-xs text-gray-500"></p>
            </div>
            @error('image')
                <p class="form-error">{{ $message }}</p>
            @enderror

            <img id="image-preview" alt="Preview gambar baru" class="mt-4 hidden aspect-[16/10] w-full rounded-lg object-cover">
        </section>
    </aside>
</div>

@push('scripts')
    @vite('resources/js/editor.js')
    <script>
        document.getElementById('image').addEventListener('change', function (event) {
            const preview = document.getElementById('image-preview');
            const fileName = document.getElementById('image-name');
            const file = event.target.files[0];

            if (file) {
                preview.src = URL.createObjectURL(file);
                preview.classList.remove('hidden');
                fileName.textContent = file.name;
            } else {
                preview.classList.add('hidden');
                fileName.textContent = '';
            }
        });
    </script>
@endpush
