@extends('layouts.app')

@section('title', 'Moje plany posiłków')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="flex justify-between items-center mb-8">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Moje plany posiłków</h1>
                <p class="text-gray-600 mt-2">Przeglądaj i zarządzaj planami posiłków wygenerowanymi przez AI</p>
            </div>
            <a href="{{ route('meal-plans.create') }}" class="btn-fit-primary">
                ✨ Generuj nowy plan
            </a>
        </div>

        <!-- Success Message -->
        @if(session('success'))
            <div class="mb-6 p-4 bg-fit-green-50 border border-fit-green-200 text-fit-green-700 rounded-lg">
                {{ session('success') }}
            </div>
        @endif

        @if($mealPlans->count() > 0)
            <!-- Meal Plans Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
                @foreach($mealPlans as $plan)
                    <div class="fit-card hover:shadow-2xl transition overflow-hidden">
                        <!-- Header -->
                        <div class="bg-gradient-to-r from-fit-green-500 to-green-600 p-6 text-white">
                            <div class="flex justify-between items-start mb-2">
                                <div>
                                    <div class="text-sm opacity-90">{{ $plan->date->format('l') }}</div>
                                    <div class="text-2xl font-bold">{{ $plan->date->format('j M Y') }}</div>
                                </div>
                                <div class="bg-white/20 backdrop-blur-sm rounded-lg px-3 py-2">
                                    <div class="text-xs opacity-90">Suma</div>
                                    <div class="text-lg font-bold">{{ number_format($plan->total_calories) }} kcal</div>
                                </div>
                            </div>
                        </div>

                        <!-- Meals Preview -->
                        <div class="p-6">
                            <div class="space-y-3 mb-4">
                                @foreach($plan->recipes->take(3) as $recipe)
                                    <div class="flex items-center gap-3">
                                        <div class="flex-shrink-0">
                                            @if($recipe->meal_type === 'breakfast')
                                                <span class="inline-flex items-center justify-center w-8 h-8 bg-amber-100 text-amber-600 rounded-full text-sm">🌅</span>
                                            @elseif($recipe->meal_type === 'lunch')
                                                <span class="inline-flex items-center justify-center w-8 h-8 bg-blue-100 text-blue-600 rounded-full text-sm">☀️</span>
                                            @elseif($recipe->meal_type === 'dinner')
                                                <span class="inline-flex items-center justify-center w-8 h-8 bg-indigo-100 text-indigo-600 rounded-full text-sm">🌙</span>
                                            @else
                                                <span class="inline-flex items-center justify-center w-8 h-8 bg-gray-100 text-gray-600 rounded-full text-sm">🍴</span>
                                            @endif
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <div class="text-sm font-medium text-gray-900 truncate">{{ $recipe->recipe_title }}</div>
                                            <div class="text-xs text-gray-500">{{ number_format($recipe->calories) }} kcal</div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <!-- Stats -->
                            <div class="flex items-center justify-between text-sm text-gray-600 mb-4 pt-4 border-t">
                                <span>{{ $plan->recipes->count() }} {{ $plan->recipes->count() === 1 ? 'posiłek' : ($plan->recipes->count() < 5 ? 'posiłki' : 'posiłków') }}</span>
                                <span>Utworzono {{ $plan->created_at->diffForHumans() }}</span>
                            </div>

                            <!-- Action -->
                            <a href="{{ route('meal-plans.show', $plan) }}" class="block w-full text-center px-4 py-2 bg-fit-green-50 text-fit-green-700 rounded-md hover:bg-fit-green-100 transition font-medium">
                                Zobacz szczegóły
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Pagination -->
            <div class="mt-8">
                {{ $mealPlans->links() }}
            </div>
        @else
            <!-- Empty State -->
            <div class="fit-card p-12 text-center">
                <div class="text-6xl mb-4">🍽️</div>
                <h3 class="text-xl font-semibold text-gray-900 mb-2">Nie masz jeszcze planów posiłków</h3>
                <p class="text-gray-600 mb-6">Wygeneruj swój pierwszy plan posiłków oparty na AI, dostosowany do twoich preferencji i zawartości lodówki</p>
                <a href="{{ route('meal-plans.create') }}" class="btn-fit-primary">
                    ✨ Generuj swój pierwszy plan
                </a>
            </div>
        @endif
    </div>
</div>
@endsection
