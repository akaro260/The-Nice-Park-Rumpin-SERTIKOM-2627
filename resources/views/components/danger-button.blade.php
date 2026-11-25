<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center rounded-md border border-brand-adventure bg-brand-charcoal px-4 py-2 text-xs font-semibold uppercase tracking-widest text-brand-cream hover:bg-brand-forest active:bg-brand-charcoal focus:outline-none focus:ring-2 focus:ring-brand-sunshine focus:ring-offset-2 transition ease-in-out duration-150']) }}>
    {{ $slot }}
</button>
