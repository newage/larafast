const errorMessages = {
    429: 'Zbyt wiele wiadomości w krótkim czasie. Odczekaj chwilę i spróbuj ponownie.',
    502: 'Asystent jest chwilowo niedostępny. Spróbuj ponownie za chwilę.',
    422: 'Nie udało się przetworzyć wiadomości. Skróć ją lub odśwież stronę i spróbuj ponownie.',
    default: 'Coś poszło nie tak. Sprawdź połączenie i spróbuj ponownie.',
};

const bubble = (text, own) => {
    const el = document.createElement('div');
    el.className = own
        ? 'msg max-w-[80%] self-end rounded-xl bg-ink p-3 text-canvas'
        : 'msg max-w-[80%] self-start rounded-xl bg-surface-raised p-3';
    el.textContent = text;

    return el;
};

// MAX_MESSAGES counts the current question too, so history gets at most maxMessages - 1 pairs.
const buildMessages = (history, question, maxMessages) => {
    const keep = Math.max(maxMessages - 1, 0);
    const pairs = keep === 0 ? [] : history.slice(-keep);

    return [...pairs, { question }];
};

const initChat = (root) => {
    const log = root.querySelector('[data-chat-log]');
    const form = root.querySelector('[data-chat-form]');
    const input = root.querySelector('[data-chat-input]');
    const maxMessages = parseInt(root.dataset.maxMessages, 10) || 2;
    const maxChars = parseInt(root.dataset.maxChars, 10);
    const history = [];
    let typing = false;

    if (maxChars > 0) {
        input.maxLength = maxChars;
    }

    // The log is column-reverse, so the newest message goes first in the DOM.
    const push = (el) => log.insertBefore(el, log.firstChild);

    const ask = async (question) => {
        let response;

        try {
            response = await fetch('/chat', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
                body: JSON.stringify({ messages: buildMessages(history, question, maxMessages) }),
            });
        } catch {
            return { error: errorMessages.default };
        }

        const data = await response.json().catch(() => null);

        if (response.ok && data?.type === 'answer' && typeof data.message === 'string' && data.message.trim() !== '') {
            return { answer: data.message };
        }

        return { error: errorMessages[response.status] ?? errorMessages.default };
    };

    const send = async (raw) => {
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

        try {
            const { answer, error } = await ask(text);

            if (answer !== undefined) {
                history.push({ question: text, answer });
            }

            push(bubble(answer ?? error, false));
        } finally {
            indicator.remove();
            typing = false;
        }
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
