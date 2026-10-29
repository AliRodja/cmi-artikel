<?php

namespace App\Http\Controllers;

use App\Models\Article;
use Illuminate\Http\Response;
use XMLWriter;

/** sitemap.xml & robots.txt untuk mesin pencari. URL lengkap dibentuk dari APP_URL. */
class SeoController extends Controller
{
    /**
     * Daftar halaman publik yang perlu diindeks: beranda dan semua artikel Published.
     * Dibuat otomatis dari database, jadi artikel baru langsung masuk tanpa diatur manual.
     * XMLWriter meng-escape karakter khusus (&, <, >) sehingga XML selalu valid.
     */
    public function sitemap(): Response
    {
        $xml = new XMLWriter;
        $xml->openMemory();
        $xml->startDocument('1.0', 'UTF-8');
        $xml->startElement('urlset');
        $xml->writeAttribute('xmlns', 'http://www.sitemaps.org/schemas/sitemap/0.9');
        $xml->writeAttribute('xmlns:image', 'http://www.google.com/schemas/sitemap-image/1.1');

        $addUrl = function (string $loc, $lastModified, ?string $image = null) use ($xml) {
            $xml->startElement('url');
            $xml->writeElement('loc', $loc);
            if ($lastModified) {
                $xml->writeElement('lastmod', $lastModified->toAtomString());
            }
            if ($image) {
                $xml->startElement('image:image');
                $xml->writeElement('image:loc', $image);
                $xml->endElement();
            }
            $xml->endElement();
        };

        $addUrl(route('home'), Article::published()->latest('updated_at')->value('updated_at'));

        // lazy(): dibaca bertahap per 1000 baris, aman walau artikelnya ribuan.
        Article::published()->select(['id', 'slug', 'image', 'updated_at'])->orderBy('id')->lazy()
            ->each(fn (Article $article) => $addUrl(route('public.articles.show', $article), $article->updated_at, $article->image_url));

        $xml->endElement();
        $xml->endDocument();

        return response($xml->outputMemory(), 200, ['Content-Type' => 'application/xml; charset=UTF-8']);
    }

    /**
     * Selain di production (mis. server uji/staging) seluruh situs ditutup dari mesin pencari, agar salinan
     * uji tidak ikut terindeks. Di production, area pribadi dilarang dirambah dan lokasi sitemap diberitahukan.
     */
    public function robots(): Response
    {
        $lines = app()->isProduction()
            ? [
                'User-agent: *',
                'Disallow: /dashboard',
                'Disallow: /akun',
                'Disallow: /baca-nanti',
                'Disallow: /notifikasi',
                'Disallow: /cari',
                '',
                'Sitemap: '.route('sitemap'),
            ]
            : ['User-agent: *', 'Disallow: /'];

        return response(implode("\n", $lines)."\n", 200, ['Content-Type' => 'text/plain; charset=UTF-8']);
    }
}
