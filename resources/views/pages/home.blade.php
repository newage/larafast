@extends('layouts.app')

@section('title', 'Vadym Leontiev · Automatyzacje AI, chatboty i aplikacje dla firm')
@section('description', 'Chatboty AI 24/7, automatyzacja procesów między działami i aplikacje na Laravel i Symfony. Jeden programista z 20 latami doświadczenia: MVP w 2–3 dni, płatność po zakończeniu pracy, faktura VAT.')

@section('content')
    <div class="lp">
        <a href="#main" class="skip">Przejdź do treści</a>

        <x-nav />

        <main id="main">
            <x-hero />
            <x-process />
            <x-vs />
            <x-services />
            <x-tagline />
            <x-rules />
            <x-projects />
            <x-recommendations />
            <x-ai-chat />
            <x-cta />
        </main>

        <x-footer />
    </div>
@endsection
