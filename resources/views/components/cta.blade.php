<section class="final reveal" id="kontakt" aria-labelledby="final-h">
    <div class="wrap">
        <h2 class="h2" id="final-h">Masz proces, który zjada czas Twojego zespołu?</h2>
        <p class="lead">Opowiedz o swoim problemie. Odpowiem w ciągu kilku godzin z orientacyjnym planem i ceną realizacji.</p>

        <x-contact-form />

        <p class="alt">Wolisz porozmawiać?</p>
        <div class="cta-row" style="margin-top:var(--s3)">
            <a class="btn btn-lg" href="{{ config('site.calendar_url') }}" target="_blank" rel="noopener"><x-glyph name="calendar" />Umów 15 minut rozmowy</a>
        </div>
        <button class="copy" data-copy-email="hello@vadymleontiev.dev" type="button" aria-label="Skopiuj adres hello@vadymleontiev.dev">
            hello@vadymleontiev.dev <x-glyph name="copy" /><small data-copy-state>kliknij, aby skopiować</small>
        </button>
    </div>
</section>
