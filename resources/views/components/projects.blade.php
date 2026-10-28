@php
    $attrs = 'width="240" height="100" viewBox="0 0 240 100" fill="none" stroke="#9B9B9B" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"';
    $projects = [
        [
            'name' => 'Englishdom',
            'host' => 'englishdom.com',
            'url' => 'https://www.englishdom.com/',
            'text' => 'Internetowa szkoła angielskiego działająca od 2010 roku: lekcje wideo jeden na jeden, zajęcia grupowe, kluby konwersacyjne i aplikacje mobilne.',
            'tags' => 'PHP, backend, architektura',
            'svg' => '<rect x="20" y="8" width="140" height="84" rx="6"/><circle cx="90" cy="38" r="12" stroke="#FFFFFF"/><path d="M66 74c4-14 44-14 48 0"/><rect x="172" y="8" width="48" height="38" rx="4"/><rect x="172" y="54" width="48" height="38" rx="4"/>',
        ],
        [
            'name' => 'All Right Pro',
            'host' => 'allrightpro.com/pl',
            'url' => 'https://allrightpro.com/pl/',
            'text' => 'Internetowa szkoła angielskiego dla polskich uczniów: lekcje indywidualne z lektorami, kursy online i kluby konwersacyjne.',
            'tags' => 'Platforma online, backend',
            'svg' => '<rect x="20" y="10" width="200" height="12" rx="6"/><rect x="20" y="10" width="120" height="12" rx="6" stroke="#FFFFFF"/><rect x="20" y="38" width="60" height="52" rx="4"/><rect x="90" y="38" width="60" height="52" rx="4" stroke="#FFFFFF"/><rect x="160" y="38" width="60" height="52" rx="4"/><path d="M30 52h30M30 62h20M100 52h30M100 62h20M170 52h30M170 62h20"/>',
        ],
    ];
@endphp

<section id="projects" class="mx-auto max-w-[1120px] scroll-mt-20 px-6 py-16 sm:py-24">
    <div class="reveal">
        <div class="font-mono text-sm text-muted">Projekty</div>
        <h2 class="mt-3 max-w-[680px] text-3xl font-semibold tracking-[-0.02em] sm:text-4xl">Platformy, przy których pracowałem.</h2>
    </div>

    <div class="reveal mt-12 flex flex-wrap gap-4">
        @foreach ($projects as $project)
            <div data-card="project" class="card flex flex-[1_1_400px] flex-col gap-3 overflow-hidden rounded-2xl border border-line bg-surface p-6 hover:bg-surface-raised">
                <div aria-hidden="true" class="-mx-6 -mt-6 flex h-[140px] items-center justify-center bg-surface-raised">
                    <svg {!! $attrs !!}>{!! $project['svg'] !!}</svg>
                </div>
                <x-tag>{{ $project['host'] }}</x-tag>
                <h3 class="text-2xl font-semibold">{{ $project['name'] }}</h3>
                <p class="text-muted">{{ $project['text'] }}</p>
                <x-tag>{{ $project['tags'] }}</x-tag>
                <div class="mt-3">
                    <a class="btn2 inline-flex min-h-11 items-center gap-2 rounded-lg border border-line px-3 py-2 font-semibold text-ink" href="{{ $project['url'] }}" target="_blank" rel="noopener noreferrer">Zobacz stronę
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#FFFFFF" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M7 17L17 7M8 7h9v9"/></svg>
                    </a>
                </div>
            </div>
        @endforeach
    </div>
</section>
