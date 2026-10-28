@props(['href' => '#contact'])

<a href="{{ $href }}" {{ $attributes->class('btn inline-flex min-h-11 items-center rounded-lg bg-ink px-3 py-2 font-semibold text-canvas') }}>{{ $slot }}</a>
