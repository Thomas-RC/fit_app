@props([
    'emoji' => '📭',
    'title' => 'Nie znaleziono elementów',
    'description' => '',
    'actionText' => null,
    'actionRoute' => null,
])

<div class="bg-white rounded-lg shadow p-12 text-center" {{ $attributes }}>
    <div class="text-6xl mb-4">{{ $emoji }}</div>
    <h3 class="text-xl font-semibold text-gray-900 mb-2">{{ $title }}</h3>
    @if($description)
        <p class="text-gray-600 mb-6">{{ $description }}</p>
    @endif
    @if($actionText && $actionRoute)
        <a href="{{ $actionRoute }}" class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-emerald-500 to-teal-600 text-white rounded-md hover:shadow-lg transition font-semibold">
            {{ $actionText }}
        </a>
    @endif
    {{ $slot }}
</div>
