@php
    $projects = [
        ['w trakcie · sklep roślin', 'Nordleaf', 'Jednostronicowa witryna sklepu z roślinami i donicami. Klienci widzą ofertę roślin, donic i zestawów oraz wysyłają zapytanie ze zdjęciem. Właścicielka sama edytuje treści i zdjęcia w prostym panelu.', 'astro · typescript · decap cms', 'https://nordleaf.vadymleontiev.dev/', 'nordleaf.vadymleontiev.dev', 'nordleaf.jpg'],
        ['o mnie · asystent AI', 'vadymleontiev.dev', 'Moje doświadczenie, umiejętności i stos technologiczny. Działa tam ten sam asystent AI, z którym możesz porozmawiać niżej.', 'claude api · rag · node.js', 'https://vadymleontiev.dev', 'vadymleontiev.dev', 'vadymleontiev.dev.jpg'],
        ['13 lat · architekt backendu', 'Englishdom', 'Szkoła angielskiego online działająca od 2010 roku: lekcje 1:1, grupy, kluby konwersacyjne i aplikacje mobilne. Cały backend i architektura.', 'php · architektura · skala', 'https://www.englishdom.com/', 'englishdom.com', 'englishdom.jpg'],
        ['od zera · cały backend', 'All Right Pro', 'Szkoła angielskiego dla Polaków: lekcje indywidualne, kursy online i kluby konwersacyjne. Cała architektura na Symfony.', 'symfony · platforma · płatności', 'https://allrightpro.com/pl/', 'allrightpro.com', 'allrightpro.jpg'],
    ];
@endphp

<section class="block reveal" id="realizacje" aria-labelledby="proj-h" style="padding-top:0">
    <div class="wrap">
        <p class="kicker">Realizacje</p>
        <h2 class="h2" id="proj-h">Systemy, które zaprojektowałem i zbudowałem.</h2>
        <div class="carousel" data-carousel>
            <div class="carousel-nav">
                <button type="button" class="carousel-btn" data-carousel-prev aria-label="Poprzednie" disabled><x-glyph name="chevron-left" width="20" height="20" /></button>
                <button type="button" class="carousel-btn" data-carousel-next aria-label="Następne"><x-glyph name="chevron-right" width="20" height="20" /></button>
            </div>
            <div class="projects" data-carousel-track tabindex="0" role="region" aria-label="Realizacje">
            @foreach ($projects as [$meta, $name, $text, $tags, $url, $host, $image])
                <article class="proj">
                    <img src="{{ asset('images/portfolio/' . $image) }}" alt="{{ $name }}" width="751" height="477" loading="lazy">
                    <div class="proj-body">
                        <p class="meta">{{ $meta }}</p>
                        <h3>{{ $name }}</h3>
                        <p>{{ $text }}</p>
                        <p class="tags">{{ $tags }}</p>
                        <a class="link" href="{{ $url }}" target="_blank" rel="noopener">{{ $host }} <x-glyph name="arrow-up-right" width="16" height="16" /></a>
                    </div>
                </article>
            @endforeach
            </div>
        </div>
    </div>
</section>
