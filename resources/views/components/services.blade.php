@php
    $attrs = 'width="240" height="100" viewBox="0 0 240 100" fill="none" stroke="#9B9B9B" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"';
    $services = [
        [
            'title' => 'Strony firmowe',
            'text' => 'Szybkie, czytelne strony w Laravel, które dobrze się wczytują, pozycjonują i są łatwe w edycji. Kod i konto hostingowe należą do Ciebie.',
            'tags' => 'Laravel, Blade, SEO',
            'svg' => '<rect x="20" y="8" width="200" height="84" rx="6"/><path d="M20 26h200"/><circle cx="32" cy="17" r="2" fill="#9B9B9B" stroke="none"/><circle cx="40" cy="17" r="2" fill="#9B9B9B" stroke="none"/><circle cx="48" cy="17" r="2" fill="#9B9B9B" stroke="none"/><rect x="34" y="38" width="90" height="10" rx="2" stroke="#FFFFFF"/><path d="M34 60h110M34 70h84"/><rect x="152" y="38" width="54" height="44" rx="4"/>',
        ],
        [
            'title' => 'Asystenci AI',
            'text' => 'Asystent odpowiada klientom na typowe pytania na podstawie Twoich dokumentów, a trudniejsze sprawy przekazuje Tobie.',
            'tags' => 'Claude API, RAG',
            'svg' => '<rect x="20" y="10" width="120" height="32" rx="8"/><path d="M34 22h60M34 31h40"/><rect x="100" y="56" width="120" height="32" rx="8" stroke="#FFFFFF"/><path d="M114 68h70M114 77h44" stroke="#FFFFFF"/><path d="M196 18l2.4 6.6L205 27l-6.6 2.4L196 36l-2.4-6.6L187 27l6.6-2.4z" stroke="#FFFFFF"/>',
        ],
        [
            'title' => 'Automatyzacja procesów',
            'text' => 'Formularze, arkusze, poczta i faktury połączone tak, aby dane przepływały same i nic nie trzeba przepisywać.',
            'tags' => 'Python, API, webhooki',
            'svg' => '<rect x="12" y="34" width="44" height="32" rx="6"/><rect x="98" y="34" width="44" height="32" rx="6" stroke="#FFFFFF"/><rect x="184" y="34" width="44" height="32" rx="6"/><path class="flow" d="M56 50h42M142 50h42" stroke-dasharray="4 4"/><path d="M24 46h20M24 54h12M110 46h20M110 54h12M196 46h20M196 54h12"/>',
        ],
        [
            'title' => 'Integracje i narzędzia wewnętrzne',
            'text' => 'Małe narzędzia na miarę, które łączą systemy, za które już płacisz: rezerwacje, CRM i księgowość.',
            'tags' => 'Laravel, FastAPI, MCP',
            'svg' => '<circle cx="120" cy="50" r="14" stroke="#FFFFFF"/><circle cx="36" cy="20" r="8"/><circle cx="204" cy="20" r="8"/><circle cx="36" cy="80" r="8"/><circle cx="204" cy="80" r="8"/><path d="M43 25L109 44M197 25L131 44M43 75L109 56M197 75L131 56"/>',
        ],
    ];
@endphp

<section id="services" class="mx-auto max-w-[1120px] scroll-mt-20 px-6 py-16 sm:py-24">
    <div class="reveal">
        <div class="font-mono text-sm text-muted">Usługi</div>
        <h2 class="mt-3 max-w-[680px] text-3xl font-semibold tracking-[-0.02em] sm:text-4xl">Dwie rzeczy, zrobione porządnie.</h2>
        <p class="mt-4 max-w-[680px] text-lg text-muted">Strona, która wyjaśnia, czym się zajmujesz, i przyciąga zapytania, oraz automatyzacje, które zajmują się tym, co dzieje się potem.</p>
    </div>

    <div class="reveal mt-12 flex flex-wrap gap-4">
        @foreach ($services as $service)
            <div data-card="service" class="card flex flex-[1_1_400px] flex-col gap-3 overflow-hidden rounded-2xl border border-line bg-surface p-6 hover:bg-surface-raised">
                <div aria-hidden="true" class="-mx-6 -mt-6 flex h-[140px] items-center justify-center bg-surface-raised">
                    <svg {!! $attrs !!}>{!! $service['svg'] !!}</svg>
                </div>
                <h3 class="text-xl font-semibold">{{ $service['title'] }}</h3>
                <p class="text-muted">{{ $service['text'] }}</p>
                <x-tag>{{ $service['tags'] }}</x-tag>
            </div>
        @endforeach
    </div>
</section>
