<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @include('partials.seo')
    <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">
    <link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}">
    @fonts
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-screen flex-col bg-gray-50 font-sans text-gray-900 antialiased">

    @include('partials.navbar')
    @include('partials.flash')

    @yield('hero')

    <main class="container-page flex-1 py-10">
        @yield('content')
    </main>

    @include('partials.footer')
    
    @unless (request()->is('dashboard*'))
        <x-floating-contact />
    @endunless

    @stack('scripts')
</body>
</html>
