@extends('layouts.app')

@section('title', 'Polityka prywatności')
@section('description', 'Polityka prywatności: jakie dane przetwarzam na stronie i w czacie z asystentem AI.')

@section('content')
    <div class="w-full bg-canvas font-sans text-base text-ink">
        <div class="flex justify-center px-4 pt-4">
            <nav aria-label="Główna" class="flex items-center gap-8 rounded-xl border border-line bg-[rgba(24,24,24,0.72)] py-2 pl-4 pr-2">
                <a href="/" class="text-sm font-semibold text-ink">Vadym Leontiev</a>
                <x-cta-button href="/" class="text-sm">Strona główna</x-cta-button>
            </nav>
        </div>

        <main class="mx-auto flex max-w-[760px] flex-col gap-12 px-6 py-16 sm:py-24">
            <header class="flex flex-col gap-4">
                <div class="font-mono text-sm text-muted">Dokument</div>
                <h1 class="bg-linear-to-r from-ink to-muted bg-clip-text text-4xl font-semibold tracking-[-0.025em] text-transparent sm:text-5xl sm:leading-none">Polityka prywatności</h1>
                <p class="text-lg text-muted">Wyjaśniam, jakie dane przetwarzam, gdy korzystasz z tej strony i rozmawiasz z asystentem AI. Ostatnia aktualizacja: 3 listopada 2026.</p>
            </header>

            <section class="flex flex-col gap-3">
                <h2 class="text-2xl font-semibold">1. Administrator danych</h2>
                <p class="text-muted">Administratorem Twoich danych osobowych jest Vadym Leontiev. Kontakt w sprawach danych osobowych: <a href="mailto:hello@vadymleontiev.dev" class="text-ink underline">hello@vadymleontiev.dev</a>.</p>
            </section>

            <section class="flex flex-col gap-3">
                <h2 class="text-2xl font-semibold">2. Jakie dane przetwarzam</h2>
                <ul class="flex list-disc flex-col gap-2 pl-6 text-muted">
                    <li>Treść wiadomości, które wpisujesz w czacie, oraz odpowiedzi asystenta.</li>
                    <li>Dane, które sam podasz w rozmowie, na przykład imię, adres email czy nazwę firmy.</li>
                    <li>Dane techniczne zapisywane przez serwer, takie jak adres IP, typ przeglądarki oraz czas połączenia.</li>
                </ul>
                <p class="text-muted">Podanie danych jest dobrowolne. Nie podawaj w czacie danych wrażliwych, takich jak dane o zdrowiu, numery dokumentów czy dane kart płatniczych.</p>
            </section>

            <section class="flex flex-col gap-3">
                <h2 class="text-2xl font-semibold">3. Cele i podstawy prawne</h2>
                <ul class="flex list-disc flex-col gap-2 pl-6 text-muted">
                    <li>Odpowiadanie na Twoje pytania w czacie oraz prowadzenie rozmowy. Podstawa: prawnie uzasadniony interes administratora, art. 6 ust. 1 lit. f RODO.</li>
                    <li>Podjęcie działań przed zawarciem umowy na Twoje żądanie, na przykład przygotowanie oferty. Podstawa: art. 6 ust. 1 lit. b RODO.</li>
                    <li>Zapewnienie bezpieczeństwa strony i wykrywanie nadużyć. Podstawa: prawnie uzasadniony interes administratora, art. 6 ust. 1 lit. f RODO.</li>
                </ul>
            </section>

            <section class="flex flex-col gap-3">
                <h2 class="text-2xl font-semibold">4. Czat z asystentem AI</h2>
                <p class="text-muted">Rozmawiasz z asystentem opartym na sztucznej inteligencji, a nie z człowiekiem. Treść Twoich wiadomości jest przekazywana dostawcy modelu AI, aby wygenerować odpowiedź. Odpowiedzi mogą być niedokładne, mają charakter informacyjny i nie stanowią oferty ani wiążącej wyceny. Asystent nie podejmuje wobec Ciebie decyzji wywołujących skutki prawne, a dane nie służą do profilowania.</p>
            </section>

            <section class="flex flex-col gap-3">
                <h2 class="text-2xl font-semibold">5. Odbiorcy danych</h2>
                <p class="text-muted">Dane mogą być powierzane podmiotom, które pomagają mi prowadzić stronę i czat:</p>
                <ul class="flex list-disc flex-col gap-2 pl-6 text-muted">
                    <li>dostawca hostingu: netcup,</li>
                    <li>dostawca modelu AI: Anthropic,</li>
                    <li>dostawca poczty email: Google (Gmail),</li>
                    <li>organy publiczne, jeśli obowiązek wynika z przepisów prawa.</li>
                </ul>
            </section>

            <section class="flex flex-col gap-3">
                <h2 class="text-2xl font-semibold">6. Przekazywanie danych poza EOG</h2>
                <p class="text-muted">Niektórzy z odbiorców mogą przetwarzać dane poza Europejskim Obszarem Gospodarczym. W takim przypadku dane są chronione standardowymi klauzulami umownymi lub innym mechanizmem przewidzianym w RODO.</p>
            </section>

            <section class="flex flex-col gap-3">
                <h2 class="text-2xl font-semibold">7. Jak długo przechowuję dane</h2>
                <p class="text-muted">Rozmowy z czatem przechowuję przez 30 dni, a następnie usuwam. Dane techniczne z logów serwera są przechowywane przez 30 dni. Jeśli napiszesz do mnie wiadomość email, zachowam ją na czas potrzebny do odpowiedzi i ewentualnej współpracy, a następnie na okres przedawnienia roszczeń.</p>
            </section>

            <section class="flex flex-col gap-3">
                <h2 class="text-2xl font-semibold">8. Twoje prawa</h2>
                <p class="text-muted">Masz prawo do dostępu do danych, ich sprostowania, usunięcia, ograniczenia przetwarzania, przenoszenia oraz wniesienia sprzeciwu wobec przetwarzania opartego na prawnie uzasadnionym interesie. Aby skorzystać z tych praw, napisz na adres <a href="mailto:hello@vadymleontiev.dev" class="text-ink underline">hello@vadymleontiev.dev</a>. Masz też prawo wnieść skargę do Prezesa Urzędu Ochrony Danych Osobowych, ul. Stawki 2, 00 193 Warszawa.</p>
            </section>

            <section class="flex flex-col gap-3">
                <h2 class="text-2xl font-semibold">9. Pliki cookies</h2>
                <p class="text-muted">Strona używa wyłącznie niezbędnych plików cookies: sesyjnego (laravel-session) i zabezpieczającego formularze (XSRF-TOKEN). Są one potrzebne do działania i bezpieczeństwa strony, wygasają po 2 godzinach i nie służą do celów analitycznych ani marketingowych. Nie stosuję cookies reklamowych ani narzędzi śledzących.</p>
            </section>

            <section class="flex flex-col gap-3">
                <h2 class="text-2xl font-semibold">10. Zmiany polityki</h2>
                <p class="text-muted">Jeśli zmienię sposób przetwarzania danych, zaktualizuję ten dokument i podam nową datę na jego początku.</p>
            </section>
        </main>

        <footer class="mx-auto flex max-w-[760px] flex-wrap justify-between gap-4 px-6 pb-12 pt-8 text-sm text-muted">
            <span>© {{ now()->year }} Vadym Leontiev</span>
            <a href="/" class="hover:text-ink">Wróć do strony głównej</a>
        </footer>
    </div>
@endsection
