@php($article ??= null)

<x-input name="title" label="Judul" :value="$article?->title" required maxlength="255" />

<div class="mb-4">
    <label for="content" class="form-label">Isi Artikel</label>
    <textarea id="content" name="content" rows="12" required
              @class(['form-input', 'form-input-error' => $errors->has('content')])>{{ old('content', $article?->content) }}</textarea>
    @error('content')
        <p class="form-error">{{ $message }}</p>
    @enderror
</div>

<div class="mb-4">
    <label for="status" class="form-label">Status</label>
    @php($currentStatus = (string) old('status', $article?->status ?? \App\Models\Article::STATUS_DRAFT))
    <select id="status" name="status" @class(['form-input', 'form-input-error' => $errors->has('status')])>
        <option value="{{ \App\Models\Article::STATUS_DRAFT }}" @selected($currentStatus === '0')>Draft</option>
        <option value="{{ \App\Models\Article::STATUS_PUBLISHED }}" @selected($currentStatus === '1')>Published</option>
    </select>
    @error('status')
        <p class="form-error">{{ $message }}</p>
    @enderror
</div>

<div class="mb-6">
    <label for="image" class="form-label">Gambar <span class="font-normal text-gray-400">(opsional, jpg/png/webp, maks 2 MB)</span></label>

    @if ($article?->image_url)
        <div class="mb-3 flex items-center gap-4">
            <img src="{{ $article->image_url }}" alt="Gambar saat ini" class="h-24 w-36 rounded-lg object-cover">
            <label class="flex items-center gap-2 text-sm text-gray-600">
                <input type="checkbox" name="remove_image" value="1" class="rounded border-gray-300" @checked(old('remove_image'))>
                Hapus gambar ini
            </label>
        </div>
    @endif

    <input type="file" id="image" name="image" accept="image/jpeg,image/png,image/webp"
           @class(['form-input', 'form-input-error' => $errors->has('image')])>
    @error('image')
        <p class="form-error">{{ $message }}</p>
    @enderror

    <img id="image-preview" alt="Preview gambar" class="mt-3 hidden h-40 rounded-lg object-cover">
</div>

@push('scripts')
    <script>
        document.getElementById('image').addEventListener('change', function (event) {
            const preview = document.getElementById('image-preview');
            const file = event.target.files[0];

            if (file) {
                preview.src = URL.createObjectURL(file);
                preview.classList.remove('hidden');
            } else {
                preview.classList.add('hidden');
            }
        });
    </script>
@endpush
