@php
    $days = range(1, 6);
    $steps = [
        ['Rozmowa i projekt', '15 min · potrzeby · projekt', '1/2', 'k', '0s', 'akceptujesz projekt', false],
        ['Wersja MVP', 'do kliknięcia w przeglądarce', '2/4', '', '.15s', 'testujesz na żywo', false],
        ['Funkcje i integracje', 'czat AI · CRM · automaty', '4/6', '', '.3s', 'chatbot, CRM, poczta', false],
        ['Start', 'testy · hosting · przekazanie', '6/7', 'k', '.45s', 'działa u Ciebie', true],
    ];
    $chips = ['płatność po zakończeniu', 'kod i dostępy należą do Ciebie', 'krótka instrukcja dla zespołu'];
@endphp

<section class="wrap reveal" id="jak" aria-labelledby="tl-h">
    <div class="shell">
        <div class="shell-in">
            <div class="shell-head">
                <h2 id="tl-h" class="mono" style="font-size:14px;line-height:20px;font-weight:500;letter-spacing:0">Jak pracuję · dzień 1 → 6</h2>
                <div class="legend"><span><b></b>praca</span><span><b class="k"></b>Twoja akceptacja</span></div>
            </div>
            <div class="tl">
                <div class="days"><span></span>@foreach ($days as $day)<span class="day">dzień {{ $day }}</span>@endforeach</div>
                @foreach ($steps as [$title, $meta, $column, $kind, $delay, $note, $atEnd])
                    <div class="lbl"><h3>{{ $title }}</h3><p>{{ $meta }}</p></div>
                    <div class="track"><div class="bar {{ $kind }}" style="grid-column:{{ $column }};transition-delay:{{ $delay }}"><span class="bar-note {{ $atEnd ? 'end' : '' }}">{{ $note }}</span></div></div>
                @endforeach
            </div>
            <div class="tl-foot">
                @foreach ($chips as $chip)
                    <span class="chip">{{ $chip }}</span>
                @endforeach
            </div>
        </div>
    </div>
</section>
