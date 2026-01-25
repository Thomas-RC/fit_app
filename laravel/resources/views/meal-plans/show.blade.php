@extends('layouts.app')

@section('title', 'Plan posiłków - ' . $mealPlan->date->translatedFormat('j M Y'))

@section('content')
<div class="py-12">
    <div class="container-wide">
        <!-- Main Card with Striped Background -->
        <div class="fit-card green-bg-stripes p-6">
            <!-- Header -->
            <div class="mb-6">
                <div class="flex items-center justify-between mb-4">
                    <a href="{{ route('meal-plans.index') }}" class="flex items-center gap-2 text-sm text-fit-green-600 hover:text-fit-green-700 no-underline">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                        </svg>
                        Powrót do planów posiłków
                    </a>
                    <form action="{{ route('meal-plans.destroy', $mealPlan) }}" method="POST" onsubmit="return confirm('Czy na pewno chcesz usunąć ten plan posiłków?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-red-600 hover:text-red-700 flex items-center gap-2 text-sm">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                            Usuń plan
                        </button>
                    </form>
                </div>

                <!-- Summary Header -->
                <div class="flex justify-between items-center mb-5">
                    <div>
                        <h1 class="text-2xl font-bold mb-1">Menu na dziś</h1>
                        <div class="text-sm text-gray-600">{{ $mealPlan->date->translatedFormat('l, j F Y') }}</div>
                        <div class="text-xs text-gray-500">Wygenerowano {{ $mealPlan->created_at->diffForHumans() }}</div>
                    </div>
                    <div class="text-right">
                        <span class="total-kcal">
                            Suma dnia: {{ number_format($mealPlan->total_calories) }} kcal
                        </span>
                    </div>
                </div>
            </div>

            <!-- Success Message -->
            @if(session('success'))
                <div class="mb-6 assistant-box">
                    {{ session('success') }}
                </div>
            @endif

            <!-- Nutrition Summary -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                <div class="nutrition-stat">
                    <div class="nutrition-stat-label">Kalorie</div>
                    <div class="nutrition-stat-value text-fit-green-600">{{ number_format($mealPlan->total_calories) }}</div>
                    <div class="text-xs text-gray-500">kcal</div>
                </div>
                <div class="nutrition-stat">
                    <div class="nutrition-stat-label">Białko</div>
                    <div class="nutrition-stat-value text-blue-600">{{ number_format($totalProtein) }}</div>
                    <div class="text-xs text-gray-500">g</div>
                </div>
                <div class="nutrition-stat">
                    <div class="nutrition-stat-label">Węglowodany</div>
                    <div class="nutrition-stat-value text-amber-600">{{ number_format($totalCarbs) }}</div>
                    <div class="text-xs text-gray-500">g</div>
                </div>
                <div class="nutrition-stat">
                    <div class="nutrition-stat-label">Tłuszcze</div>
                    <div class="nutrition-stat-value text-red-600">{{ number_format($totalFat) }}</div>
                    <div class="text-xs text-gray-500">g</div>
                </div>
            </div>

            <!-- Shopping List Section -->
            @if(count($missingIngredients) > 0)
            <div class="mb-6 p-6 bg-amber-50 border-2 border-amber-200 rounded-lg">
                <div class="flex items-center gap-3 mb-4">
                    <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                    <h3 class="text-xl font-bold text-gray-900 m-0">🛒 Dokup produkty</h3>
                </div>
                <p class="text-sm text-gray-600 mb-4">Poniższe składniki są potrzebne do przygotowania posiłków z tego planu (brakuje w Twojej lodówce):</p>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-2">
                    @foreach($missingIngredients as $ingredient)
                        <div class="flex items-center gap-2 text-sm text-gray-700 bg-white px-3 py-2 rounded border border-amber-200">
                            <svg class="w-4 h-4 text-amber-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                            {{ $ingredient }}
                        </div>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- Meals -->
            <div class="space-y-5">
                @foreach($mealPlan->recipes as $recipe)
                    @php
                        $recipeData = $recipe->recipe_data;
                    @endphp

                    <div class="meal-card">
                        <!-- Meal Header -->
                        <div class="meal-header">
                            <div class="flex items-center gap-3">
                                <span class="meal-type-badge
                                    @if($recipe->meal_type === 'breakfast') meal-type-breakfast
                                    @elseif($recipe->meal_type === 'lunch') meal-type-lunch
                                    @elseif($recipe->meal_type === 'dinner') meal-type-dinner
                                    @elseif($recipe->meal_type === 'snack') bg-purple-100 text-purple-700
                                    @endif">
                                    @if($recipe->meal_type === 'breakfast') 🥐 Śniadanie
                                    @elseif($recipe->meal_type === 'lunch') 🍽️ Obiad
                                    @elseif($recipe->meal_type === 'dinner') 🌙 Kolacja
                                    @elseif($recipe->meal_type === 'snack') 🍪 Przekąska
                                    @else Posiłek
                                    @endif
                                </span>
                                <h3 class="text-lg font-bold text-gray-900 m-0">{{ $recipe->recipe_title }}</h3>
                            </div>
                            <span class="kcal-badge">{{ number_format($recipe->calories) }} kcal</span>
                        </div>

                        <!-- Meal Body -->
                        <div class="meal-body">
                            <!-- Ingredients Section -->
                            <div class="ingredients">
                                <h4 class="font-semibold text-gray-700 mb-2">Składniki:</h4>
                                @if(isset($recipeData['extendedIngredients']) && count($recipeData['extendedIngredients']) > 0)
                                    <ul class="text-sm text-gray-600">
                                        @foreach($recipeData['extendedIngredients'] as $ingredient)
                                            <li>{{ $ingredient['original_pl'] ?? $ingredient['name_pl'] ?? $ingredient['original'] ?? $ingredient['name'] }}</li>
                                        @endforeach
                                    </ul>
                                @else
                                    <p class="text-sm text-gray-500">Składniki niedostępne</p>
                                @endif

                                <!-- Quick Stats -->
                                <div class="mt-4 text-xs text-gray-600">
                                    @if(isset($recipeData['readyInMinutes']))
                                        <div class="flex items-center gap-1 mb-1">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                            {{ $recipeData['readyInMinutes'] }} min
                                        </div>
                                    @endif
                                    @if(isset($recipeData['servings']))
                                        <div class="flex items-center gap-1">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                            </svg>
                                            {{ $recipeData['servings'] }} porcji
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <!-- Instructions Section -->
                            <div class="steps">
                                <h4 class="font-semibold text-gray-700 mb-2">Sposób przygotowania:</h4>
                                @if(isset($recipeData['instructions_pl']) && !empty($recipeData['instructions_pl']))
                                    @php
                                        $instructionsText = is_array($recipeData['instructions_pl'])
                                            ? implode("\n\n", $recipeData['instructions_pl'])
                                            : $recipeData['instructions_pl'];
                                    @endphp
                                    <p class="text-sm text-gray-600 whitespace-pre-line">{{ $instructionsText }}</p>
                                @elseif(isset($recipeData['instructions']) && !empty($recipeData['instructions']))
                                    @php
                                        $instructionsText = is_array($recipeData['instructions'])
                                            ? implode("\n\n", $recipeData['instructions'])
                                            : $recipeData['instructions'];
                                    @endphp
                                    <p class="text-sm text-gray-600 whitespace-pre-line">{{ $instructionsText }}</p>
                                @else
                                    <p class="text-sm text-gray-500 mb-3">Instrukcje niedostępne w API</p>
                                @endif

                                <!-- View Full Recipe Link -->
                                @if(isset($recipeData['sourceUrl']))
                                    <a href="{{ $recipeData['sourceUrl'] }}" target="_blank" class="inline-flex items-center text-sm font-medium mt-2 text-fit-green-600 hover:text-fit-green-700 no-underline">
                                        Zobacz pełny przepis
                                        <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                        </svg>
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Actions -->
            <div class="mt-8 grid grid-cols-1 md:grid-cols-2 gap-4">
                <a href="{{ route('meal-plans.index') }}" class="btn-fit-secondary text-center no-underline">
                    Powrót do wszystkich planów
                </a>
                <button onclick="window.print()" class="btn-fit-primary">
                    Drukuj menu
                </button>
            </div>

        </div> <!-- End fit-card -->
    </div> <!-- End container-wide -->
</div>
@endsection
