@props(['title' => null, 'padding' => true])

<div class="bg-white rounded-lg shadow {{ $padding ? 'p-6' : '' }}" {{ $attributes }}>
    @if($title)
        <h3 class="text-lg font-bold text-gray-900 mb-4">{{ $title }}</h3>
    @endif
    {{ $slot }}
</div>
