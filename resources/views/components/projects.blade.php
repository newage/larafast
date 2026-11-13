@php
    $projects = [
        ['13 lat · architekt backendu', 'Englishdom', 'Szkoła angielskiego online działająca od 2010 roku: lekcje 1:1, grupy, kluby konwersacyjne i aplikacje mobilne. Cały backend i architektura.', 'php · architektura · skala', 'https://www.englishdom.com/', 'englishdom.com'],
        ['od zera · cały backend', 'All Right Pro', 'Szkoła angielskiego dla Polaków: lekcje indywidualne, kursy online i kluby konwersacyjne. Cała architektura na Symfony.', 'symfony · platforma · płatności', 'https://allrightpro.com/pl/', 'allrightpro.com'],
        ['o mnie · asystent AI', 'vadymleontiev.dev', 'Moje doświadczenie, umiejętności i stos technologiczny. Działa tam ten sam asystent AI, z którym możesz porozmawiać niżej.', 'claude api · rag · mcp', 'https://vadymleontiev.dev', 'vadymleontiev.dev'],
    ];
@endphp

<section class="block reveal" id="realizacje" aria-labelledby="proj-h" style="padding-top:0">
    <div class="wrap">
        <p class="kicker">Realizacje</p>
        <h2 class="h2" id="proj-h">Systemy, które zaprojektowałem i zbudowałem.</h2>
        <div class="projects">
            @foreach ($projects as [$meta, $name, $text, $tags, $url, $host])
                <article class="proj">
                    <p class="meta">{{ $meta }}</p>
                    <h3>{{ $name }}</h3>
                    <p>{{ $text }}</p>
                    <p class="tags">{{ $tags }}</p>
                    <a class="link" href="{{ $url }}" target="_blank" rel="noopener">{{ $host }} <x-glyph name="arrow-up-right" width="16" height="16" /></a>
                </article>
            @endforeach
        </div>
    </div>
</section>
