@php
    $recommendations = [
        ['Szybko ogarnia nawet duże, złożone systemy i błyskawicznie znajduje źródło problemu, nawet głęboko ukryte. Jego rozwiązania są przemyślane, stabilne i łatwo je rozwijać.', 'Roman Yas', 'Front End Engineer', 'jeden zespół, 12+ lat · paź 2026'],
        ['Podchodzi do pracy poważnie, dba o jakość i zawsze można na nim polegać. Współpraca z nim jest prosta i przyjemna.', 'Tetiana Shevchenko', 'QA Lead', 'jeden zespół, 8 lat · paź 2026'],
        ['Szybko realizuje zadania, rzeczowo prowadzi dyskusje i trafnie podpowiada rozwiązania. Chętnie bierze też rutynową pracę, której nikt inny nie chce robić.', 'Yevhen Loshkarov', 'IT Team Lead', 'bezpośredni przełożony, 9 lat · wrz 2026'],
    ];
@endphp

<section class="block reveal" id="rekomendacje" aria-labelledby="rec-h" style="padding-top:0">
    <div class="wrap">
        <p class="kicker">Rekomendacje</p>
        <h2 class="h2" id="rec-h">Co mówią ludzie, z którymi pracowałem.</h2>
        <div class="recs">
            @foreach ($recommendations as [$quote, $name, $role, $meta])
                <figure class="rec">
                    <x-glyph name="quotes" class="q" width="32" height="32" />
                    <blockquote>{{ $quote }}</blockquote>
                    <figcaption><b>{{ $name }}</b><span>{{ $role }}</span><span class="mono">{{ $meta }}</span></figcaption>
                </figure>
            @endforeach
        </div>
        <p class="rec-foot">Cytaty przetłumaczone z angielskiego. <a href="https://www.linkedin.com/in/vadim-leontiev-a7035938/details/recommendations/" target="_blank" rel="noopener">Wszystkie rekomendacje na LinkedIn <x-glyph name="arrow-up-right" width="16" height="16" /></a></p>
    </div>
</section>
