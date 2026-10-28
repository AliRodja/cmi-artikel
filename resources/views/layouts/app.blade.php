<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Beranda') · {{ config('app.name') }}</title>
    @fonts
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-screen flex-col bg-gray-50 font-sans text-gray-900 antialiased">

    @include('partials.navbar')

    @yield('hero')

    <main class="container-page flex-1 py-10">
        @include('partials.flash')
        @yield('content')
    </main>

    @include('partials.footer')

    @stack('scripts')
</body>
</html>
