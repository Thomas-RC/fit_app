@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Welcome Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">Welcome back, {{ Auth::user()->name }}!</h1>
            <p class="text-gray-600 mt-2">Let's plan your next healthy meal</p>
        </div>

        <!-- Quick Actions -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <!-- Scan Fridge -->
            <div class="bg-gradient-to-br from-emerald-500 to-teal-600 rounded-lg shadow-lg p-6 text-white">
                <div class="text-4xl mb-4">📸</div>
                <h3 class="text-xl font-semibold mb-2">Scan Your Fridge</h3>
                <p class="text-emerald-100 mb-4">Take a photo and let AI identify your ingredients</p>
                <a href="{{ route('fridge.scan') }}" class="inline-block bg-white text-emerald-600 px-4 py-2 rounded-md font-medium hover:bg-emerald-50 transition">
                    Start Scan
                </a>
            </div>

            <!-- Generate Meal Plan -->
            <div class="bg-gradient-to-br from-blue-500 to-indigo-600 rounded-lg shadow-lg p-6 text-white">
                <div class="text-4xl mb-4">🍽️</div>
                <h3 class="text-xl font-semibold mb-2">Generate Meal Plan</h3>
                <p class="text-blue-100 mb-4">Get AI-powered meal suggestions based on your fridge</p>
                <a href="{{ route('meal-plans.generate') }}" class="inline-block bg-white text-blue-600 px-4 py-2 rounded-md font-medium hover:bg-blue-50 transition">
                    Generate Plan
                </a>
            </div>

            <!-- View Recipes -->
            <div class="bg-gradient-to-br from-purple-500 to-pink-600 rounded-lg shadow-lg p-6 text-white">
                <div class="text-4xl mb-4">📚</div>
                <h3 class="text-xl font-semibold mb-2">Browse Recipes</h3>
                <p class="text-purple-100 mb-4">Explore recipes from Spoonacular API</p>
                <a href="{{ route('recipes.index') }}" class="inline-block bg-white text-purple-600 px-4 py-2 rounded-md font-medium hover:bg-purple-50 transition">
                    Browse Recipes
                </a>
            </div>
        </div>

        <!-- Stats Grid -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow p-6">
                <div class="text-sm font-medium text-gray-600 mb-1">Fridge Items</div>
                <div class="text-3xl font-bold text-gray-900">{{ $fridgeItemsCount }}</div>
            </div>
            <div class="bg-white rounded-lg shadow p-6">
                <div class="text-sm font-medium text-gray-600 mb-1">Meal Plans</div>
                <div class="text-3xl font-bold text-gray-900">{{ $mealPlansCount }}</div>
            </div>
            <div class="bg-white rounded-lg shadow p-6">
                <div class="text-sm font-medium text-gray-600 mb-1">Custom Dishes</div>
                <div class="text-3xl font-bold text-gray-900">{{ $customDishesCount }}</div>
            </div>
            <div class="bg-white rounded-lg shadow p-6">
                <div class="text-sm font-medium text-gray-600 mb-1">Daily Calories</div>
                <div class="text-3xl font-bold text-gray-900">{{ $dailyCalories }}</div>
            </div>
        </div>

        <!-- Recent Meal Plans -->
        <div class="bg-white rounded-lg shadow">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-xl font-semibold text-gray-900">Recent Meal Plans</h2>
            </div>
            <div class="p-6">
                @if($recentMealPlans->isEmpty())
                    <div class="text-center py-8 text-gray-500">
                        <div class="text-5xl mb-4">🍽️</div>
                        <p>No meal plans yet. Start by generating your first plan!</p>
                        <a href="{{ route('meal-plans.generate') }}" class="inline-block mt-4 bg-emerald-600 text-white px-6 py-2 rounded-md hover:bg-emerald-700 transition">
                            Generate Meal Plan
                        </a>
                    </div>
                @else
                    <div class="space-y-4">
                        @foreach($recentMealPlans as $mealPlan)
                            <div class="border border-gray-200 rounded-lg p-4 hover:border-emerald-500 transition">
                                <div class="flex justify-between items-start mb-2">
                                    <div>
                                        <div class="text-sm text-gray-600">{{ $mealPlan->date->format('l, F j, Y') }}</div>
                                        <div class="text-lg font-semibold text-gray-900">{{ $mealPlan->total_calories }} kcal</div>
                                    </div>
                                    <a href="{{ route('meal-plans.show', $mealPlan) }}" class="text-emerald-600 hover:text-emerald-700 font-medium">
                                        View →
                                    </a>
                                </div>
                                <div class="flex gap-2 flex-wrap">
                                    @foreach($mealPlan->recipes as $recipe)
                                        <span class="inline-block bg-gray-100 px-3 py-1 rounded-full text-xs text-gray-700">
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
            <div class="mt-8 bg-amber-50 border border-amber-200 rounded-lg p-6">
                <h2 class="text-xl font-semibold text-amber-900 mb-4">⚠️ Items Expiring Soon</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($expiringItems as $item)
                        <div class="bg-white rounded-lg p-4 border border-amber-200">
                            <div class="font-medium text-gray-900">{{ $item->product_name }}</div>
                            <div class="text-sm text-gray-600">{{ $item->quantity }} {{ $item->unit }}</div>
                            <div class="text-xs text-amber-700 mt-2">Expires: {{ $item->expires_at->format('M j, Y') }}</div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
