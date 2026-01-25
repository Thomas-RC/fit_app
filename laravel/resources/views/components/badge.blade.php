@props(['color' => 'emerald', 'size' => 'md'])

@php
    $colors = [
        'emerald' => 'bg-emerald-100 text-emerald-700',
        'red' => 'bg-red-100 text-red-700',
        'amber' => 'bg-amber-100 text-amber-700',
        'blue' => 'bg-blue-100 text-blue-700',
        'gray' => 'bg-gray-100 text-gray-700',
        'purple' => 'bg-purple-100 text-purple-700',
    ];

    $sizes = [
        'sm' => 'px-2 py-0.5 text-xs',
        'md' => 'px-3 py-1 text-sm',
        'lg' => 'px-4 py-2 text-base',
    ];
@endphp

<span class="inline-flex items-center rounded-full font-medium {{ $colors[$color] ?? $colors['emerald'] }} {{ $sizes[$size] ?? $sizes['md'] }}" {{ $attributes }}>
    {{ $slot }}
</span>
