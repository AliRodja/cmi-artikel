@php
    $article ??= null;
    $currentStatus = (string) old('status', $article?->status ?? \App\Models\Article::STATUS_DRAFT);
    $statusOptions = [
        ['value' => (string) \App\Models\Article::STATUS_DRAFT, 'label' => 'Draft', 'hint' => 'Disimpan, belum tampil di situs publik.'],
        ['value' => (string) \App\Models\Article::STATUS_PUBLISHED, 'label' => 'Published', 'hint' => 'Langsung tampil untuk semua pengunjung.'],
    ];
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
    </div>

    {{-- Sidebar: publikasi & gambar --}}
    <aside class="min-w-0 space-y-6 lg:sticky lg:top-24">
        <section class="card p-5">
            <h2 class="font-bold text-gray-900">Publikasi</h2>

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
                <x-icon name="cloud-arrow-up" size="size-8" class="mx-auto text-gray-400" />
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
