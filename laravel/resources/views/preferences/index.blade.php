@extends('layouts.app')

@section('title', 'Preferencje')

@section('content')
<div class="py-12">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">Moje preferencje</h1>
            <p class="text-gray-600 mt-2">Dostosuj swoje planowanie posiłków</p>
        </div>

        <!-- Success Message -->
        @if(session('success'))
            <div class="mb-6 assistant-box">
                {{ session('success') }}
            </div>
        @endif

        <!-- Form -->
        <form action="{{ route('preferences.update') }}" method="POST" class="fit-card green-bg-stripes p-8">
            @csrf
            @method('PUT')

            <!-- Diet Type Section -->
            <div class="mb-8">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">Typ diety</h2>
                <div x-data="{ selected: '{{ old('diet_type', $preferences->diet_type ?? 'omnivore') }}' }" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @php
                        $dietTypes = [
                            'omnivore' => ['emoji' => '🥩', 'label' => 'Wszystkożerna', 'desc' => 'Wszystkie produkty, włącznie z mięsem'],
                            'vegetarian' => ['emoji' => '🥗', 'label' => 'Wegetariańska', 'desc' => 'Bez mięsa, z nabiałem i jajkami'],
                            'vegan' => ['emoji' => '🌱', 'label' => 'Wegańska', 'desc' => 'Bez produktów pochodzenia zwierzęcego'],
                            'keto' => ['emoji' => '🥓', 'label' => 'Keto', 'desc' => 'Niskotłuszczowa, wysokobiałkowa']
                        ];
                    @endphp

                    @foreach($dietTypes as $value => $data)
                        <label
                            class="relative border-2 rounded-lg p-6 cursor-pointer transition"
                            :class="selected === '{{ $value }}' ? 'border-fit-green-500 bg-fit-green-50 opacity-100' : 'border-gray-200 bg-white hover:border-fit-green-300 opacity-40 hover:opacity-60'"
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
                                class="absolute top-4 right-4 w-6 h-6 bg-fit-green-600 rounded-full flex items-center justify-center"
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
                <h2 class="text-xl font-semibold text-gray-900 mb-4">Dzienny cel kaloryczny</h2>
                <div x-data="{ calories: {{ old('daily_calories', $preferences->daily_calories ?? 2000) }} }">
                    <div class="flex items-center gap-4 mb-4">
                        <input
                            type="number"
                            name="daily_calories"
                            x-model="calories"
                            min="1000"
                            max="5000"
                            step="50"
                            class="flex-1 border border-gray-300 rounded-md px-4 py-3 text-lg focus:ring-2 focus:ring-fit-green-500 focus:border-fit-green-500"
                        >
                        <span class="text-gray-600 font-medium">kcal/dzień</span>
                    </div>
                    <p class="text-sm text-gray-600 mb-4">Zalecane: 1800-2500 kcal dla większości dorosłych</p>
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
                <h2 class="text-xl font-semibold text-gray-900 mb-2">Alergie i wykluczenia</h2>
                <p class="text-sm text-gray-600 mb-4">Wkrótce w następnej wersji</p>
                <div class="bg-gray-50 border-2 border-dashed border-gray-300 rounded-lg p-6 text-center text-gray-500">
                    Funkcja w trakcie rozwoju
                </div>
            </div>

            <!-- Actions -->
            <div class="flex gap-4">
                <a
                    href="{{ route('dashboard') }}"
                    class="flex-1 btn-fit-secondary text-center"
                >
                    Anuluj
                </a>
                <button
                    type="submit"
                    class="flex-1 btn-fit-primary"
                >
                    Zapisz preferencje
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
