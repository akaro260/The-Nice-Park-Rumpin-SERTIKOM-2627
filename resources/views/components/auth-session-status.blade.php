@props(['status'])

@if ($status)
    <div {{ $attributes->merge(['class' => 'font-medium text-sm text-brand-leaf']) }}>
        {{ $status }}
    </div>
@endif
