@extends('layouts.app')

@section('title', 'Preferences')

@section('content')
<div class="py-12">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">My Preferences</h1>
            <p class="text-gray-600 mt-2">Customize your meal planning experience</p>
        </div>

        <!-- Success Message -->
        @if(session('success'))
            <div class="mb-6 p-4 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-lg">
                {{ session('success') }}
            </div>
        @endif

        <!-- Form -->
        <form action="{{ route('preferences.update') }}" method="POST" class="bg-white rounded-lg shadow-lg p-8">
            @csrf
            @method('PUT')

            <!-- Diet Type Section -->
            <div class="mb-8">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">Diet Type</h2>
                <div x-data="{ selected: '{{ old('diet_type', $preferences->diet_type ?? 'omnivore') }}' }" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @php
                        $dietTypes = [
                            'omnivore' => ['emoji' => '🥩', 'label' => 'Omnivore', 'desc' => 'All foods including meat'],
                            'vegetarian' => ['emoji' => '🥗', 'label' => 'Vegetarian', 'desc' => 'No meat, includes dairy & eggs'],
                            'vegan' => ['emoji' => '🌱', 'label' => 'Vegan', 'desc' => 'No animal products'],
                            'keto' => ['emoji' => '🥓', 'label' => 'Keto', 'desc' => 'Low-carb, high-fat diet']
                        ];
                    @endphp

                    @foreach($dietTypes as $value => $data)
                        <label
                            class="relative border-2 rounded-lg p-6 cursor-pointer transition"
                            :class="selected === '{{ $value }}' ? 'border-emerald-500 bg-emerald-50' : 'border-gray-200 hover:border-emerald-300'"
                        >
                            <input
                                type="radio"
                                name="diet_type"
                                value="{{ $value }}"
                                x-model="selected"
                                class="sr-only"
                                {{ old('diet_type', $preferences->diet_type ?? 'omnivore') === $value ? 'checked' : '' }}
                            >
                            <div class="text-4xl mb-3">{{ $data['emoji'] }}</div>
                            <div class="font-semibold text-gray-900 mb-1">{{ $data['label'] }}</div>
                            <div class="text-sm text-gray-600">{{ $data['desc'] }}</div>
                            <div
                                x-show="selected === '{{ $value }}'"
                                class="absolute top-4 right-4 w-6 h-6 bg-emerald-600 rounded-full flex items-center justify-center"
                            >
                                <span class="text-white text-sm">✓</span>
                            </div>
                        </label>
                    @endforeach
                </div>
                @error('diet_type')
                    <p class="text-red-600 text-sm mt-2">{{ $message }}</p>
                @enderror
            </div>

            <!-- Daily Calories Section -->
            <div class="mb-8">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">Daily Calorie Target</h2>
                <div x-data="{ calories: {{ old('daily_calories', $preferences->daily_calories ?? 2000) }} }">
                    <div class="flex items-center gap-4 mb-4">
                        <input
                            type="number"
                            name="daily_calories"
                            x-model="calories"
                            min="1000"
                            max="5000"
                            step="50"
                            class="flex-1 border border-gray-300 rounded-md px-4 py-3 text-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                        >
                        <span class="text-gray-600 font-medium">kcal/day</span>
                    </div>
                    <p class="text-sm text-gray-600 mb-4">Recommended: 1800-2500 kcal for most adults</p>
                    <div class="flex gap-2">
                        <button
                            type="button"
                            @click="calories = 1500"
                            class="px-4 py-2 border border-gray-300 rounded-md text-sm hover:bg-gray-50"
                        >
                            1500
                        </button>
                        <button
                            type="button"
                            @click="calories = 2000"
                            class="px-4 py-2 border border-gray-300 rounded-md text-sm hover:bg-gray-50"
                        >
                            2000
                        </button>
                        <button
                            type="button"
                            @click="calories = 2500"
                            class="px-4 py-2 border border-gray-300 rounded-md text-sm hover:bg-gray-50"
                        >
                            2500
                        </button>
                    </div>
                </div>
                @error('daily_calories')
                    <p class="text-red-600 text-sm mt-2">{{ $message }}</p>
                @enderror
            </div>

            <!-- Allergies (Future Feature) -->
            <div class="mb-8 opacity-50">
                <h2 class="text-xl font-semibold text-gray-900 mb-2">Allergies & Exclusions</h2>
                <p class="text-sm text-gray-600 mb-4">Coming soon in next version</p>
                <div class="bg-gray-50 border-2 border-dashed border-gray-300 rounded-lg p-6 text-center text-gray-500">
                    Feature in development
                </div>
            </div>

            <!-- Actions -->
            <div class="flex gap-4">
                <a
                    href="{{ route('dashboard') }}"
                    class="flex-1 px-6 py-3 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50 text-center"
                >
                    Cancel
                </a>
                <button
                    type="submit"
                    class="flex-1 px-6 py-3 bg-gradient-to-r from-emerald-500 to-teal-600 text-white rounded-md hover:shadow-lg transition font-semibold"
                >
                    Save Preferences
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
