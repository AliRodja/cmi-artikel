// Font Awesome Free: gaya solid, regular, dan brands (logo WhatsApp/X/Facebook untuk tombol bagikan, serta
// media sosial CMI di footer). Footer ada di setiap halaman, jadi font brands (±115 KB) diunduh sekali lalu di-cache.
// Ikon dipanggil lewat komponen <x-icon>, bukan menulis class "fa-..." langsung di view.
import '@fortawesome/fontawesome-free/css/fontawesome.css';
import '@fortawesome/fontawesome-free/css/solid.css';
import '@fortawesome/fontawesome-free/css/regular.css';
import '@fortawesome/fontawesome-free/css/brands.css';

import './live-search';

// Dialog navbar: drawer menu (partials/nav-drawer) & panel pencarian (partials/search-panel).
// Tombol pembuka: data-dialog-open="<id dialog>"; tombol penutup di dalam dialog: data-dialog-close.
// showModal() membuat dialog modal: Esc menutup, fokus terkunci di dalamnya,
// dan halaman di belakangnya tidak bisa diklik.
document.querySelectorAll('dialog.nav-drawer, dialog.search-panel').forEach((dialog) => {
    const openButtons = document.querySelectorAll(`[data-dialog-open="${dialog.id}"]`);

    openButtons.forEach((button) => {
        button.addEventListener('click', () => {
            dialog.showModal();
            button.setAttribute('aria-expanded', 'true');
        });
    });

    dialog.querySelectorAll('[data-dialog-close]').forEach((button) => {
        button.addEventListener('click', () => dialog.close());
    });

    // Klik pada latar gelap: target klik adalah <dialog> itu sendiri, bukan isinya.
    dialog.addEventListener('click', (event) => {
        if (event.target === dialog) {
            dialog.close();
        }
    });

    // Esc langsung menutup. Tanpa ini, Esc pertama di <input type="search"> yang berisi
    // teks hanya mengosongkan teksnya (perilaku bawaan browser), belum menutup panel.
    dialog.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            event.preventDefault();
            dialog.close();
        }
    });

    dialog.addEventListener('close', () => {
        openButtons.forEach((button) => button.setAttribute('aria-expanded', 'false'));
    });
});

// Pop-up notifikasi (partials/flash): ditutup lewat tombol ×, atau otomatis saat bar waktunya habis
// (event animationend dari animasi CSS .toast-timer, yang berhenti selama pop-up disorot kursor).
// Pop-up gagal tidak punya bar waktu, jadi hanya tertutup lewat tombol ×.
document.querySelectorAll('[data-toast]').forEach((toast) => {
    const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    const dismiss = () => {
        if (reduceMotion) {
            toast.remove();
            return;
        }
        toast.classList.add('toast-leaving');
        toast.addEventListener('transitionend', () => toast.remove(), { once: true });
    };

    toast.querySelector('[data-toast-close]').addEventListener('click', dismiss);
    toast.querySelector('[data-toast-timer]')?.addEventListener('animationend', dismiss);
});

// Kolom password (components/input): tombol mata menampilkan/menyembunyikan isi password,
// dan peringatan "Caps Lock aktif" muncul selama Caps Lock menyala saat mengetik.
document.querySelectorAll('[data-password-toggle]').forEach((button) => {
    const input = document.getElementById(button.getAttribute('aria-controls'));
    const showIcon = button.querySelector('[data-password-show]');
    const hideIcon = button.querySelector('[data-password-hide]');

    button.addEventListener('click', () => {
        const willShow = input.type === 'password';
        input.type = willShow ? 'text' : 'password';
        button.setAttribute('aria-pressed', String(willShow));
        // Label dari Blade (data-label-*) agar ikut bahasa yang dipilih.
        button.setAttribute('aria-label', willShow ? button.dataset.labelHide : button.dataset.labelShow);
        showIcon.hidden = willShow;
        hideIcon.hidden = !willShow;
        input.focus();
    });

    const capsLockHint = document.getElementById(`${input.id}-capslock`);
    const updateCapsLock = (event) => {
        capsLockHint.hidden = !event.getModifierState?.('CapsLock');
    };
    input.addEventListener('keydown', updateCapsLock);
    input.addEventListener('keyup', updateCapsLock);
    input.addEventListener('blur', () => (capsLockHint.hidden = true));
});

