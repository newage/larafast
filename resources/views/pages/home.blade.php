@extends('layouts.app')

@section('content')
    <div class="relative w-full overflow-x-clip bg-canvas font-sans text-base text-ink">
        <x-backdrop />

        <a href="#main" class="absolute -top-20 left-4 z-10 rounded-lg bg-ink px-3 py-2 text-sm font-semibold text-canvas focus:top-4">Przejdź do treści</a>

        <x-nav />

        <main id="main" class="relative z-[1]">
            <x-hero />
            <x-intro />
            <x-services />
            <x-projects />
            <x-process />
            <x-ai-chat />
            <x-cta />
        </main>

        <x-footer />
    </div>
@endsection
