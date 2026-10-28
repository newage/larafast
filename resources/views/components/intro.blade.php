@php
    $words = explode(' ', 'Dobre oprogramowanie działa po cichu. Odpowiada na zapytanie, uzupełnia arkusz i wystawia fakturę, a Ty zajmujesz się tym, co potrafisz tylko Ty.');
@endphp

<section class="tag mx-auto max-w-[1120px] px-6 py-16 sm:py-24" aria-label="Podejście">
    <p class="max-w-[880px] text-3xl font-medium tracking-[-0.02em] sm:text-5xl sm:leading-none">
        @foreach ($words as $i => $word)<span class="w" style="--i:{{ $i }}">{{ $word }}</span>{{ ' ' }}@endforeach
    </p>
</section>
