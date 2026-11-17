@props(['name'])

<svg {{ $attributes->merge(['width' => 20, 'height' => 20]) }} viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
    @switch($name)
        @case('calendar')<rect x="3" y="5" width="18" height="16" rx="2"/><path d="M3 10h18M8 3v4M16 3v4"/>@break
        @case('chat')<path d="M21 12a8 8 0 0 1-11.6 7.1L4 20l1-4.6A8 8 0 1 1 21 12z"/><path d="M8.5 12h.01M12 12h.01M15.5 12h.01"/>@break
        @case('buildings')<path d="M4 21V5l9-2v18M13 9h7v12M3 21h18M8 8h1M8 12h1M8 16h1M16 13h1M16 17h1"/>@break
        @case('user')<circle cx="12" cy="8" r="4"/><path d="M4 21c0-4 4-6 8-6s8 2 8 6"/>@break
        @case('x')<path d="M6 6l12 12M18 6L6 18"/>@break
        @case('check')<path d="M5 12.5l4.5 4.5L19 7"/>@break
        @case('coins')<circle cx="12" cy="12" r="9"/><path d="M12 7v10M9.5 9.5h4a1.5 1.5 0 0 1 0 3h-3a1.5 1.5 0 0 0 0 3H15"/>@break
        @case('arrow-up-right')<path d="M7 17L17 7M8 7h9v9"/>@break
        @case('chevron-left')<path d="M15 5l-7 7 7 7"/>@break
        @case('chevron-right')<path d="M9 5l7 7-7 7"/>@break
        @case('quotes')<path fill="currentColor" stroke="none" d="M10 8c-3 0-5 2-5 5v5h5v-5H7.5c0-1.5 1-3 2.5-3zM19 8c-3 0-5 2-5 5v5h5v-5h-2.5c0-1.5 1-3 2.5-3z"/>@break
        @case('send')<path d="M21 3L10 14M21 3l-7 18-4-7-7-4z"/>@break
        @case('copy')<rect x="9" y="9" width="11" height="11" rx="2"/><path d="M5 15V6a2 2 0 0 1 2-2h9"/>@break
    @endswitch
</svg>
