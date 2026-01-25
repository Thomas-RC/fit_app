@extends('layouts.app')

@section('title', $recipe['title'])

@section('content')
<div class="py-12" x-data="{ servings: {{ $recipe['servings'] ?? 1 }} }">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Back Button -->
        <div class="mb-6">
            <a href="{{ route('recipes.index') }}" class="text-emerald-600 hover:text-emerald-700 flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Back to Recipes
            </a>
        </div>

        <!-- Hero Section -->
        <div class="relative h-96 rounded-lg overflow-hidden mb-8">
            @if(isset($recipe['image']))
                <img src="{{ $recipe['image'] }}" alt="{{ $recipe['title'] }}" class="w-full h-full object-cover">
            @else
                <div class="w-full h-full bg-gradient-to-br from-emerald-100 to-teal-100 flex items-center justify-center">
                    <span class="text-9xl">🍽️</span>
                </div>
            @endif

            <!-- Gradient Overlay -->
            <div class="absolute inset-0 bg-gradient-to-t from-black/70 to-transparent"></div>

            <!-- Title & Meta -->
            <div class="absolute bottom-0 left-0 right-0 p-8 text-white">
                <h1 class="text-4xl font-bold mb-4">{{ $recipe['title'] }}</h1>
                <div class="flex items-center gap-6 text-sm">
                    @if(isset($recipe['readyInMinutes']))
                        <span class="flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            {{ $recipe['readyInMinutes'] }} minutes
                        </span>
                    @endif
                    @if(isset($recipe['servings']))
                        <span class="flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                            <span x-text="servings"></span> servings
                        </span>
                    @endif
                    @if(isset($recipe['aggregateLikes']))
                        <span class="flex items-center gap-2">
                            ❤️ {{ number_format($recipe['aggregateLikes']) }} likes
                        </span>
                    @endif
                </div>
            </div>
        </div>

        <!-- Action Bar -->
        <div class="flex gap-4 mb-8">
            @if(isset($recipe['sourceUrl']))
                <a href="{{ $recipe['sourceUrl'] }}" target="_blank" class="flex-1 text-center px-6 py-3 border border-emerald-500 text-emerald-600 rounded-md hover:bg-emerald-50 transition font-medium">
                    View Original Recipe
                    <svg class="inline w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                    </svg>
                </a>
            @endif
        </div>

        <div class="lg:flex lg:gap-8">
            <!-- Main Content -->
            <div class="lg:w-2/3 space-y-8">
                <!-- Ingredients -->
                <div class="bg-white rounded-lg shadow-lg p-8">
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-2xl font-bold text-gray-900">Ingredients</h2>

                        <!-- Servings Adjuster -->
                        @if(isset($recipe['servings']))
                            <div class="flex items-center gap-3">
                                <span class="text-sm text-gray-600">Servings:</span>
                                <button @click="servings = Math.max(1, servings - 1)" class="w-8 h-8 rounded-full bg-gray-200 hover:bg-gray-300 flex items-center justify-center">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/>
                                    </svg>
                                </button>
                                <span class="text-lg font-semibold w-8 text-center" x-text="servings"></span>
                                <button @click="servings++" class="w-8 h-8 rounded-full bg-gray-200 hover:bg-gray-300 flex items-center justify-center">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                    </svg>
                                </button>
                            </div>
                        @endif
                    </div>

                    @if(isset($recipe['extendedIngredients']) && count($recipe['extendedIngredients']) > 0)
                        <div class="space-y-3">
                            @foreach($recipe['extendedIngredients'] as $ingredient)
                                @php
                                    $hasIngredient = in_array(strtolower($ingredient['name']), $userIngredients);
                                @endphp
                                <label class="flex items-start gap-3 p-3 rounded-lg hover:bg-gray-50 transition {{ $hasIngredient ? 'bg-emerald-50' : '' }}">
                                    <input type="checkbox" class="mt-1 text-emerald-600 focus:ring-emerald-500 rounded">
                                    <div class="flex-1">
                                        <div class="flex items-center gap-2">
                                            <span class="text-gray-900">
                                                <span x-text="({{ $ingredient['amount'] ?? 0 }} * servings / {{ $recipe['servings'] ?? 1 }}).toFixed(2)"></span>
                                                {{ $ingredient['unit'] ?? '' }}
                                                <strong>{{ $ingredient['name'] }}</strong>
                                            </span>
                                            @if($hasIngredient)
                                                <span class="inline-flex items-center px-2 py-1 bg-emerald-100 text-emerald-700 rounded text-xs font-medium">
                                                    ✓ In your fridge
                                                </span>
                                            @endif
                                        </div>
                                        @if(isset($ingredient['original']))
                                            <div class="text-sm text-gray-500">{{ $ingredient['original'] }}</div>
                                        @endif
                                    </div>
                                </label>
                            @endforeach
                        </div>
                    @else
                        <p class="text-gray-500">No ingredient information available</p>
                    @endif
                </div>

                <!-- Instructions -->
                @if(isset($recipe['analyzedInstructions']) && count($recipe['analyzedInstructions']) > 0)
                    <div class="bg-white rounded-lg shadow-lg p-8">
                        <h2 class="text-2xl font-bold text-gray-900 mb-6">Instructions</h2>

                        @foreach($recipe['analyzedInstructions'] as $instruction)
                            @if(isset($instruction['steps']) && count($instruction['steps']) > 0)
                                <div class="space-y-4">
                                    @foreach($instruction['steps'] as $step)
                                        <label class="flex gap-4 p-4 rounded-lg hover:bg-gray-50 transition cursor-pointer">
                                            <input type="checkbox" class="mt-1 text-emerald-600 focus:ring-emerald-500 rounded">
                                            <div class="flex-1">
                                                <div class="flex items-center gap-3 mb-2">
                                                    <span class="flex-shrink-0 w-8 h-8 bg-emerald-100 text-emerald-700 rounded-full flex items-center justify-center font-semibold text-sm">
                                                        {{ $step['number'] }}
                                                    </span>
                                                    <h3 class="font-semibold text-gray-900">Step {{ $step['number'] }}</h3>
                                                </div>
                                                <p class="text-gray-700 ml-11">{{ $step['step'] }}</p>

                                                @if(isset($step['equipment']) && count($step['equipment']) > 0)
                                                    <div class="mt-2 ml-11 flex flex-wrap gap-2">
                                                        <span class="text-xs text-gray-500">Equipment:</span>
                                                        @foreach($step['equipment'] as $equipment)
                                                            <span class="inline-block px-2 py-1 bg-gray-100 text-gray-600 rounded text-xs">
                                                                {{ $equipment['name'] }}
                                                            </span>
                                                        @endforeach
                                                    </div>
                                                @endif
                                            </div>
                                        </label>
                                    @endforeach
                                </div>
                            @endif
                        @endforeach
                    </div>
                @elseif(isset($recipe['instructions']))
                    <div class="bg-white rounded-lg shadow-lg p-8">
                        <h2 class="text-2xl font-bold text-gray-900 mb-6">Instructions</h2>
                        <div class="prose max-w-none text-gray-700">
                            {!! $recipe['instructions'] !!}
                        </div>
                    </div>
                @endif
            </div>

            <!-- Sidebar -->
            <div class="lg:w-1/3 mt-8 lg:mt-0">
                <div class="space-y-6 sticky top-4">
                    <!-- Nutrition -->
                    @if(isset($recipe['nutrition']['nutrients']))
                        <div class="bg-white rounded-lg shadow-lg p-6">
                            <h3 class="text-lg font-bold text-gray-900 mb-4">Nutrition Facts</h3>
                            <div class="text-center mb-4 pb-4 border-b">
                                @php
                                    $calories = collect($recipe['nutrition']['nutrients'])->firstWhere('name', 'Calories');
                                @endphp
                                @if($calories)
                                    <div class="text-4xl font-bold text-emerald-600">{{ number_format($calories['amount']) }}</div>
                                    <div class="text-sm text-gray-600">Calories per serving</div>
                                @endif
                            </div>

                            <div class="space-y-3">
                                @php
                                    $mainNutrients = ['Protein', 'Carbohydrates', 'Fat', 'Fiber'];
                                    $nutrients = collect($recipe['nutrition']['nutrients'])->whereIn('name', $mainNutrients);
                                @endphp
                                @foreach($nutrients as $nutrient)
                                    <div class="flex justify-between items-center">
                                        <span class="text-sm font-medium text-gray-700">{{ $nutrient['name'] }}</span>
                                        <span class="text-sm text-gray-900">
                                            {{ number_format($nutrient['amount'], 1) }}{{ $nutrient['unit'] }}
                                        </span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <!-- Diet Info -->
                    @if(isset($recipe['diets']) && count($recipe['diets']) > 0)
                        <div class="bg-white rounded-lg shadow-lg p-6">
                            <h3 class="text-lg font-bold text-gray-900 mb-4">Diet Information</h3>
                            <div class="flex flex-wrap gap-2">
                                @foreach($recipe['diets'] as $diet)
                                    <span class="inline-block px-3 py-1 bg-emerald-50 text-emerald-700 rounded-full text-sm font-medium">
                                        {{ ucfirst($diet) }}
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <!-- Summary -->
                    @if(isset($recipe['summary']))
                        <div class="bg-white rounded-lg shadow-lg p-6">
                            <h3 class="text-lg font-bold text-gray-900 mb-4">About This Recipe</h3>
                            <div class="text-sm text-gray-700 prose prose-sm max-w-none">
                                {!! $recipe['summary'] !!}
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
