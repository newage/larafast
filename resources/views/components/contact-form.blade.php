<div class="shell form-shell">
    <form class="shell-in lead-form" data-contact-form data-endpoint="{{ url('/contact') }}" novalidate>
        <div class="fields" data-contact-fields>
            <div class="field">
                <label for="f-name">Imię i nazwisko</label>
                <input id="f-name" name="name" type="text" autocomplete="name" placeholder="Anna Kowalczyk" maxlength="120">
            </div>
            <div class="field">
                <label for="f-email">E-mail <span class="req">wymagany</span></label>
                <input id="f-email" name="email" type="email" autocomplete="email" placeholder="anna@firma.pl" required maxlength="160" aria-describedby="e-email">
                <p class="ferr" id="e-email" data-contact-email-error hidden></p>
            </div>
            <div class="field full">
                <label for="f-msg">Opisz swój problem</label>
                <textarea id="f-msg" name="message" rows="5" maxlength="3000" placeholder="Np. Co tydzień ręcznie przepisujemy zamówienia z maili do Excela i CRM. Zajmuje to dwóm osobom około 6 godzin."></textarea>
                <p class="fhint"><span data-contact-count>0</span> / 3000</p>
            </div>
            <input type="text" name="company_website" tabindex="-1" autocomplete="off" class="hp" aria-hidden="true">
            <div class="form-foot full">
                <p class="consent">Wysyłając formularz, zgadzasz się na kontakt w sprawie zapytania. Szczegóły w <a href="/polityka-prywatnosci">polityce prywatności</a>.</p>
                <button class="btn btn-dark btn-lg" type="submit" data-contact-send><x-glyph name="send" />Wyślij</button>
            </div>
            <p class="ferr full" data-contact-form-error role="alert" hidden></p>
        </div>
        <div class="sent" data-contact-sent hidden tabindex="-1">
            <div class="sent-ic"><x-glyph name="check" width="24" height="24" /></div>
            <h3>Zapytanie wysłane</h3>
            <p>Odpowiem na <b data-contact-sent-email></b> w ciągu kilku godzin, z orientacyjnym planem i ceną.</p>
        </div>
    </form>
</div>
