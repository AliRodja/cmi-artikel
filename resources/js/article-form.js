// Form tulis/edit artikel (articles/_form): penghitung kata, kartu "Kelengkapan artikel", pratinjau gambar,
// dan peringatan sebelum meninggalkan halaman bila ada perubahan yang belum disimpan.
const root = document.querySelector('[data-article-form]');

if (root) {
    const form = root.closest('form');
    const field = (id) => document.getElementById(id);
    const imageInput = field('image');

    const plainText = (html) => {
        const div = document.createElement('div');
        div.innerHTML = html;
        return (div.textContent || '').replace(/\s+/g, ' ').trim();
    };
    const countWords = (text) => (text === '' ? 0 : text.split(' ').length);

    const setItem = (key, done) => {
        const item = root.querySelector(`[data-completeness="${key}"]`);
        item.querySelector('[data-pass]').hidden = !done;
        item.querySelector('[data-fail]').hidden = done;
        return done;
    };

    const update = () => {
        const words = countWords(plainText(field('content').value));
        root.querySelector('[data-word-count]').textContent = `${words.toLocaleString('id-ID')} kata`;

        const hasImage = imageInput.files.length > 0
            || (imageInput.dataset.hasImage === '1' && !field('remove_image')?.checked);

        const results = [
            setItem('title', field('title').value.trim().length >= 10),
            setItem('category', !!form.querySelector('input[name="category"]:checked')),
            setItem('content', words >= 300),
            setItem('image', hasImage),
            setItem('sources', field('sources').value.trim() !== ''),
            setItem('keyword', field('focus_keyword').value.trim() !== ''),
        ];

        const done = results.filter(Boolean).length;
        root.querySelector('[data-completeness-score]').textContent = `${done}/${results.length}`;
        root.querySelector('[data-completeness-bar]').style.width = `${Math.round((done / results.length) * 100)}%`;
    };

    // Pratinjau gambar baru yang dipilih (sebelumnya skrip inline di _form).
    imageInput.addEventListener('change', () => {
        const preview = field('image-preview');
        const file = imageInput.files[0];
        preview.classList.toggle('hidden', !file);
        field('image-name').textContent = file ? file.name : '';
        if (file) {
            preview.src = URL.createObjectURL(file);
        }
    });

    // Peringatan "perubahan belum disimpan": aktif setelah ada ketikan/pilihan, mati saat form dikirim.
    let dirty = false;
    const markDirty = () => {
        dirty = true;
        update();
    };
    form.addEventListener('input', markDirty);
    form.addEventListener('change', markDirty);
    document.addEventListener('trix-change', markDirty);
    form.addEventListener('submit', () => {
        dirty = false;
    });
    window.addEventListener('beforeunload', (event) => {
        if (dirty) {
            event.preventDefault();
        }
    });

    // Trix mengisi editor dari input tersembunyi setelah inisialisasi; hitung ulang begitu siap.
    document.addEventListener('trix-initialize', update);
    update();
}
