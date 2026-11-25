<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex min-h-12 items-center justify-center rounded-sm border border-brand-forest bg-brand-sunshine px-5 text-sm font-semibold text-brand-forest hover:bg-brand-sunshine/90 active:bg-brand-sunshine/80 focus:outline-none focus:ring-2 focus:ring-brand-forest focus:ring-offset-2 focus:ring-offset-brand-surface disabled:cursor-not-allowed disabled:opacity-60']) }}>
    {{ $slot }}
</button>
