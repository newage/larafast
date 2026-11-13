@php
    $links = ['jak' => 'Jak pracuję', 'uslugi' => 'Usługi', 'realizacje' => 'Realizacje', 'rekomendacje' => 'Opinie', 'czat' => 'Czat AI'];
@endphp

<header class="nav" id="top">
    <a class="brand" href="#top" aria-label="Vadym Leontiev, początek strony"><span class="mark">{VL}</span>Vadym Leontiev</a>
    <nav aria-label="Główna">
        <ul>
            @foreach ($links as $id => $label)
                <li><a href="#{{ $id }}">{{ $label }}</a></li>
            @endforeach
        </ul>
    </nav>
    <a class="btn btn-dark btn-sm" href="{{ config('site.calendar_url') }}" target="_blank" rel="noopener"><x-glyph name="calendar" width="16" height="16" />Umów 15 minut</a>
    <button type="button" class="burger" aria-label="Menu" aria-expanded="false" aria-controls="panel"><span></span><span></span></button>
</header>

<div class="panel" id="panel" aria-hidden="true">
    @foreach ($links as $id => $label)
        <a href="#{{ $id }}">{{ $label }}</a>
    @endforeach
    <a class="btn btn-dark btn-lg" href="{{ config('site.calendar_url') }}" target="_blank" rel="noopener"><x-glyph name="calendar" />Umów 15 minut rozmowy</a>
</div>
