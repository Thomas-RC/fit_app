@extends('layouts.app')

@section('title', 'Browse Recipes')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="lg:flex lg:gap-8">
            <!-- Sidebar Filters -->
            <div class="lg:w-1/4 mb-8 lg:mb-0">
                <div class="bg-white rounded-lg shadow-lg p-6 sticky top-4">
                    <h2 class="text-xl font-bold text-gray-900 mb-6">Filters</h2>

                    <form method="GET" action="{{ route('recipes.index') }}" class="space-y-6">
                        <!-- Search Query -->
                        <div>
                            <label for="query" class="block text-sm font-medium text-gray-700 mb-2">
                                Search
                            </label>
                            <input
                                type="text"
                                name="query"
                                id="query"
                                value="{{ $validated['query'] ?? '' }}"
                                placeholder="e.g., pasta, chicken..."
                                class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                            >
                        </div>

                        <!-- Diet Type -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Diet
                            </label>
                            <div class="space-y-2">
                                @php
                                    $diets = [
                                        '' => 'Any',
                                        'vegetarian' => 'Vegetarian',
                                        'vegan' => 'Vegan',
                                        'ketogenic' => 'Keto',
                                        'gluten free' => 'Gluten Free',
                                        'paleo' => 'Paleo',
                                    ];
                                @endphp
                                @foreach($diets as $value => $label)
                                    <label class="flex items-center">
                                        <input
                                            type="radio"
                                            name="diet"
                                            value="{{ $value }}"
                                            {{ ($validated['diet'] ?? '') === $value ? 'checked' : '' }}
                                            class="text-emerald-600 focus:ring-emerald-500"
                                        >
                                        <span class="ml-2 text-sm text-gray-700">{{ $label }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        <!-- Max Calories -->
                        <div>
                            <label for="maxCalories" class="block text-sm font-medium text-gray-700 mb-2">
                                Max Calories
                            </label>
                            <input
                                type="number"
                                name="maxCalories"
                                id="maxCalories"
                                value="{{ $validated['maxCalories'] ?? '' }}"
                                placeholder="e.g., 500"
                                min="100"
                                max="2000"
                                step="50"
                                class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                            >
                        </div>

                        <!-- Cuisine -->
                        <div>
                            <label for="cuisine" class="block text-sm font-medium text-gray-700 mb-2">
                                Cuisine
                            </label>
                            <select
                                name="cuisine"
                                id="cuisine"
                                class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                            >
                                <option value="">Any Cuisine</option>
                                @php
                                    $cuisines = ['italian', 'chinese', 'mexican', 'indian', 'american', 'japanese', 'thai', 'french', 'mediterranean', 'greek'];
                                @endphp
                                @foreach($cuisines as $cuisine)
                                    <option value="{{ $cuisine }}" {{ ($validated['cuisine'] ?? '') === $cuisine ? 'selected' : '' }}>
                                        {{ ucfirst($cuisine) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Buttons -->
                        <div class="flex gap-2">
                            <button
                                type="submit"
                                class="flex-1 px-4 py-2 bg-gradient-to-r from-emerald-500 to-teal-600 text-white rounded-md hover:shadow-lg transition font-semibold text-sm"
                            >
                                Apply Filters
                            </button>
                            <a
                                href="{{ route('recipes.index') }}"
                                class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50 text-sm"
                            >
                                Clear
                            </a>
                        </div>
                    </form>

                    <!-- Quick Links -->
                    <div class="mt-6 pt-6 border-t">
                        <h3 class="text-sm font-semibold text-gray-700 mb-3">Quick Links</h3>
                        <div class="space-y-2">
                            <a href="{{ route('recipes.random') }}" class="block text-sm text-emerald-600 hover:text-emerald-700">
                                🎲 Random Recipes
                            </a>
                            <a href="{{ route('meal-plans.create') }}" class="block text-sm text-emerald-600 hover:text-emerald-700">
                                ✨ Generate Meal Plan
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="lg:w-3/4">
                <!-- Header -->
                <div class="mb-8">
                    <h1 class="text-3xl font-bold text-gray-900">Browse Recipes</h1>
                    <p class="text-gray-600 mt-2">
                        @if(isset($totalResults))
                            Found {{ number_format($totalResults) }} recipes
                        @else
                            Discover delicious recipes
                        @endif
                    </p>
                </div>

                @if(count($recipes) > 0)
                    <!-- Recipes Grid -->
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
                        @foreach($recipes as $recipe)
                            <a href="{{ route('recipes.show', $recipe['id']) }}" class="bg-white rounded-lg shadow hover:shadow-xl transition overflow-hidden group">
                                <!-- Image -->
                                <div class="relative h-48 overflow-hidden">
                                    @if(isset($recipe['image']))
                                        <img src="{{ $recipe['image'] }}" alt="{{ $recipe['title'] }}" class="w-full h-full object-cover group-hover:scale-110 transition duration-300">
                                    @else
                                        <div class="w-full h-full bg-gradient-to-br from-emerald-100 to-teal-100 flex items-center justify-center">
                                            <span class="text-5xl">🍽️</span>
                                        </div>
                                    @endif

                                    <!-- Calories Badge -->
                                    @if(isset($recipe['nutrition']['nutrients']))
                                        @php
                                            $calories = collect($recipe['nutrition']['nutrients'])->firstWhere('name', 'Calories');
                                        @endphp
                                        @if($calories)
                                            <div class="absolute top-3 right-3 bg-white/90 backdrop-blur-sm rounded-full px-3 py-1 text-sm font-semibold text-emerald-600">
                                                {{ number_format($calories['amount']) }} kcal
                                            </div>
                                        @endif
                                    @endif
                                </div>

                                <!-- Content -->
                                <div class="p-4">
                                    <h3 class="font-semibold text-gray-900 mb-2 line-clamp-2 h-12">{{ $recipe['title'] }}</h3>

                                    <!-- Stats -->
                                    <div class="flex items-center gap-4 text-xs text-gray-500 mb-3">
                                        @if(isset($recipe['readyInMinutes']))
                                            <span class="flex items-center gap-1">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                </svg>
                                                {{ $recipe['readyInMinutes'] }} min
                                            </span>
                                        @endif
                                        @if(isset($recipe['servings']))
                                            <span class="flex items-center gap-1">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                                </svg>
                                                {{ $recipe['servings'] }}
                                            </span>
                                        @endif
                                    </div>

                                    <!-- Diet Tags -->
                                    @if(isset($recipe['diets']) && count($recipe['diets']) > 0)
                                        <div class="flex flex-wrap gap-1">
                                            @foreach(array_slice($recipe['diets'], 0, 2) as $diet)
                                                <span class="inline-block px-2 py-1 bg-emerald-50 text-emerald-700 rounded text-xs font-medium">
                                                    {{ ucfirst($diet) }}
                                                </span>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            </a>
                        @endforeach
                    </div>

                    <!-- Pagination -->
                    @if(isset($totalResults) && $totalResults > $perPage)
                        <div class="flex justify-center items-center gap-2">
                            @if($offset > 0)
                                <a href="{{ route('recipes.index', array_merge($validated, ['offset' => max(0, $offset - $perPage)])) }}" class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                                    Previous
                                </a>
                            @endif

                            <span class="px-4 py-2 text-gray-600">
                                Page {{ floor($offset / $perPage) + 1 }} of {{ ceil($totalResults / $perPage) }}
                            </span>

                            @if($offset + $perPage < $totalResults)
                                <a href="{{ route('recipes.index', array_merge($validated, ['offset' => $offset + $perPage])) }}" class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                                    Next
                                </a>
                            @endif
                        </div>
                    @endif
                @else
                    <!-- Empty State -->
                    <div class="bg-white rounded-lg shadow p-12 text-center">
                        <div class="text-6xl mb-4">🔍</div>
                        <h3 class="text-xl font-semibold text-gray-900 mb-2">No Recipes Found</h3>
                        <p class="text-gray-600 mb-6">Try adjusting your filters or search terms</p>
                        <a href="{{ route('recipes.index') }}" class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-emerald-500 to-teal-600 text-white rounded-md hover:shadow-lg transition font-semibold">
                            View All Recipes
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
