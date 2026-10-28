// Font Awesome Free: hanya gaya solid & regular (gaya brands tidak dipakai, hemat ±115 KB).
// Ikon dipanggil lewat komponen <x-icon>, bukan menulis class "fa-..." langsung di view.
import '@fortawesome/fontawesome-free/css/fontawesome.css';
import '@fortawesome/fontawesome-free/css/solid.css';
import '@fortawesome/fontawesome-free/css/regular.css';

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

// Dropdown akun (partials/user-menu, Popover API). Buka/tutup, klik di luar, dan Esc
// sudah ditangani browser; di sini hanya menaruh posisinya tepat di bawah tombol avatar.
const userMenu = document.getElementById('user-menu');

if (userMenu) {
    const trigger = document.querySelector('[popovertarget="user-menu"]');

    userMenu.addEventListener('beforetoggle', (event) => {
        if (event.newState !== 'open') {
            return;
        }

        const rect = trigger.getBoundingClientRect();
        userMenu.style.top = `${rect.bottom + 8}px`;
        userMenu.style.right = `${Math.max(16, document.documentElement.clientWidth - rect.right)}px`;
    });

    // Posisi dihitung saat dibuka; bila lebar layar berubah, tutup saja agar tidak melayang salah tempat.
    // Hanya lebar yang dicek: di ponsel, address bar yang muncul/hilang saat scroll mengubah tinggi.
    let lastWidth = window.innerWidth;
    window.addEventListener('resize', () => {
        if (window.innerWidth !== lastWidth) {
            lastWidth = window.innerWidth;
            userMenu.hidePopover();
        }
    });
}
