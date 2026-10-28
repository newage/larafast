@php
    $chips = ['Ile kosztuje strona?', 'Co można zautomatyzować?', 'Czy to bezpieczne dla danych klientów?'];
@endphp

<section id="chat" class="mx-auto max-w-[1120px] scroll-mt-20 px-6 py-16 sm:py-24">
    <div class="reveal flex flex-wrap items-start gap-12">
        <div class="flex flex-[1_1_320px] flex-col gap-4">
            <div class="font-mono text-sm text-muted">Czat AI</div>
            <h2 class="text-3xl font-semibold tracking-[-0.02em] sm:text-4xl">Zapytaj mojego asystenta.</h2>
            <p class="text-lg text-muted">Opisz swoją firmę i zadanie, a asystent podpowie, co da się zautomatyzować, jak wygląda współpraca i od czego zależy cena.</p>
            <div class="mt-2">
                <x-cta-button>Umów bezpłatną rozmowę</x-cta-button>
            </div>
        </div>

        <div data-chat class="flex min-w-0 flex-[1.6_1_480px] flex-col overflow-hidden rounded-2xl border border-line bg-surface">
            <div class="flex items-center gap-3 bg-surface-raised px-6 py-4">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#FFFFFF" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 3l1.8 5.2L19 10l-5.2 1.8L12 17l-1.8-5.2L5 10l5.2-1.8z"/><path d="M18 16v4M16 18h4"/></svg>
                <div>
                    <div class="font-semibold">Asystent AI</div>
                    <div class="text-sm text-muted">Odpowiada na pytania o usługi i współpracę</div>
                </div>
            </div>

            <div data-chat-log role="log" aria-live="polite" aria-label="Rozmowa z asystentem" class="flex h-[360px] flex-col-reverse gap-3 overflow-y-auto p-6">
                <div class="msg max-w-[80%] self-start rounded-xl bg-surface-raised p-3">Cześć. Jestem asystentem AI. Opisz krótko swoją firmę i zadanie, a podpowiem, co da się zautomatyzować lub zbudować.</div>
            </div>

            <div class="flex flex-wrap gap-2 px-6 pb-4">
                @foreach ($chips as $chip)
                    <button type="button" data-chat-chip class="chip min-h-9 cursor-pointer rounded-lg border border-line-strong bg-transparent px-3 py-2 text-sm text-ink">{{ $chip }}</button>
                @endforeach
            </div>

            <form data-chat-form class="flex gap-3 bg-surface-raised px-6 py-4">
                <input data-chat-input type="text" aria-label="Twoja wiadomość" placeholder="Napisz wiadomość" autocomplete="off" class="chat-input min-h-11 min-w-0 flex-1 rounded-lg border border-line-strong bg-canvas px-3 py-2 text-ink">
                <button type="submit" class="btn min-h-11 cursor-pointer rounded-lg bg-ink px-3 py-2 font-semibold text-canvas">Wyślij</button>
            </form>
        </div>
    </div>
</section>
