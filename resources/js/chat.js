const replies = [
    [['cen', 'kosz', 'ile ', 'wycen', 'płat'], 'Każdy projekt ma stałą cenę, którą ustalamy na piśmie po bezpłatnej rozmowie. Cena zależy głównie od zakresu: liczby podstron, integracji i tego, ile procesów ma działać automatycznie.'],
    [['dane', 'bezpiecz', 'rodo', 'prywat', 'poufn'], 'Projektuję procesy tak, aby do modelu AI trafiały tylko dane potrzebne do danego zadania. Przed startem dostajesz listę usług, z których korzystamy, a wrażliwe kroki mogą wymagać Twojej akceptacji.'],
    [['automat', 'faktur', 'arkusz', 'proces', 'zautomat'], 'Automatyzować warto wszystko, co powtarza się według stałych reguł: sortowanie zapytań, uzupełnianie arkuszy, przypomnienia dla klientów, przygotowanie faktur czy szkice odpowiedzi. Opisz jedno zadanie, które zajmuje Ci najwięcej czasu.'],
    [['wordpress', 'laravel', 'stron', 'www', 'sklep'], 'Strony buduję w Laravel: bez wtyczek wymagających ciągłego łatania, szybkie i z możliwością własnej logiki, na przykład rezerwacji czy kalkulatorów. Kod i konta zostają Twoje.'],
    [['asystent', 'chat', 'bot', 'ai'], 'Asystent AI odpowiada klientom na typowe pytania na podstawie Twoich własnych dokumentów, a trudniejsze sprawy przekazuje Tobie razem z podsumowaniem rozmowy.'],
    [['termin', 'kiedy', 'jak długo', 'czas'], 'Czas zależy od zakresu. Dokładny harmonogram dostajesz w pisemnej ofercie, jeszcze przed rozpoczęciem pracy.'],
    [['umów', 'umow', 'kontakt', 'rozmow', 'mail'], 'Najprościej umówić bezpłatną rozmowę trwającą 30 minut. Użyj przycisku na tej stronie, a omówimy Twoje zadanie.'],
];

const fallback = 'Dziękuję za wiadomość. Żeby dobrze doradzić, potrzebuję trochę więcej szczegółów: czym zajmuje się Twoja firma i jakie zadanie chcesz uprościć? Możesz też od razu umówić bezpłatną rozmowę.';

const answer = (raw) => {
    const text = raw.toLowerCase();
    const match = replies.find(([words]) => words.some((word) => text.includes(word)));

    return match ? match[1] : fallback;
};

const bubble = (text, own) => {
    const el = document.createElement('div');
    el.className = own
        ? 'msg max-w-[80%] self-end rounded-xl bg-ink p-3 text-canvas'
        : 'msg max-w-[80%] self-start rounded-xl bg-surface-raised p-3';
    el.textContent = text;

    return el;
};

const initChat = (root) => {
    const log = root.querySelector('[data-chat-log]');
    const form = root.querySelector('[data-chat-form]');
    const input = root.querySelector('[data-chat-input]');
    let typing = false;

    // The log is column-reverse, so the newest message goes first in the DOM.
    const push = (el) => log.insertBefore(el, log.firstChild);

    const send = (raw) => {
        const text = raw.trim();

        if (!text || typing) {
            return;
        }

        typing = true;
        push(bubble(text, true));
        input.value = '';

        const indicator = bubble('Asystent pisze…', false);
        indicator.classList.add('text-muted');
        push(indicator);

        setTimeout(() => {
            indicator.remove();
            push(bubble(answer(text), false));
            typing = false;
        }, 900);
    };

    form.addEventListener('submit', (event) => {
        event.preventDefault();
        send(input.value);
    });

    root.querySelectorAll('[data-chat-chip]').forEach((chip) => {
        chip.addEventListener('click', () => send(chip.textContent));
    });
};

document.querySelectorAll('[data-chat]').forEach(initChat);
