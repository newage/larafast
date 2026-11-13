@php
    $rules = [
        ['15 minut rozmowy za darmo', 'Opowiadasz o problemie, ja mówię, czy i jak mogę pomóc. Bez zobowiązań.'],
        ['Analiza zostaje u Ciebie', 'Nawet jeśli nie będziemy dalej współpracować, opis procesów i rekomendacje są Twoje.'],
        ['Płacisz po zakończeniu pracy', 'Realizacja trwa dni, nie miesiące, więc nie dzielę jej na raty. Najpierw działa, potem faktura.'],
        ['Faktura VAT, umowa B2B', 'Normalna współpraca firma z firmą, z fakturą i jasnym zakresem prac.'],
    ];
@endphp

<section class="block reveal" aria-labelledby="rules-h" style="padding-top:0">
    <div class="wrap">
        <p class="kicker">Zasady</p>
        <h2 class="h2" id="rules-h">Proste zasady współpracy.</h2>
        <div class="rules">
            @foreach ($rules as $i => [$title, $text])
                <div class="rule"><span class="n">{{ sprintf('%02d', $i + 1) }}</span><div><h3>{{ $title }}</h3><p>{{ $text }}</p></div></div>
            @endforeach
        </div>
    </div>
</section>
