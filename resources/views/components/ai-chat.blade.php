@php
    $chips = ['Ile kosztuje strona?', 'Co można zautomatyzować?', 'Czy to bezpieczne dla danych klientów?'];
@endphp

<section class="block reveal" id="czat" aria-labelledby="chat-h" style="padding-top:0">
    <div class="wrap">
        <p class="kicker">Czat AI</p>
        <h2 class="h2" id="chat-h">Zamiast FAQ: zapytaj mojego asystenta AI.</h2>
        <p class="lead">To przykład tego, co mogę zbudować dla Twojej firmy. Opisz swoją firmę i zadanie, a asystent podpowie, co da się zautomatyzować, jak wygląda współpraca i od czego zależy cena.</p>

        <div class="shell chat">
            <div data-chat data-max-messages="{{ (int) \App\Models\Setting::resolve('MAX_MESSAGES') }}" data-max-chars="{{ (int) \App\Models\Setting::resolve('MAX_MESSAGE_CHARS') }}" class="shell-in chat-win">
                <div data-chat-log role="log" aria-live="polite" aria-label="Rozmowa z asystentem" class="chat-log">
                    <div class="msg bot">Cześć. Jestem asystentem AI. Opisz krótko swoją firmę i zadanie, a podpowiem, co da się zautomatyzować lub zbudować.</div>
                </div>

                <div class="suggest">
                    @foreach ($chips as $chip)
                        <button type="button" data-chat-chip>{{ $chip }}</button>
                    @endforeach
                </div>

                <form data-chat-form class="chat-form">
                    <input data-chat-input type="text" aria-label="Twoja wiadomość" placeholder="Napisz wiadomość" autocomplete="off">
                    <button type="submit" class="btn btn-dark"><x-glyph name="send" />Wyślij</button>
                </form>
                <p class="chat-hint">Rozmawiasz z asystentem AI. Nie podawaj danych wrażliwych. <a href="/polityka-prywatnosci" style="text-decoration:underline">Polityka prywatności</a></p>
            </div>
        </div>
    </div>
</section>
