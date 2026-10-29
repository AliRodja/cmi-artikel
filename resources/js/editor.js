// Rich text editor untuk isi artikel. Dimuat hanya di halaman tulis/edit artikel.
import 'trix';
import 'trix/dist/trix.css';

import './seo-panel';
import './article-form';

// Gambar artikel dikelola lewat field "Gambar" terpisah, jadi lampiran file di editor dimatikan.
document.addEventListener('trix-file-accept', (event) => event.preventDefault());

document.addEventListener('trix-initialize', (event) => {
    event.target.toolbarElement.querySelector('.trix-button-group--file-tools')?.remove();
});
