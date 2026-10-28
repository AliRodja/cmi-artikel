@use('App\Support\Seo')

{{--
    Tag SEO di <head> (dipasang di layouts/app). Halaman mengisi lewat section:
    - title (wajib), description (ringkasan 120–160 karakter), og_type (website|article), og_image (URL lengkap)
    - JSON-LD lewat @push('structured-data')
    Robots & canonical ditentukan otomatis oleh App\Support\Seo. Nilai @section('x', $nilai) sudah di-escape Blade.
--}}
<title>@yield('title', 'Beranda') · {{ config('app.name') }}</title>
<meta name="description" content="@yield('description', config('seo.description'))">
<meta name="robots" content="{{ Seo::isIndexable(request()) ? 'index, follow, max-image-preview:large' : 'noindex, follow' }}">
<link rel="canonical" href="{{ Seo::canonicalUrl(request()) }}">
<meta name="theme-color" content="#066ac9">

{{-- Open Graph & Twitter Card: judul, ringkasan, dan gambar saat link dibagikan ke WhatsApp/Facebook/X. --}}
<meta property="og:site_name" content="{{ config('app.name') }}">
<meta property="og:locale" content="id_ID">
<meta property="og:type" content="@yield('og_type', 'website')">
<meta property="og:title" content="@yield('title', 'Beranda')">
<meta property="og:description" content="@yield('description', config('seo.description'))">
<meta property="og:url" content="{{ Seo::canonicalUrl(request()) }}">
<meta property="og:image" content="@yield('og_image', asset(config('seo.image')))">
<meta name="twitter:card" content="summary_large_image">
@stack('seo-meta')

@stack('structured-data')
