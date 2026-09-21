<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=dm-sans:400,500,600,700|space-grotesk:500,600,700&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-gray-900 antialiased">
        <div class="guest-shell">
            <div class="guest-brand"><a href="/" class="brand-lockup"><span class="brand-mark">G</span><span><strong>GILbank</strong><small>painel operacional</small></span></a></div>

            <div class="guest-card">
                {{ $slot }}
            </div>
            <p class="guest-footnote">Acesso seguro para a equipe de gestão</p>
        </div>
    </body>
</html>
