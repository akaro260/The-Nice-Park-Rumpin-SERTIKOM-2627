@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'min-h-12 w-full rounded-sm border-brand-secondary bg-brand-surface text-brand-charcoal placeholder:text-brand-secondary focus:border-brand-forest focus:ring-2 focus:ring-brand-forest/20']) }}>
