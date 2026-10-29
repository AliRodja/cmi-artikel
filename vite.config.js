import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import { google } from 'laravel-vite-plugin/fonts';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js', 'resources/js/editor.js'],
            refresh: true,
            // Diunduh dari Google Fonts API saat build lalu disajikan dari server sendiri
            // (tanpa request ke Google di browser pengunjung). Preload hanya varian yang
            // dipakai di atas lipatan; varian lain dimuat saat dibutuhkan.
            fonts: [
                google('Inter', {
                    weights: [400, 500, 600, 700, 800],
                    styles: ['normal', 'italic'],
                    preload: [{ weight: 400, style: 'normal' }],
                }),
                // Huruf artikel (isi & judul artikel di daftar): Roboto, sans-serif.
                google('Roboto', {
                    weights: [400, 500, 600],
                    styles: ['normal', 'italic'],
                    preload: [{ weight: 400, style: 'normal' }],
                }),
            ],
        }),
        tailwindcss(),
    ],
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
