<!DOCTYPE html>
<html lang="pl">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Strony www i automatyzacja AI</title>
        <meta name="description" content="Strony firmowe w Laravel i automatyzacje z AI dla małych firm. Stały zakres, jasna cena, kod, który należy do Ciebie.">

        @fonts

        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @endif
    </head>
    <body class="bg-canvas">
        @yield('content')
    </body>
</html>
