<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Site Contact
    |--------------------------------------------------------------------------
    |
    | The address behind every "Umów bezpłatną rozmowę" call to action on the
    | landing page. It is rendered as a mailto link, so no mail transport is
    | involved.
    |
    */

    'contact_email' => env('SITE_CONTACT_EMAIL', 'hello@vadymleontiev.dev'),

    /*
    |--------------------------------------------------------------------------
    | Calendar Booking
    |--------------------------------------------------------------------------
    |
    | Public Google Calendar appointment page behind every "Umów 15 minut"
    | call to action on the landing page.
    |
    */

    'calendar_url' => env('SITE_CALENDAR_URL', 'https://calendar.app.google/xVjCjasQxYJgKxyN9'),

];
