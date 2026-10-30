// Panel "Optimasi SEO" di form tulis/edit artikel (articles/_seo-panel): pratinjau tampilan di Google,
// penghitung karakter, dan checklist kata kunci yang diperbarui langsung saat penulis mengetik.
const panel = document.querySelector('[data-seo-panel]');

if (panel) {
    const field = (id) => document.getElementById(id);
    const titleInput = field('title');
    const contentInput = field('content'); // input tersembunyi yang diisi Trix (HTML)
    const keywordInput = field('focus_keyword');
    const seoTitleInput = field('seo_title');
    const seoDescriptionInput = field('seo_description');

    // Samakan dengan Str::slug() Laravel: huruf kecil, tanpa aksen, selain huruf/angka jadi "-".
    const slugify = (text) => text.normalize('NFD').replace(/[̀-ͯ]/g, '').toLowerCase()
        .replace(/[^a-z0-9]+/g, '-').replace(/^-+|-+$/g, '');

    const plainText = (html) => {
        const div = document.createElement('div');
        div.innerHTML = html;
        return (div.textContent || '').replace(/\s+/g, ' ').trim();
    };

    // Teks dari Blade (data-text-*, ikut bahasa yang dipilih); ":nama" diganti nilainya.
    const text = (key, values = {}) => Object.entries(values).reduce(
        (result, [name, value]) => result.replaceAll(`:${name}`, value),
        panel.dataset[`text${key[0].toUpperCase()}${key.slice(1)}`] ?? '',
    );

    const limit = (text, max) => (text.length > max ? `${text.slice(0, max - 1).trimEnd()}…` : text);
    const contains = (text, keyword) => keyword !== '' && text.toLowerCase().includes(keyword.toLowerCase());

    const setCheck = (key, passed, detail = '') => {
        const item = panel.querySelector(`[data-check="${key}"]`);
        item.querySelector('[data-pass]').hidden = !passed;
        item.querySelector('[data-fail]').hidden = passed;
        item.querySelector('[data-detail]').textContent = detail;
        return passed;
    };

    const setCounter = (name, length) => {
        const counter = panel.querySelector(`[data-seo-counter="${name}"]`);
        const ideal = length >= Number(counter.dataset.min) && length <= Number(counter.dataset.max);
        counter.textContent = text('counter', { length, min: counter.dataset.min, max: counter.dataset.max });
        counter.classList.toggle('text-green-700', ideal);
        counter.classList.toggle('text-amber-700', !ideal);
    };

    const update = () => {
        const keyword = keywordInput.value.trim();
        const content = plainText(contentInput.value);
        // Sama dengan Article::meta_title / meta_description di server: nilai SEO bila diisi, selain itu bawaan.
        const metaTitle = seoTitleInput.value.trim() || titleInput.value.trim();
        const metaDescription = seoDescriptionInput.value.trim() || limit(content, 155);
        const slug = panel.dataset.slug || slugify(titleInput.value) || 'judul-artikel';
        const host = new URL(panel.dataset.urlBase).host;

        panel.querySelector('[data-seo-preview-url]').textContent = `${host} › artikel › ${slug}`;
        panel.querySelector('[data-seo-preview-title]').textContent = limit(metaTitle || text('title'), 60);
        panel.querySelector('[data-seo-preview-description]').textContent = metaDescription || text('description');

        setCounter('seo_title', metaTitle.length);
        setCounter('seo_description', metaDescription.length);

        const results = [
            setCheck('has-keyword', keyword !== ''),
            setCheck('keyword-title', contains(metaTitle, keyword)),
            setCheck('keyword-description', contains(metaDescription, keyword)),
            setCheck('keyword-intro', contains(content.slice(0, 200), keyword)),
            setCheck('keyword-url', keyword !== '' && slug.includes(slugify(keyword))),
            setCheck('title-length', metaTitle.length >= 30 && metaTitle.length <= 60, text('now', { length: metaTitle.length })),
            setCheck('description-length', metaDescription.length >= 120 && metaDescription.length <= 160, text('now', { length: metaDescription.length })),
        ];

        const passed = results.filter(Boolean).length;
        const score = panel.querySelector('[data-seo-score]');
        score.textContent = text('score', { passed, total: results.length });
        score.className = passed >= 6 ? 'text-green-700' : passed >= 4 ? 'text-amber-700' : 'text-red-700';
    };

    [titleInput, keywordInput, seoTitleInput, seoDescriptionInput].forEach((input) => input.addEventListener('input', update));
    document.addEventListener('trix-change', update);
    update();
}
