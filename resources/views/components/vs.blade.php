<section class="block reveal" aria-labelledby="vs-h">
    <div class="wrap">
        <p class="kicker">Dlaczego ja</p>
        <h2 class="h2" id="vs-h">Jedna osoba zamiast software house'u.</h2>
        <p class="lead">Nie mam działu sprzedaży, project managera ani biura do opłacenia. Płacisz za pracę, która trafia do Twojej firmy.</p>
        <div class="vs">
            <div class="vs-card">
                <h3><x-glyph name="buildings" />Typowy software house</h3>
                <p class="tag">sprzedawca → PM → analityk → zespół</p>
                <ul>
                    @foreach (['Wymagania przechodzą przez 3 osoby, zanim trafią do programisty', 'W cenie są biuro, sprzedaż i zarządzanie', 'Pierwsza wersja po kilku tygodniach', 'Każda zmiana to nowe spotkanie i nowa wycena'] as $item)
                        <li><x-glyph name="x" class="x" />{{ $item }}</li>
                    @endforeach
                </ul>
            </div>
            <div class="vs-card me">
                <h3><x-glyph name="user" />Pracujesz ze mną</h3>
                <p class="tag">Ty → ja</p>
                <ul>
                    @foreach (['Mówisz, czego potrzebujesz, osobie, która to buduje', 'Bez narzutów na biuro i zespół, więc niższa cena', 'MVP po 2–3 dniach, gotowe wdrożenie po 6', '20 lat doświadczenia w systemach dla tysięcy użytkowników'] as $item)
                        <li><x-glyph name="check" class="v" />{{ $item }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
</section>