// Indikator "password cocok" (auth/_password-match, halaman Daftar & Reset Password): dicek langsung saat
// mengetik konfirmasi. Validasi sebenarnya tetap di server (rule "confirmed").
document.querySelectorAll('[data-password-match]').forEach((status) => {
    const password = document.getElementById(status.dataset.password);
    const confirmation = document.getElementById(status.dataset.confirmation);

    const update = () => {
        if (confirmation.value === '') {
            status.hidden = true;
            return;
        }
        const match = confirmation.value === password.value;
        status.hidden = false;
        status.textContent = match ? status.dataset.textMatch : status.dataset.textMismatch;
        status.classList.toggle('text-green-700', match);
        status.classList.toggle('text-amber-700', !match);
    };

    password.addEventListener('input', update);
    confirmation.addEventListener('input', update);
});

// Pintasan "/" membuka panel pencarian, kecuali saat sedang mengetik di kolom isian/editor.
document.addEventListener('keydown', (event) => {
    const typing = event.target.closest('input, textarea, select, [contenteditable], trix-editor');

    if (event.key === '/' && !typing && !event.ctrlKey && !event.metaKey && !document.querySelector('dialog[open]')) {
        event.preventDefault();
        document.querySelector('[data-dialog-open="search-panel"]')?.click();
    }
});

// Navbar diberi bayangan tipis begitu halaman di-scroll (class data-scrolled:* di partials/navbar).
const navbar = document.querySelector('[data-navbar]');
const updateNavbarShadow = () => navbar?.toggleAttribute('data-scrolled', window.scrollY > 0);
updateNavbarShadow();
window.addEventListener('scroll', updateNavbarShadow, { passive: true });

// Dropdown navbar (partials/notification-menu & user-menu, Popover API). Buka/tutup, klik
// di luar, dan Esc sudah ditangani browser; di sini hanya menaruh posisinya tepat di bawah
// tombol pembukanya.
const dropdowns = document.querySelectorAll('[popover].dropdown-panel');

dropdowns.forEach((panel) => {
    const trigger = document.querySelector(`[popovertarget="${panel.id}"]`);

    panel.addEventListener('beforetoggle', (event) => {
        if (event.newState !== 'open') {
            return;
        }

        const rect = trigger.getBoundingClientRect();
        panel.style.top = `${rect.bottom + 8}px`;
        panel.style.right = `${Math.max(16, document.documentElement.clientWidth - rect.right)}px`;
    });

    // Lebar panel baru bisa diukur setelah tampil. Bila sisi kirinya keluar layar
    // (mis. lonceng di ponsel), geser ke kanan sampai muat dengan jarak 16px.
    panel.addEventListener('toggle', (event) => {
        if (event.newState !== 'open') {
            return;
        }

        const { left, width } = panel.getBoundingClientRect();

        if (left < 16) {
            panel.style.right = `${Math.max(16, document.documentElement.clientWidth - 16 - width)}px`;
        }
    });
});

// Posisi dihitung saat dibuka; bila lebar layar berubah, tutup saja agar tidak melayang salah tempat.
// Hanya lebar yang dicek: di ponsel, address bar yang muncul/hilang saat scroll mengubah tinggi.
let lastWidth = window.innerWidth;
window.addEventListener('resize', () => {
    if (window.innerWidth !== lastWidth) {
        lastWidth = window.innerWidth;
        dropdowns.forEach((panel) => panel.hidePopover());
    }
});
