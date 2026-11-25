<button {{ $attributes->merge(['type' => 'button', 'class' => 'inline-flex items-center rounded-md border border-brand-secondary bg-brand-surface px-4 py-2 text-xs font-semibold uppercase tracking-widest text-brand-charcoal shadow-sm hover:bg-brand-cream focus:outline-none focus:ring-2 focus:ring-brand-forest focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150']) }}>
    {{ $slot }}
</button>
