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

    <title>{{ config('app.name', 'FIT AI') }} - Planowanie posiłków oparte na AI</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'fit-green': {
                            50: '#f4fff6',
                            100: '#d4f8dd',
                            200: '#a9f1c0',
                            500: '#3cb371',
                            600: '#2d8f56',
                            700: '#1f6b3d',
                        }
                    }
                }
            }
        }
    </script>

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        [x-cloak] { display: none !important; }

        /* Fit Green Colors */
        :root {
            --fit-green-50: #f4fff6;
            --fit-green-100: #d4f8dd;
            --fit-green-200: #a9f1c0;
            --fit-green-500: #3cb371;
            --fit-green-600: #2d8f56;
            --fit-green-700: #1f6b3d;
        }

        .text-fit-green-50 { color: var(--fit-green-50) !important; }
        .text-fit-green-600 { color: var(--fit-green-600) !important; }
        .text-fit-green-700 { color: var(--fit-green-700) !important; }

        .bg-fit-green-50 { background-color: var(--fit-green-50) !important; }
        .bg-fit-green-500 { background-color: var(--fit-green-500) !important; }
        .bg-fit-green-600 { background-color: var(--fit-green-600) !important; }
        .bg-fit-green-700 { background-color: var(--fit-green-700) !important; }

        .from-fit-green-50 { --tw-gradient-from: var(--fit-green-50) !important; }
        .from-fit-green-500 { --tw-gradient-from: var(--fit-green-500) !important; }
        .from-fit-green-600 { --tw-gradient-from: var(--fit-green-600) !important; }

        .to-green-600 { --tw-gradient-to: #16a34a !important; }
        .to-green-500 { --tw-gradient-to: #22c55e !important; }

        .hover\:text-fit-green-600:hover { color: var(--fit-green-600); }
        .hover\:bg-fit-green-700:hover { background-color: var(--fit-green-700); }

        /* Buttons */
        .btn-fit-primary {
            display: inline-flex;
            align-items: center;
            padding: 0.5rem 1.5rem;
            background-color: var(--fit-green-600);
            color: white;
            border-radius: 0.375rem;
            font-weight: 600;
            transition: all 0.2s;
        }

        .btn-fit-primary:hover {
            background-color: var(--fit-green-700);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body class="bg-gray-50 font-sans antialiased">
    <!-- Navigation -->
    <nav class="bg-white shadow-sm border-b border-gray-200 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <a href="{{ route('home') }}" class="text-2xl font-bold text-fit-green-600">
                        🍽️ FIT AI
                    </a>
                </div>
                @if (Route::has('login'))
                    <div class="flex items-center gap-4">
                        @auth
                            <a href="{{ url('/dashboard') }}" class="text-gray-700 hover:text-fit-green-600 font-medium transition">
                                Panel
                            </a>
                        @else
                            <a href="{{ route('login') }}" class="btn-fit-primary">
                                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                                    <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                                    <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                                    <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                                </svg>
                                Zaloguj się przez Google
                            </a>
                        @endauth
                    </div>
                @endif
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="relative bg-gradient-to-br from-fit-green-50 via-green-50 to-emerald-50 py-20 sm:py-32">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="lg:grid lg:grid-cols-2 lg:gap-12 items-center">
                <!-- Left Column: Text -->
                <div class="text-center lg:text-left">
                    <h1 class="text-4xl sm:text-5xl lg:text-6xl font-bold text-gray-900 mb-6">
                        Twój asystent
                        <span class="bg-gradient-to-r from-fit-green-600 to-green-600 bg-clip-text text-transparent">
                            planowania posiłków
                        </span>
                        oparty na AI
                    </h1>
                    <p class="text-xl text-gray-600 mb-8 max-w-2xl mx-auto lg:mx-0">
                        Zeskanuj lodówkę za pomocą AI, otrzymuj spersonalizowane plany posiłków i odkrywaj przepisy dopasowane do twoich preferencji żywieniowych. Pożegnaj marnowanie jedzenia i powitaj bezproblemowe planowanie posiłków.
                    </p>
                    <div class="flex flex-col sm:flex-row gap-4 justify-center lg:justify-start">
                        @guest
                            <a href="{{ route('login') }}" class="inline-flex items-center justify-center px-8 py-4 bg-fit-green-600 text-white rounded-lg hover:bg-fit-green-700 hover:shadow-xl transition font-semibold text-lg">
                                <svg class="w-6 h-6 mr-2" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                                    <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                                    <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                                    <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                                </svg>
                                Rozpocznij za darmo
                            </a>
                        @else
                            <a href="{{ route('dashboard') }}" class="inline-flex items-center justify-center px-8 py-4 bg-fit-green-600 text-white rounded-lg hover:bg-fit-green-700 hover:shadow-xl transition font-semibold text-lg">
                                Przejdź do panelu
                            </a>
                        @endguest
                    </div>
                </div>

                <!-- Right Column: Visual -->
                <div class="mt-12 lg:mt-0">
                    <div class="relative">
                        <div class="absolute inset-0 bg-gradient-to-r from-fit-green-500 to-green-500 rounded-3xl transform rotate-3"></div>
                        <div class="relative bg-white rounded-3xl shadow-2xl p-8">
                            <div class="space-y-4">
                                <div class="flex items-center gap-4 bg-fit-green-50 rounded-lg p-4">
                                    <div class="w-12 h-12 bg-fit-green-500 rounded-full flex items-center justify-center text-2xl">📸</div>
                                    <div>
                                        <h3 class="font-semibold text-gray-900">Skaner lodówki AI</h3>
                                        <p class="text-sm text-gray-600">Rozpoznaj składniki natychmiast</p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-4 bg-green-50 rounded-lg p-4">
                                    <div class="w-12 h-12 bg-green-500 rounded-full flex items-center justify-center text-2xl">🍽️</div>
                                    <div>
                                        <h3 class="font-semibold text-gray-900">Inteligentne plany posiłków</h3>
                                        <p class="text-sm text-gray-600">Dopasowane do twojej diety</p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-4 bg-emerald-50 rounded-lg p-4">
                                    <div class="w-12 h-12 bg-emerald-500 rounded-full flex items-center justify-center text-2xl">📊</div>
                                    <div>
                                        <h3 class="font-semibold text-gray-900">Śledzenie wartości odżywczych</h3>
                                        <p class="text-sm text-gray-600">Osiągnij cele kaloryczne</p>
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
                    Wszystko, czego potrzebujesz do bezproblemowego planowania posiłków
                </h2>
                <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                    Zasilane najnowocześniejszą technologią AI, aby planowanie posiłków było mądrzejsze i szybsze
                </p>
            </div>

            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
                <!-- Feature 1 -->
                <div class="bg-gradient-to-br from-fit-green-50 to-green-50 rounded-2xl p-8 hover:shadow-lg transition">
                    <div class="w-16 h-16 bg-gradient-to-r from-fit-green-500 to-green-600 rounded-xl flex items-center justify-center text-3xl mb-4">
                        📸
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Skanowanie lodówki AI</h3>
                    <p class="text-gray-600">
                        Zrób zdjęcie lodówki, a nasze AI Gemini Vision automatycznie rozpozna wszystkie składniki. Koniec z ręcznym wprowadzaniem danych.
                    </p>
                </div>

                <!-- Feature 2 -->
                <div class="bg-gradient-to-br from-green-50 to-emerald-50 rounded-2xl p-8 hover:shadow-lg transition">
                    <div class="w-16 h-16 bg-gradient-to-r from-green-500 to-emerald-600 rounded-xl flex items-center justify-center text-3xl mb-4">
                        ✨
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Spersonalizowane plany posiłków</h3>
                    <p class="text-gray-600">
                        Generuj dzienne plany posiłków na podstawie zawartości lodówki, preferencji żywieniowych i celów kalorycznych. Śniadanie, obiad i kolacja uporządkowane.
                    </p>
                </div>

                <!-- Feature 3 -->
                <div class="bg-gradient-to-br from-emerald-50 to-fit-green-50 rounded-2xl p-8 hover:shadow-lg transition">
                    <div class="w-16 h-16 bg-gradient-to-r from-emerald-500 to-fit-green-600 rounded-xl flex items-center justify-center text-3xl mb-4">
                        🔍
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Odkrywanie przepisów</h3>
                    <p class="text-gray-600">
                        Przeglądaj tysiące przepisów z zaawansowanymi filtrami. Szukaj według kuchni, typu diety, maksymalnych kalorii i sprawdź, co już masz w lodówce.
                    </p>
                </div>

                <!-- Feature 4 -->
                <div class="bg-gradient-to-br from-fit-green-50 to-emerald-50 rounded-2xl p-8 hover:shadow-lg transition">
                    <div class="w-16 h-16 bg-gradient-to-r from-fit-green-500 to-emerald-600 rounded-xl flex items-center justify-center text-3xl mb-4">
                        🥗
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Preferencje żywieniowe</h3>
                    <p class="text-gray-600">
                        Wsparcie dla diet wegetariańskich, wegańskich, keto i wszystkożernych. Ustaw alergie i wykluczone składniki dla idealnie dopasowanych przepisów.
                    </p>
                </div>

                <!-- Feature 5 -->
                <div class="bg-gradient-to-br from-green-50 to-fit-green-50 rounded-2xl p-8 hover:shadow-lg transition">
                    <div class="w-16 h-16 bg-gradient-to-r from-green-500 to-fit-green-600 rounded-xl flex items-center justify-center text-3xl mb-4">
                        📊
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Analiza wartości odżywczych</h3>
                    <p class="text-gray-600">
                        Śledź kalorie, białko, węglowodany i tłuszcze dla każdego przepisu i planu posiłków. Z łatwością osiągaj dzienne cele żywieniowe.
                    </p>
                </div>

                <!-- Feature 6 -->
                <div class="bg-gradient-to-br from-emerald-50 to-green-50 rounded-2xl p-8 hover:shadow-lg transition">
                    <div class="w-16 h-16 bg-gradient-to-r from-emerald-500 to-green-600 rounded-xl flex items-center justify-center text-3xl mb-4">
                        ⏰
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Śledzenie dat ważności</h3>
                    <p class="text-gray-600">
                        Nigdy więcej nie marnuj jedzenia. Śledź daty ważności produktów w lodówce i otrzymuj powiadomienia, gdy składniki wkrótce przeterminują się.
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
                    Jak to działa
                </h2>
                <p class="text-xl text-gray-600">
                    Zacznij w 3 prostych krokach
                </p>
            </div>

            <div class="grid md:grid-cols-3 gap-8">
                <!-- Step 1 -->
                <div class="text-center">
                    <div class="relative inline-block mb-6">
                        <div class="w-24 h-24 bg-gradient-to-r from-fit-green-500 to-green-600 rounded-full flex items-center justify-center text-white text-4xl font-bold shadow-xl">
                            1
                        </div>
                        <div class="absolute -top-2 -right-2 w-8 h-8 bg-yellow-400 rounded-full flex items-center justify-center text-xl">
                            📸
                        </div>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Zeskanuj lodówkę</h3>
                    <p class="text-gray-600">
                        Zaloguj się przez Google i zrób zdjęcie lodówki. Nasze AI rozpozna wszystkie składniki w kilka sekund.
                    </p>
                </div>

                <!-- Step 2 -->
                <div class="text-center">
                    <div class="relative inline-block mb-6">
                        <div class="w-24 h-24 bg-gradient-to-r from-green-500 to-emerald-600 rounded-full flex items-center justify-center text-white text-4xl font-bold shadow-xl">
                            2
                        </div>
                        <div class="absolute -top-2 -right-2 w-8 h-8 bg-yellow-400 rounded-full flex items-center justify-center text-xl">
                            ⚙️
                        </div>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Ustaw preferencje</h3>
                    <p class="text-gray-600">
                        Wybierz typ diety, dzienne cele kaloryczne oraz wszelkie alergie lub wykluczenia żywieniowe.
                    </p>
                </div>

                <!-- Step 3 -->
                <div class="text-center">
                    <div class="relative inline-block mb-6">
                        <div class="w-24 h-24 bg-gradient-to-r from-emerald-500 to-fit-green-600 rounded-full flex items-center justify-center text-white text-4xl font-bold shadow-xl">
                            3
                        </div>
                        <div class="absolute -top-2 -right-2 w-8 h-8 bg-yellow-400 rounded-full flex items-center justify-center text-xl">
                            ✨
                        </div>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Generuj plany posiłków</h3>
                    <p class="text-gray-600">
                        Kliknij generuj i otrzymaj spersonalizowany plan posiłków na dany dzień. Przeglądaj przepisy, śledź wartości odżywcze i ciesz się gotowaniem!
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="py-20 bg-gradient-to-r from-fit-green-600 to-green-600">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-3xl sm:text-4xl font-bold text-white mb-4">
                Gotowy przekształcić planowanie posiłków?
            </h2>
            <p class="text-xl text-fit-green-50 mb-8">
                Dołącz do tysięcy użytkowników, którzy jedzą zdrowiej i marnują mniej jedzenia dzięki FIT AI
            </p>
            @guest
                <a href="{{ route('login') }}" class="inline-flex items-center px-8 py-4 bg-white text-fit-green-600 rounded-lg hover:bg-gray-50 transition font-semibold text-lg shadow-xl">
                    <svg class="w-6 h-6 mr-2" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                        <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                        <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                        <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                    </svg>
                    Rozpocznij za darmo przez Google
                </a>
            @else
                <a href="{{ route('dashboard') }}" class="inline-flex items-center px-8 py-4 bg-white text-fit-green-600 rounded-lg hover:bg-gray-50 transition font-semibold text-lg shadow-xl">
                    Przejdź do panelu
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
                        Planowanie posiłków oparte na AI, aby pomóc ci jeść zdrowiej i marnować mniej jedzenia.
                    </p>
                </div>
                <div>
                    <h3 class="text-white font-semibold mb-4">Technologia</h3>
                    <ul class="space-y-2 text-sm">
                        <li>Zasilane przez Google Gemini Vision AI</li>
                        <li>Spoonacular Recipe API</li>
                        <li>Laravel 11 Framework</li>
                    </ul>
                </div>
                <div>
                    <h3 class="text-white font-semibold mb-4">Funkcje</h3>
                    <ul class="space-y-2 text-sm">
                        <li>Skanowanie lodówki AI</li>
                        <li>Spersonalizowane plany posiłków</li>
                        <li>Odkrywanie przepisów</li>
                        <li>Śledzenie wartości odżywczych</li>
                    </ul>
                </div>
            </div>
            <div class="border-t border-gray-800 mt-8 pt-8 text-center text-sm">
                <p>&copy; {{ date('Y') }} FIT AI. Zasilane przez AI dla mądrzejszego planowania posiłków.</p>
                <p class="mt-2 text-xs">Laravel v{{ Illuminate\Foundation\Application::VERSION }} | PHP v{{ PHP_VERSION }}</p>
            </div>
        </div>
    </footer>
</body>
</html>
