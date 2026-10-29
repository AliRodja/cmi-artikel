<?php

namespace App\Support;

use App\Models\Article;
use Illuminate\Http\Request;

/**
 * Aturan SEO di satu tempat: halaman mana yang boleh diindeks mesin pencari, URL canonical,
 * dan data terstruktur JSON-LD (schema.org) yang membantu Google memahami isi halaman.
 */
class Seo
{
    /**
     * Opsi json_encode untuk JSON-LD. JSON_HEX_TAG mengubah < dan > menjadi </>, sehingga judul
     * berisi "</script>" tidak bisa "keluar" dari tag <script type="application/ld+json">.
     */
    public const JSON_FLAGS = JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP;

    /**
     * Hanya halaman publik yang bernilai untuk pencarian: beranda dan detail artikel.
     * Hasil pencarian, halaman login/daftar, dashboard, akun, dll. diberi noindex.
     */
    public static function isIndexable(Request $request): bool
    {
        return $request->routeIs('public.articles.show')
            || ($request->routeIs('home') && ! $request->filled('search'));
    }

    /**
     * URL "resmi" halaman ini, tanpa parameter yang tidak mengubah isi (mis. ?utm_source=, ?search=), agar Google
     * tidak menganggap satu halaman sebagai banyak halaman kembar. Di beranda hanya ?page= (>1) yang dipakai.
     */
    public static function canonicalUrl(Request $request): string
    {
        if (! $request->routeIs('home')) {
            return $request->url();
        }

        $page = (int) $request->query('page');

        return route('home', array_filter(['page' => $page > 1 ? $page : null]));
    }

    /** Identitas klinik (schema.org MedicalClinic) + situs dengan kotak pencarian, untuk beranda. */
    public static function organization(): array
    {
        $clinic = config('clinic');

        return [
            '@context' => 'https://schema.org',
            '@graph' => [
                array_filter([
                    '@type' => 'MedicalClinic',
                    '@id' => route('home').'#organization',
                    'name' => config('app.name'),
                    'url' => route('home'),
                    'logo' => asset('images/logo-cmi.png'),
                    'image' => asset(config('seo.image')),
                    'email' => $clinic['email'],
                    'address' => $clinic['address'] ? ['@type' => 'PostalAddress', 'streetAddress' => $clinic['address']] : null,
                    'contactPoint' => [
                        '@type' => 'ContactPoint',
                        'telephone' => '+'.$clinic['whatsapp_number'],
                        'contactType' => 'customer service',
                        'availableLanguage' => 'id',
                    ],
                ]),
                [
                    '@type' => 'WebSite',
                    '@id' => route('home').'#website',
                    'name' => config('app.name'),
                    'url' => route('home'),
                    'inLanguage' => 'id-ID',
                    'publisher' => ['@id' => route('home').'#organization'],
                    // Memungkinkan kotak pencarian situs langsung di hasil Google.
                    'potentialAction' => [
                        '@type' => 'SearchAction',
                        'target' => route('home').'?search={search_term_string}',
                        'query-input' => 'required name=search_term_string',
                    ],
                ],
            ],
        ];
    }

    /**
     * Artikel kesehatan: halaman medis (dengan peninjau & tanggal tinjauan bila ada), artikelnya
     * (penulis, profesi, tanggal terbit/diperbarui, gambar), dan breadcrumb.
     */
    public static function article(Article $article): array
    {
        $url = route('public.articles.show', $article);
        $person = fn ($user) => $user ? array_filter([
            '@type' => 'Person',
            'name' => $user->name,
            'jobTitle' => $user->profession,
        ]) : null;

        $breadcrumbs = [['Beranda', route('home')], [$article->title, $url]];

        return [
            '@context' => 'https://schema.org',
            '@graph' => [
                array_filter([
                    '@type' => 'MedicalWebPage',
                    '@id' => $url.'#webpage',
                    'url' => $url,
                    'name' => $article->title,
                    'inLanguage' => 'id-ID',
                    // Seluruh situs membahas kesehatan jantung (spesialisasi kardiovaskular).
                    'specialty' => 'https://schema.org/Cardiovascular',
                    'reviewedBy' => $person($article->medicalReviewer),
                    'lastReviewed' => $article->medicalReviewer ? $article->updated_at->toDateString() : null,
                    'mainEntity' => ['@id' => $url.'#article'],
                ]),
                array_filter([
                    '@type' => 'Article',
                    '@id' => $url.'#article',
                    'headline' => $article->title,
                    'description' => $article->meta_description,
                    'keywords' => $article->focus_keyword,
                    'image' => $article->image_url ?? asset(config('seo.image')),
                    'datePublished' => $article->created_at->toAtomString(),
                    'dateModified' => $article->updated_at->toAtomString(),
                    'articleSection' => 'Kesehatan Jantung',
                    'author' => $person($article->user) ?? ['@type' => 'Organization', 'name' => config('app.name')],
                    'publisher' => [
                        '@type' => 'Organization',
                        'name' => config('app.name'),
                        'logo' => ['@type' => 'ImageObject', 'url' => asset('images/logo-cmi.png')],
                    ],
                    'mainEntityOfPage' => $url,
                ]),
                [
                    '@type' => 'BreadcrumbList',
                    'itemListElement' => collect($breadcrumbs)->values()->map(fn (array $item, int $index) => [
                        '@type' => 'ListItem',
                        'position' => $index + 1,
                        'name' => $item[0],
                        'item' => $item[1],
                    ])->all(),
                ],
            ],
        ];
    }
}
