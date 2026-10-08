@props(['paginator', 'label' => 'records'])
@if ($paginator instanceof \Illuminate\Contracts\Pagination\Paginator && $paginator->total() > 0)
    <div class="flex flex-wrap items-center justify-between gap-3 border-t border-ink-200/80 px-4 py-3 dark:border-ink-800">
        <p class="text-xs text-ink-500">
            Showing <span class="font-semibold text-ink-700 dark:text-ink-300 num">{{ $paginator->firstItem() }}</span>–<span
                class="font-semibold text-ink-700 dark:text-ink-300 num">{{ $paginator->lastItem() }}</span>
            of <span class="font-semibold text-ink-700 dark:text-ink-300 num">{{ $paginator->total() }}</span> {{ $label }}
        </p>
        @if ($paginator->hasPages())
            <div class="pagination-wrap">{{ $paginator->onEachSide(1)->withQueryString()->links() }}</div>
        @endif
    </div>
@endif
