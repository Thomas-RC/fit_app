<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Disable caching in development -->
    @if(config('app.env') === 'local')
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    @endif

    <title>{{ config('app.name', 'FIT AI') }} - AI-Powered Meal Planning</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="bg-gray-50 font-sans antialiased">
    <!-- Navigation -->
    <nav class="bg-white shadow-sm border-b border-gray-200 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <a href="{{ route('home') }}" class="text-2xl font-bold text-emerald-600">
                        🍽️ FIT AI
                    </a>
                </div>
                @if (Route::has('login'))
                    <div class="flex items-center gap-4">
                        @auth
                            <a href="{{ url('/dashboard') }}" class="text-gray-700 hover:text-emerald-600 font-medium transition">
                                Dashboard
                            </a>
                        @else
                            <a href="{{ route('login') }}" class="inline-flex items-center px-6 py-2 bg-gradient-to-r from-emerald-500 to-teal-600 text-white rounded-md hover:shadow-lg transition font-semibold">
                                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                                    <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                                    <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                                    <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                                </svg>
                                Sign in with Google
                            </a>
                        @endauth
                    </div>
                @endif
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="relative bg-gradient-to-br from-emerald-50 via-teal-50 to-blue-50 py-20 sm:py-32">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="lg:grid lg:grid-cols-2 lg:gap-12 items-center">
                <!-- Left Column: Text -->
                <div class="text-center lg:text-left">
                    <h1 class="text-4xl sm:text-5xl lg:text-6xl font-bold text-gray-900 mb-6">
                        Your AI-Powered
                        <span class="bg-gradient-to-r from-emerald-600 to-teal-600 bg-clip-text text-transparent">
                            Meal Planning
                        </span>
                        Assistant
                    </h1>
                    <p class="text-xl text-gray-600 mb-8 max-w-2xl mx-auto lg:mx-0">
                        Scan your fridge with AI, get personalized meal plans, and discover recipes that match your dietary preferences. Say goodbye to food waste and hello to effortless meal planning.
                    </p>
                    <div class="flex flex-col sm:flex-row gap-4 justify-center lg:justify-start">
                        @guest
                            <a href="{{ route('login') }}" class="inline-flex items-center justify-center px-8 py-4 bg-gradient-to-r from-emerald-500 to-teal-600 text-white rounded-lg hover:shadow-xl transition font-semibold text-lg">
                                <svg class="w-6 h-6 mr-2" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                                    <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                                    <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                                    <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                                </svg>
                                Get Started Free
                            </a>
                        @else
                            <a href="{{ route('dashboard') }}" class="inline-flex items-center justify-center px-8 py-4 bg-gradient-to-r from-emerald-500 to-teal-600 text-white rounded-lg hover:shadow-xl transition font-semibold text-lg">
                                Go to Dashboard
                            </a>
                        @endguest
                    </div>
                </div>

                <!-- Right Column: Visual -->
                <div class="mt-12 lg:mt-0">
                    <div class="relative">
                        <div class="absolute inset-0 bg-gradient-to-r from-emerald-400 to-teal-500 rounded-3xl transform rotate-3"></div>
                        <div class="relative bg-white rounded-3xl shadow-2xl p-8">
                            <div class="space-y-4">
                                <div class="flex items-center gap-4 bg-emerald-50 rounded-lg p-4">
                                    <div class="w-12 h-12 bg-emerald-500 rounded-full flex items-center justify-center text-2xl">📸</div>
                                    <div>
                                        <h3 class="font-semibold text-gray-900">AI Fridge Scanner</h3>
                                        <p class="text-sm text-gray-600">Identify ingredients instantly</p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-4 bg-teal-50 rounded-lg p-4">
                                    <div class="w-12 h-12 bg-teal-500 rounded-full flex items-center justify-center text-2xl">🍽️</div>
                                    <div>
                                        <h3 class="font-semibold text-gray-900">Smart Meal Plans</h3>
                                        <p class="text-sm text-gray-600">Personalized to your diet</p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-4 bg-blue-50 rounded-lg p-4">
                                    <div class="w-12 h-12 bg-blue-500 rounded-full flex items-center justify-center text-2xl">📊</div>
                                    <div>
                                        <h3 class="font-semibold text-gray-900">Nutrition Tracking</h3>
                                        <p class="text-sm text-gray-600">Meet your calorie goals</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="py-20 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl sm:text-4xl font-bold text-gray-900 mb-4">
                    Everything You Need for Effortless Meal Planning
                </h2>
                <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                    Powered by cutting-edge AI technology to make your meal planning smarter and faster
                </p>
            </div>

            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
                <!-- Feature 1 -->
                <div class="bg-gradient-to-br from-emerald-50 to-teal-50 rounded-2xl p-8 hover:shadow-lg transition">
                    <div class="w-16 h-16 bg-gradient-to-r from-emerald-500 to-teal-600 rounded-xl flex items-center justify-center text-3xl mb-4">
                        📸
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">AI Fridge Scanning</h3>
                    <p class="text-gray-600">
                        Take a photo of your fridge and let our Gemini Vision AI identify all your ingredients automatically. No more manual entry.
                    </p>
                </div>

                <!-- Feature 2 -->
                <div class="bg-gradient-to-br from-teal-50 to-blue-50 rounded-2xl p-8 hover:shadow-lg transition">
                    <div class="w-16 h-16 bg-gradient-to-r from-teal-500 to-blue-600 rounded-xl flex items-center justify-center text-3xl mb-4">
                        ✨
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Personalized Meal Plans</h3>
                    <p class="text-gray-600">
                        Generate daily meal plans based on your fridge contents, dietary preferences, and calorie goals. Breakfast, lunch, and dinner sorted.
                    </p>
                </div>

                <!-- Feature 3 -->
                <div class="bg-gradient-to-br from-blue-50 to-purple-50 rounded-2xl p-8 hover:shadow-lg transition">
                    <div class="w-16 h-16 bg-gradient-to-r from-blue-500 to-purple-600 rounded-xl flex items-center justify-center text-3xl mb-4">
                        🔍
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Recipe Discovery</h3>
                    <p class="text-gray-600">
                        Browse thousands of recipes with advanced filters. Search by cuisine, diet type, max calories, and see what you already have in your fridge.
                    </p>
                </div>

                <!-- Feature 4 -->
                <div class="bg-gradient-to-br from-purple-50 to-pink-50 rounded-2xl p-8 hover:shadow-lg transition">
                    <div class="w-16 h-16 bg-gradient-to-r from-purple-500 to-pink-600 rounded-xl flex items-center justify-center text-3xl mb-4">
                        🥗
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Diet Preferences</h3>
                    <p class="text-gray-600">
                        Support for vegetarian, vegan, keto, and omnivore diets. Set your allergies and excluded ingredients for perfectly matched recipes.
                    </p>
                </div>

                <!-- Feature 5 -->
                <div class="bg-gradient-to-br from-pink-50 to-red-50 rounded-2xl p-8 hover:shadow-lg transition">
                    <div class="w-16 h-16 bg-gradient-to-r from-pink-500 to-red-600 rounded-xl flex items-center justify-center text-3xl mb-4">
                        📊
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Nutrition Insights</h3>
                    <p class="text-gray-600">
                        Track calories, protein, carbs, and fats for every recipe and meal plan. Meet your daily nutritional goals with ease.
                    </p>
                </div>

                <!-- Feature 6 -->
                <div class="bg-gradient-to-br from-red-50 to-orange-50 rounded-2xl p-8 hover:shadow-lg transition">
                    <div class="w-16 h-16 bg-gradient-to-r from-red-500 to-orange-600 rounded-xl flex items-center justify-center text-3xl mb-4">
                        ⏰
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Expiration Tracking</h3>
                    <p class="text-gray-600">
                        Never waste food again. Track expiration dates for your fridge items and get notified when ingredients are about to expire.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- How It Works -->
    <section class="py-20 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl sm:text-4xl font-bold text-gray-900 mb-4">
                    How It Works
                </h2>
                <p class="text-xl text-gray-600">
                    Get started in 3 simple steps
                </p>
            </div>

            <div class="grid md:grid-cols-3 gap-8">
                <!-- Step 1 -->
                <div class="text-center">
                    <div class="relative inline-block mb-6">
                        <div class="w-24 h-24 bg-gradient-to-r from-emerald-500 to-teal-600 rounded-full flex items-center justify-center text-white text-4xl font-bold shadow-xl">
                            1
                        </div>
                        <div class="absolute -top-2 -right-2 w-8 h-8 bg-yellow-400 rounded-full flex items-center justify-center text-xl">
                            📸
                        </div>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Scan Your Fridge</h3>
                    <p class="text-gray-600">
                        Sign in with Google and take a photo of your fridge. Our AI will identify all your ingredients in seconds.
                    </p>
                </div>

                <!-- Step 2 -->
                <div class="text-center">
                    <div class="relative inline-block mb-6">
                        <div class="w-24 h-24 bg-gradient-to-r from-teal-500 to-blue-600 rounded-full flex items-center justify-center text-white text-4xl font-bold shadow-xl">
                            2
                        </div>
                        <div class="absolute -top-2 -right-2 w-8 h-8 bg-yellow-400 rounded-full flex items-center justify-center text-xl">
                            ⚙️
                        </div>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Set Your Preferences</h3>
                    <p class="text-gray-600">
                        Choose your diet type, daily calorie goals, and any allergies or food exclusions.
                    </p>
                </div>

                <!-- Step 3 -->
                <div class="text-center">
                    <div class="relative inline-block mb-6">
                        <div class="w-24 h-24 bg-gradient-to-r from-blue-500 to-purple-600 rounded-full flex items-center justify-center text-white text-4xl font-bold shadow-xl">
                            3
                        </div>
                        <div class="absolute -top-2 -right-2 w-8 h-8 bg-yellow-400 rounded-full flex items-center justify-center text-xl">
                            ✨
                        </div>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Generate Meal Plans</h3>
                    <p class="text-gray-600">
                        Click generate and get a personalized meal plan for the day. Browse recipes, track nutrition, and enjoy cooking!
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="py-20 bg-gradient-to-r from-emerald-600 to-teal-600">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-3xl sm:text-4xl font-bold text-white mb-4">
                Ready to Transform Your Meal Planning?
            </h2>
            <p class="text-xl text-emerald-50 mb-8">
                Join thousands of users who are eating healthier and wasting less food with FIT AI
            </p>
            @guest
                <a href="{{ route('login') }}" class="inline-flex items-center px-8 py-4 bg-white text-emerald-600 rounded-lg hover:bg-gray-50 transition font-semibold text-lg shadow-xl">
                    <svg class="w-6 h-6 mr-2" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                        <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                        <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                        <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                    </svg>
                    Start Free with Google
                </a>
            @else
                <a href="{{ route('dashboard') }}" class="inline-flex items-center px-8 py-4 bg-white text-emerald-600 rounded-lg hover:bg-gray-50 transition font-semibold text-lg shadow-xl">
                    Go to Your Dashboard
                </a>
            @endguest
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-gray-900 text-gray-400 py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid md:grid-cols-3 gap-8">
                <div>
                    <div class="text-2xl font-bold text-white mb-4">🍽️ FIT AI</div>
                    <p class="text-sm">
                        AI-powered meal planning to help you eat healthier and waste less food.
                    </p>
                </div>
                <div>
                    <h3 class="text-white font-semibold mb-4">Technology</h3>
                    <ul class="space-y-2 text-sm">
                        <li>Powered by Google Gemini Vision AI</li>
                        <li>Spoonacular Recipe API</li>
                        <li>Laravel 11 Framework</li>
                    </ul>
                </div>
                <div>
                    <h3 class="text-white font-semibold mb-4">Features</h3>
                    <ul class="space-y-2 text-sm">
                        <li>AI Fridge Scanning</li>
                        <li>Personalized Meal Plans</li>
                        <li>Recipe Discovery</li>
                        <li>Nutrition Tracking</li>
                    </ul>
                </div>
            </div>
            <div class="border-t border-gray-800 mt-8 pt-8 text-center text-sm">
                <p>&copy; {{ date('Y') }} FIT AI. Powered by AI for smarter meal planning.</p>
                <p class="mt-2 text-xs">Laravel v{{ Illuminate\Foundation\Application::VERSION }} | PHP v{{ PHP_VERSION }}</p>
            </div>
        </div>
    </footer>
</body>
</html>
