@props(['active'])

@php
$classes = ($active ?? false)
            ? 'inline-flex items-center border-b-2 border-brand-leaf px-1 pt-1 text-sm font-medium leading-5 text-brand-charcoal focus:outline-none focus:border-brand-forest transition duration-150 ease-in-out'
            : 'inline-flex items-center border-b-2 border-transparent px-1 pt-1 text-sm font-medium leading-5 text-brand-secondary hover:text-brand-charcoal hover:border-brand-secondary focus:outline-none focus:text-brand-charcoal focus:border-brand-secondary transition duration-150 ease-in-out';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
