@if ($paginator->hasPages())
    <nav class="flex items-center justify-between" role="navigation" aria-label="{{ __('Pagination Navigation') }}">
        <div class="flex sm:hidden">
            @if ($paginator->onFirstPage())
                <span class="inline-flex min-h-11 items-center rounded-sm border border-brand-secondary px-4 text-sm font-medium text-brand-secondary">
                    {{ __('pagination.previous') }}
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="inline-flex min-h-11 items-center rounded-sm border border-brand-secondary bg-brand-surface px-4 text-sm font-medium text-brand-charcoal hover:bg-brand-cream focus:outline-none focus:ring-2 focus:ring-brand-forest">
                    {{ __('pagination.previous') }}
                </a>
            @endif

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="ms-3 inline-flex min-h-11 items-center rounded-sm border border-brand-secondary bg-brand-surface px-4 text-sm font-medium text-brand-charcoal hover:bg-brand-cream focus:outline-none focus:ring-2 focus:ring-brand-forest">
                    {{ __('pagination.next') }}
                </a>
            @else
                <span class="ms-3 inline-flex min-h-11 items-center rounded-sm border border-brand-secondary px-4 text-sm font-medium text-brand-secondary">
                    {{ __('pagination.next') }}
                </span>
            @endif
        </div>

        <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
            <p class="text-sm text-brand-secondary">
                {!! __('Showing') !!}
                @if ($paginator->firstItem())
                    <span class="font-medium text-brand-charcoal">{{ $paginator->firstItem() }}</span>
                    {!! __('to') !!}
                    <span class="font-medium text-brand-charcoal">{{ $paginator->lastItem() }}</span>
                @else
                    {{ $paginator->count() }}
                @endif
                {!! __('of') !!}
                <span class="font-medium text-brand-charcoal">{{ $paginator->total() }}</span>
                {!! __('results') !!}
            </p>

            <div class="inline-flex rounded-sm shadow-sm">
                @foreach ($elements as $element)
                    @if (is_string($element))
                        <span class="inline-flex min-h-11 items-center border border-brand-secondary bg-brand-surface px-4 text-sm font-medium text-brand-secondary" aria-disabled="true">
                            {{ $element }}
                        </span>
                    @endif

                    @if (is_array($element))
                        @foreach ($element as $page => $url)
                            @if ($page == $paginator->currentPage())
                                <span class="inline-flex min-h-11 items-center border border-brand-forest bg-brand-sunshine px-4 text-sm font-semibold text-brand-forest" aria-current="page">
                                    {{ $page }}
                                </span>
                            @else
                                <a href="{{ $url }}" class="inline-flex min-h-11 items-center border border-brand-secondary bg-brand-surface px-4 text-sm font-medium text-brand-charcoal hover:bg-brand-cream focus:outline-none focus:ring-2 focus:ring-brand-forest" aria-label="{{ __('Go to page :page', ['page' => $page]) }}">
                                    {{ $page }}
                                </a>
                            @endif
                        @endforeach
                    @endif
                @endforeach
            </div>
        </div>
    </nav>
@endif
