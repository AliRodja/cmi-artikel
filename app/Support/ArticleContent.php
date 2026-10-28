<?php

namespace App\Support;

use Illuminate\Support\Str;
use Symfony\Component\HtmlSanitizer\HtmlSanitizer;
use Symfony\Component\HtmlSanitizer\HtmlSanitizerConfig;

/**
 * Satu-satunya tempat isi artikel diubah menjadi HTML yang aman.
 *
 * Isi artikel ditulis lewat rich text editor (Trix), sehingga berupa HTML.
 * HTML dari user tidak boleh dipercaya: semua tag/atribut di luar daftar putih
 * (mis. <script>, onclick, href="javascript:...") dibuang di sini.
 */
class ArticleContent
{
    private static ?HtmlSanitizer $sanitizer = null;

    /**
     * Ubah isi artikel menjadi HTML aman. Isi lama berupa teks polos
     * dibungkus menjadi paragraf <p> terlebih dahulu.
     */
    public static function toHtml(?string $content): string
    {
        $content = (string) $content;

        // Dianggap HTML hanya jika memuat tag yang memang dihasilkan editor;
        // selain itu teks polos (mis. "nilai <kedua>") di-escape apa adanya.
        if (! preg_match('/<\/?(div|p|br|strong|em|del|h1|blockquote|pre|ul|ol|li|a)\b/i', $content)) {
            $content = static::paragraphsFromPlainText($content);
        }

        return static::sanitizer()->sanitize($content);
    }

    /**
     * Teks polos tanpa tag, untuk ringkasan (excerpt) dan estimasi waktu baca.
     */
    public static function toPlainText(?string $content): string
    {
        // Beri spasi setelah penutup blok agar "<div>a</div><div>b</div>" tidak menjadi "ab".
        $content = preg_replace('/<\/(p|div|h[1-6]|li|blockquote|pre)>|<br\s*\/?>/i', '$0 ', (string) $content);

        return Str::squish(html_entity_decode(strip_tags($content), ENT_QUOTES | ENT_HTML5));
    }

    private static function paragraphsFromPlainText(string $text): string
    {
        return collect(preg_split('/\R{2,}/', trim($text)))
            ->filter(fn ($paragraph) => trim($paragraph) !== '')
            ->map(fn ($paragraph) => '<p>'.nl2br(e(trim($paragraph)), false).'</p>')
            ->implode('');
    }

    private static function sanitizer(): HtmlSanitizer
    {
        return static::$sanitizer ??= new HtmlSanitizer(
            (new HtmlSanitizerConfig)
                // Tag yang dihasilkan Trix (+ <p> untuk isi lama berupa teks polos).
                ->allowElement('div')
                ->allowElement('p')
                ->allowElement('br')
                ->allowElement('strong')
                ->allowElement('em')
                ->allowElement('del')
                ->allowElement('h1')
                ->allowElement('blockquote')
                ->allowElement('pre')
                ->allowElement('ul')
                ->allowElement('ol')
                ->allowElement('li')
                ->allowElement('a', ['href'])
                ->allowLinkSchemes(['http', 'https', 'mailto'])
                ->forceAttribute('a', 'target', '_blank')
                ->forceAttribute('a', 'rel', 'noopener noreferrer')
                // Bawaan Symfony memotong input di atas 20.000 karakter; artikel bisa lebih panjang.
                ->withMaxInputLength(1_000_000)
        );
    }
}
