// Live search di panel cari navbar (partials/search-panel).
// Hasil dirender server oleh Blade (PublicArticleController@liveSearch), jadi teksnya sudah di-escape;
// di sini cukup mengambil HTML-nya, menempelkannya, dan mengatur navigasi keyboard.
const panel = document.getElementById('search-panel');
const input = panel?.querySelector('#navbar-search');

if (input) {
    const form = input.form;
    const idle = panel.querySelector('[data-live-search-idle]');
    const results = panel.querySelector('[data-live-search-results]');

    let debounceTimer;
    let controller;
    let activeIndex = -1;
    let lastTerm = '';

    const options = () => [...results.querySelectorAll('[role="option"]')];

    const clearActive = () => {
        activeIndex = -1;
        input.removeAttribute('aria-activedescendant');
    };

    const setActive = (index) => {
        const list = options();
        if (list.length === 0) {
            return;
        }
        activeIndex = (index + list.length) % list.length;
        list.forEach((option, i) => option.setAttribute('aria-selected', String(i === activeIndex)));
        input.setAttribute('aria-activedescendant', list[activeIndex].id);
        list[activeIndex].scrollIntoView({ block: 'nearest' });
    };

    const showIdle = () => {
        controller?.abort();
        results.hidden = true;
        idle.hidden = false;
        input.setAttribute('aria-expanded', 'false');
        clearActive();
    };

    const search = async (term) => {
        if (term.length < 2) {
            lastTerm = '';
            showIdle();
            return;
        }
        if (term === lastTerm) {
            return;
        }

        // Batalkan permintaan sebelumnya, agar jawaban lama yang datang belakangan tidak menimpa yang baru.
        controller?.abort();
        controller = new AbortController();
        results.setAttribute('aria-busy', 'true');

        try {
            const response = await fetch(`${form.dataset.liveSearchUrl}?${new URLSearchParams({ q: term })}`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest', Accept: 'text/html' },
                signal: controller.signal,
            });
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}`);
            }
            results.innerHTML = await response.text();
            lastTerm = term;
        } catch (error) {
            if (error.name === 'AbortError') {
                return;
            }
            results.innerHTML = '<p class="px-2 py-6 text-center text-sm text-gray-500">Pencarian langsung sedang tidak tersedia. Tekan Enter untuk mencari.</p>';
            lastTerm = '';
        } finally {
            results.removeAttribute('aria-busy');
        }

        idle.hidden = true;
        results.hidden = false;
        input.setAttribute('aria-expanded', String(options().length > 0));
        clearActive();
    };

    // Tunggu 250 ms setelah berhenti mengetik, agar tidak mengirim permintaan di setiap huruf.
    input.addEventListener('input', () => {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => search(input.value.trim()), 250);
    });

    input.addEventListener('keydown', (event) => {
        if (event.key === 'ArrowDown') {
            event.preventDefault();
            setActive(activeIndex + 1);
        } else if (event.key === 'ArrowUp') {
            event.preventDefault();
            setActive(activeIndex - 1);
        } else if (event.key === 'Enter' && activeIndex >= 0) {
            // Ada hasil yang dipilih dengan panah → buka artikelnya. Tanpa pilihan, Enter mengirim form biasa.
            event.preventDefault();
            options()[activeIndex].querySelector('a').click();
        }
    });

    // Panel dibuka dengan kata kunci yang sudah terisi (mis. di beranda setelah mencari) → langsung tampilkan hasilnya.
    input.addEventListener('focus', () => search(input.value.trim()));
}
