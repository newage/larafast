<!DOCTYPE html>
<html lang="pl">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>@yield('title', 'Strony www i automatyzacja AI')</title>
        <meta name="description" content="@yield('description', 'Strony firmowe w Laravel i automatyzacje z AI dla małych firm. Stały zakres, jasna cena, kod, który należy do Ciebie.')">

        <link rel="icon" href="/favicon.ico" sizes="48x48">
        <link rel="icon" href="/favicon.svg" type="image/svg+xml">
        <link rel="apple-touch-icon" href="/apple-touch-icon.png">

        @fonts

        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @endif
    </head>
    <body class="bg-canvas">
        @yield('content')
    </body>
</html>
