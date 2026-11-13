import './chat';

const reduceMotion = matchMedia('(prefers-reduced-motion: reduce)').matches;

const initMenu = () => {
    const burger = document.querySelector('.burger');
    const panel = document.getElementById('panel');

    if (!burger || !panel) {
        return;
    }

    const links = [...panel.querySelectorAll('a')];
    const setMenu = (open) => {
        burger.setAttribute('aria-expanded', open);
        panel.classList.toggle('open', open);
        panel.setAttribute('aria-hidden', !open);
        document.body.style.overflow = open ? 'hidden' : '';
        links.forEach((a, i) => {
            a.style.transitionDelay = open ? `${Math.min(i * 60, 400)}ms` : '0ms';
        });
    };

    burger.addEventListener('click', () => setMenu(burger.getAttribute('aria-expanded') !== 'true'));
    links.forEach((a) => a.addEventListener('click', () => setMenu(false)));
};

const initReveal = () => {
    const items = document.querySelectorAll('.reveal');

    if (reduceMotion || !('IntersectionObserver' in window)) {
        items.forEach((el) => el.classList.add('in'));

        return;
    }

    const io = new IntersectionObserver((entries) => entries.forEach((entry) => {
        if (entry.isIntersecting) {
            entry.target.classList.add('in');
            io.unobserve(entry.target);
        }
    }), { rootMargin: '0px 0px -10% 0px' });

    items.forEach((el) => io.observe(el));
};

const initNavSpy = () => {
    const links = [...document.querySelectorAll('.nav ul a')];

    if (!links.length || !('IntersectionObserver' in window)) {
        return;
    }

    const spy = new IntersectionObserver((entries) => entries.forEach((entry) => {
        if (entry.isIntersecting) {
            links.forEach((a) => a.setAttribute('aria-current', a.getAttribute('href') === `#${entry.target.id}`));
        }
    }), { rootMargin: '-45% 0px -50% 0px' });

    links.forEach((a) => {
        const section = document.getElementById(a.getAttribute('href').slice(1));

        if (section) {
            spy.observe(section);
        }
    });
};

const initTagline = () => {
    const tagline = document.querySelector('[data-tagline]');

    if (!tagline) {
        return;
    }

    const accent = new Set((tagline.dataset.accent || '').split('|'));

    tagline.replaceChildren(...tagline.textContent.trim().split(/\s+/).flatMap((word, i) => {
        const span = document.createElement('span');
        span.className = accent.has(word) ? 'w acc' : 'w';
        span.textContent = word;

        return i === 0 ? [span] : [' ', span];
    }));

    const words = tagline.querySelectorAll('.w');

    if (reduceMotion || !('IntersectionObserver' in window)) {
        words.forEach((w) => w.classList.add('on'));

        return;
    }

    const io = new IntersectionObserver((entries) => entries.forEach((entry) => {
        if (entry.isIntersecting) {
            entry.target.classList.add('on');
            io.unobserve(entry.target);
        }
    }), { rootMargin: '0px 0px -40% 0px' });

    words.forEach((w) => io.observe(w));
};

// navigator.clipboard exists only in secure contexts, so fall back to a temporary textarea.
const copyText = async (text) => {
    if (navigator.clipboard?.writeText) {
        try {
            await navigator.clipboard.writeText(text);

            return true;
        } catch {
            // fall through to the legacy path
        }
    }

    const area = document.createElement('textarea');
    area.value = text;
    area.setAttribute('readonly', '');
    area.style.cssText = 'position:fixed;left:-9999px;top:0;opacity:0';
    document.body.appendChild(area);
    area.select();

    try {
        return document.execCommand('copy');
    } catch {
        return false;
    } finally {
        area.remove();
    }
};

const initCopyEmail = () => {
    document.querySelectorAll('[data-copy-email]').forEach((button) => {
        const state = button.querySelector('[data-copy-state]');
        const idle = state.textContent;
        let timer;

        button.addEventListener('click', async () => {
            const copied = await copyText(button.dataset.copyEmail);
            state.textContent = copied ? 'skopiowano' : 'nie udało się skopiować, zaznacz adres ręcznie';
            clearTimeout(timer);
            timer = setTimeout(() => {
                state.textContent = idle;
            }, 2400);
        });
    });
};

const contactErrors = {
    429: 'Zbyt wiele prób w krótkim czasie. Odczekaj chwilę i spróbuj ponownie.',
    422: 'Nie udało się przetworzyć formularza. Sprawdź wpisane dane i spróbuj ponownie.',
    default: 'Nie udało się wysłać formularza. Spróbuj ponownie albo napisz na hello@vadymleontiev.dev.',
};

const emailOk = (value) => /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test(value);

const initContactForm = (form) => {
    const email = form.querySelector('[name="email"]');
    const message = form.querySelector('[name="message"]');
    const emailError = form.querySelector('[data-contact-email-error]');
    const formError = form.querySelector('[data-contact-form-error]');
    const count = form.querySelector('[data-contact-count]');
    const fields = form.querySelector('[data-contact-fields]');
    const sent = form.querySelector('[data-contact-sent]');
    const send = form.querySelector('[data-contact-send]');
    const sendLabel = send.innerHTML;
    let sending = false;

    const showEmailError = (text) => {
        emailError.textContent = text;
        emailError.hidden = !text;
        email.setAttribute('aria-invalid', text ? 'true' : 'false');
    };

    message.addEventListener('input', () => {
        count.textContent = message.value.length;
    });
    email.addEventListener('blur', () => {
        const value = email.value.trim();

        if (value) {
            showEmailError(emailOk(value) ? '' : 'Sprawdź adres e-mail, np. anna@firma.pl.');
        }
    });
    email.addEventListener('input', () => {
        if (email.getAttribute('aria-invalid') === 'true' && emailOk(email.value.trim())) {
            showEmailError('');
        }
    });

    form.addEventListener('submit', async (event) => {
        event.preventDefault();

        if (sending) {
            return;
        }

        formError.hidden = true;
        const address = email.value.trim();

        if (!emailOk(address)) {
            showEmailError(address ? 'Sprawdź adres e-mail, np. anna@firma.pl.' : 'Podaj adres e-mail, żebym mógł odpowiedzieć.');
            email.focus();

            return;
        }

        showEmailError('');
        sending = true;
        send.disabled = true;
        send.innerHTML = '<span class="spin"></span>Wysyłam';

        try {
            const response = await fetch(form.dataset.endpoint, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
                body: JSON.stringify(Object.fromEntries(new FormData(form))),
            });
            const data = await response.json().catch(() => null);

            if (response.status === 422 && data?.errors?.email) {
                showEmailError('Sprawdź adres e-mail, np. anna@firma.pl.');
                email.focus();

                return;
            }

            if (!response.ok || data?.type !== 'ok') {
                throw new Error(contactErrors[response.status] ?? contactErrors.default);
            }

            form.querySelector('[data-contact-sent-email]').textContent = address;
            fields.hidden = true;
            sent.hidden = false;
            sent.focus();
        } catch (error) {
            formError.textContent = error instanceof TypeError ? contactErrors.default : error.message;
            formError.hidden = false;
        } finally {
            sending = false;
            send.disabled = false;
            send.innerHTML = sendLabel;
        }
    });
};

initMenu();
initReveal();
initNavSpy();
initTagline();
initCopyEmail();
document.querySelectorAll('[data-contact-form]').forEach(initContactForm);
