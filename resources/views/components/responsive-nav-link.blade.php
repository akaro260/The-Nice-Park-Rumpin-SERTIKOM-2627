@props(['active'])

@php
$classes = ($active ?? false)
            ? 'block w-full border-l-4 border-brand-forest bg-brand-cream py-2 pe-4 ps-3 text-start text-base font-medium text-brand-forest focus:outline-none focus:text-brand-forest focus:bg-brand-cream focus:border-brand-forest transition duration-150 ease-in-out'
            : 'block w-full border-l-4 border-transparent py-2 pe-4 ps-3 text-start text-base font-medium text-brand-secondary hover:text-brand-charcoal hover:bg-brand-cream hover:border-brand-secondary focus:outline-none focus:text-brand-charcoal focus:bg-brand-cream focus:border-brand-secondary transition duration-150 ease-in-out';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
