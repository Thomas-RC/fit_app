@props(['type' => 'info', 'message', 'dismissible' => true])

@php
    $styles = [
        'success' => 'bg-emerald-50 border-emerald-200 text-emerald-800',
        'error' => 'bg-red-50 border-red-200 text-red-800',
        'warning' => 'bg-amber-50 border-amber-200 text-amber-800',
        'info' => 'bg-blue-50 border-blue-200 text-blue-800',
    ];

    $icons = [
        'success' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>',
        'error' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>',
        'warning' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>',
        'info' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>',
    ];

    $iconColors = [
        'success' => 'text-emerald-600',
        'error' => 'text-red-600',
        'warning' => 'text-amber-600',
        'info' => 'text-blue-600',
    ];
@endphp

<div
    class="rounded-lg border p-4 {{ $styles[$type] ?? $styles['info'] }}"
    @if($dismissible) x-data="{ show: true }" x-show="show" x-cloak @endif
    {{ $attributes }}
>
    <div class="flex items-start">
        <svg class="w-5 h-5 mr-3 flex-shrink-0 mt-0.5 {{ $iconColors[$type] ?? $iconColors['info'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            {!! $icons[$type] ?? $icons['info'] !!}
        </svg>
        <div class="flex-1">
            <p class="text-sm font-medium">{{ $message ?? $slot }}</p>
        </div>
        @if($dismissible)
            <button @click="show = false" class="ml-3 {{ $iconColors[$type] ?? $iconColors['info'] }} hover:opacity-70">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        @endif
    </div>
</div>
