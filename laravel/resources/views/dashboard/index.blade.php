@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="py-8">
    <div class="container-wide">
        <!-- Welcome Card -->
        <div class="fit-card p-8 mb-6">
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Witaj, {{ Auth::user()->name }}!</h1>
            <p class="text-gray-600">Zaplanuj swój kolejny zdrowy posiłek</p>
        </div>

        <!-- Quick Actions -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <!-- Scan Fridge -->
            <div class="fit-card p-6">
                <div class="text-4xl mb-4">📸</div>
                <h3 class="text-xl font-semibold mb-2 text-gray-900">Zeskanuj lodówkę</h3>
                <p class="text-gray-600 mb-4 text-sm">Zrób zdjęcie i pozwól AI zidentyfikować składniki</p>
                <a href="{{ route('fridge.scan') }}" class="btn-fit-primary inline-block text-center">
                    Rozpocznij skan
                </a>
            </div>

            <!-- Generate Meal Plan -->
            <div class="fit-card p-6">
                <div class="text-4xl mb-4">🍽️</div>
                <h3 class="text-xl font-semibold mb-2 text-gray-900">Generuj plan posiłków</h3>
                <p class="text-gray-600 mb-4 text-sm">Otrzymaj propozycje posiłków na podstawie zawartości lodówki</p>
                <a href="{{ route('meal-plans.generate') }}" class="btn-fit-primary inline-block text-center">
                    Generuj plan
                </a>
            </div>

            <!-- View Recipes -->
            <div class="fit-card p-6">
                <div class="text-4xl mb-4">📚</div>
                <h3 class="text-xl font-semibold mb-2 text-gray-900">Przeglądaj przepisy</h3>
                <p class="text-gray-600 mb-4 text-sm">Odkryj przepisy z API Spoonacular</p>
                <a href="{{ route('recipes.index') }}" class="btn-fit-primary inline-block text-center">
                    Przeglądaj
                </a>
            </div>
        </div>

        <!-- Stats Grid -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
            <div class="fit-card p-6 text-center">
                <div class="text-sm font-medium text-gray-600 mb-2 uppercase tracking-wide">Produkty</div>
                <div class="text-3xl font-bold text-fit-green-600">{{ $fridgeItemsCount }}</div>
            </div>
            <div class="fit-card p-6 text-center">
                <div class="text-sm font-medium text-gray-600 mb-2 uppercase tracking-wide">Plany</div>
                <div class="text-3xl font-bold text-fit-green-600">{{ $mealPlansCount }}</div>
            </div>
            <div class="fit-card p-6 text-center">
                <div class="text-sm font-medium text-gray-600 mb-2 uppercase tracking-wide">Dania</div>
                <div class="text-3xl font-bold text-fit-green-600">{{ $customDishesCount }}</div>
            </div>
            <div class="fit-card p-6 text-center">
                <div class="text-sm font-medium text-gray-600 mb-2 uppercase tracking-wide">Kalorie</div>
                <div class="text-3xl font-bold text-fit-green-600">{{ $dailyCalories }}</div>
            </div>
        </div>

        <!-- Recent Meal Plans -->
        <div class="fit-card">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-xl font-semibold text-gray-900">Ostatnie plany posiłków</h2>
            </div>
            <div class="p-6">
                @if($recentMealPlans->isEmpty())
                    <div class="text-center py-8 text-gray-500">
                        <div class="text-5xl mb-4">🍽️</div>
                        <p class="mb-4">Nie masz jeszcze planów posiłków. Wygeneruj swój pierwszy!</p>
                        <a href="{{ route('meal-plans.generate') }}" class="btn-fit-primary inline-block">
                            Generuj plan posiłków
                        </a>
                    </div>
                @else
                    <div class="space-y-4">
                        @foreach($recentMealPlans as $mealPlan)
                            <div class="border border-gray-200 rounded-xl p-4 hover:border-fit-green-500 transition">
                                <div class="flex justify-between items-start mb-2">
                                    <div>
                                        <div class="text-sm text-gray-600">{{ $mealPlan->date->format('l, j F Y') }}</div>
                                        <div class="text-lg font-semibold text-fit-green-600">{{ $mealPlan->total_calories }} kcal</div>
                                    </div>
                                    <a href="{{ route('meal-plans.show', $mealPlan) }}" class="text-fit-green-600 hover:text-fit-green-700 font-medium">
                                        Zobacz →
                                    </a>
                                </div>
                                <div class="flex gap-2 flex-wrap">
                                    @foreach($mealPlan->recipes as $recipe)
                                        <span class="ingredient-tag">
                                            {{ $recipe->meal_type }}: {{ $recipe->recipe_title }}
                                        </span>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        <!-- Expiring Items -->
        @if($expiringItems->isNotEmpty())
            <div class="mt-6 fit-card p-6 border-l-4 border-amber-500">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">⚠️ Produkty wkrótce przeterminowane</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($expiringItems as $item)
                        <div class="bg-amber-50 rounded-lg p-4 border border-amber-200">
                            <div class="font-medium text-gray-900">{{ $item->product_name }}</div>
                            <div class="text-sm text-gray-600">{{ $item->quantity }} {{ $item->unit }}</div>
                            <div class="text-xs text-amber-700 mt-2">Wygasa: {{ $item->expires_at->format('j M Y') }}</div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
