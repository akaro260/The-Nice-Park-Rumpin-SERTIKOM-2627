@props(['value'])

<label {{ $attributes->merge(['class' => 'block text-sm font-semibold text-brand-charcoal']) }}>
    {{ $value ?? $slot }}
</label>
