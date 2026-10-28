@php
    $steps = [
        [
            'title' => 'Rozmowa wstępna',
            'text' => 'Trzydzieści minut. Opisujesz zadanie, które pochłania najwięcej czasu. Zadaję pytania i uczciwie mówię, czy oprogramowanie jest tu właściwym rozwiązaniem.',
            'svg' => '<path d="M4 5h16v11H9l-5 4z"/><path d="M8 9.5h8M8 12.5h5"/>',
        ],
        [
            'title' => 'Pisemna oferta',
            'text' => 'Przed rozpoczęciem pracy dostajesz zakres, cenę i harmonogram prostym językiem, bez niespodzianek.',
            'svg' => '<path d="M7 3h8l4 4v14H7z"/><path d="M15 3v4h4M10 12h6M10 16h6"/>',
        ],
        [
            'title' => 'Realizacja i przekazanie',
            'text' => 'Pracuję etapami i pokazuję efekt po każdym z nich. Na końcu otrzymujesz kod, dokumentację i omówienie rozwiązania.',
            'svg' => '<path d="M9 8l-5 4 5 4M15 8l5 4-5 4"/>',
        ],
    ];
    $technologies = ['Laravel', 'PHP', 'Python', 'FastAPI', 'LangChain', 'Claude API', 'MCP', 'Docker'];
@endphp

<section id="process" class="mx-auto max-w-[1120px] scroll-mt-20 px-6 py-16 sm:py-24">
    <div class="reveal">
        <div class="font-mono text-sm text-muted">Proces</div>
        <h2 class="mt-3 max-w-[680px] text-3xl font-semibold tracking-[-0.02em] sm:text-4xl">Trzy kroki od pomysłu do przekazania.</h2>
    </div>

    <div class="reveal mt-12 flex flex-wrap gap-4">
        @foreach ($steps as $i => $step)
            <div data-card="step" class="flex flex-[1_1_300px] flex-col gap-3 rounded-2xl border border-line p-6">
                <div class="flex items-center justify-between">
                    <span class="font-mono text-sm text-muted">{{ sprintf('%02d', $i + 1) }}</span>
                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#FFFFFF" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">{!! $step['svg'] !!}</svg>
                </div>
                <h3 class="text-xl font-semibold">{{ $step['title'] }}</h3>
                <p class="text-muted">{{ $step['text'] }}</p>
            </div>
        @endforeach
    </div>

    <div class="reveal mt-16 flex flex-col gap-4">
        <div class="text-sm text-muted">Technologie, na których pracuję</div>
        <div class="flex flex-wrap gap-2 font-mono text-sm">
            @foreach ($technologies as $technology)
                <span class="rounded-lg border border-line-strong px-3 py-2">{{ $technology }}</span>
            @endforeach
        </div>
    </div>
</section>
