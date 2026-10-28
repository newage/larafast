@php
    $steps = [
        ['Z formularza kontaktowego wpada nowe zapytanie', 'Gotowe'],
        ['Claude czyta treść i oznacza temat oraz pilność', 'Gotowe'],
        ['Kontakt trafia do arkusza z klientami', 'Gotowe'],
        ['Szkic odpowiedzi czeka na Twoją akceptację', 'Czeka na Ciebie'],
    ];
@endphp

<section class="mx-auto flex max-w-[1120px] flex-col items-center px-6 py-16 text-center sm:py-24">
    <div class="in inline-flex items-center rounded-lg border border-line bg-canvas px-3 py-1 font-mono text-sm text-muted">Strony www i automatyzacja AI</div>

    <h1 class="in mt-6 max-w-[680px] bg-linear-to-r from-ink to-muted bg-clip-text text-4xl font-semibold tracking-[-0.025em] text-transparent sm:text-5xl sm:leading-none" style="animation-delay:80ms">Strony, które sprzedają.<br>Automatyzacje, które pracują.</h1>

    <p class="in mt-6 max-w-[680px] text-lg text-muted" style="animation-delay:160ms">Tworzę szybkie strony firmowe w Laravel oraz procesy z AI, które zdejmują z zespołu powtarzalną pracę. Jeden programista, jeden kontakt, ustalony zakres przed startem.</p>

    <div class="in mt-8 flex flex-col items-center gap-4" style="animation-delay:240ms">
        <x-cta-button>Umów bezpłatną rozmowę</x-cta-button>
        <p class="text-sm text-muted">13 lat doświadczenia w backendzie stoi za każdym projektem</p>
    </div>

    <div class="in mt-16 w-full max-w-[720px] rounded-3xl border border-line bg-surface p-3 text-left" style="animation-delay:320ms">
        <div class="flex flex-col gap-4 rounded-xl bg-surface-raised p-6">
            <div class="flex items-center justify-between font-mono text-sm text-muted">
                <span>Przykładowy proces</span>
                <span>Zapytanie klienta</span>
            </div>
            @foreach ($steps as $i => [$label, $status])
                <div class="flex flex-wrap items-center gap-3 py-3">
                    <span class="font-mono text-sm text-muted">{{ sprintf('%02d', $i + 1) }}</span>
                    <span class="flex-[1_1_200px]">{{ $label }}</span>
                    <span class="font-mono text-xs {{ $loop->last ? 'text-ink' : 'text-muted' }}">{{ $status }}</span>
                </div>
            @endforeach
        </div>
    </div>
</section>
