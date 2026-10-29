<!DOCTYPE html>
<html lang="id">
<head>
    {{--
        Layout minimal untuk halaman error server (500, 503): tanpa navbar/footer/View Composer, sehingga tetap
        tampil walau database atau session sedang bermasalah.
    --}}
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, follow">
    <title>@yield('title') · {{ config('app.name') }}</title>
    <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">
    @vite(['resources/css/app.css'])
</head>
<body class="flex min-h-screen flex-col bg-gray-50 font-sans text-gray-900 antialiased">
    <header class="border-t-[3px] border-b border-t-brand-600 border-b-brand-100 bg-brand-50">
        <div class="container-page flex h-16 items-center justify-center">
            <a href="{{ url('/') }}"><x-logo /></a>
        </div>
    </header>
    <main class="container-page flex-1 py-10">
        @yield('content')
    </main>
</body>
</html>
