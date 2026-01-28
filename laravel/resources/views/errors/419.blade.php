<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sesja wygasła - FIT AI</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gradient-to-br from-emerald-50 via-teal-50 to-blue-50">
    <div class="min-h-screen flex items-center justify-center px-4">
        <div class="max-w-md w-full">
            <!-- Card -->
            <div class="bg-white rounded-2xl shadow-2xl p-8 text-center">
                <!-- Icon -->
                <div class="mb-6">
                    <div class="inline-flex items-center justify-center w-20 h-20 bg-amber-100 rounded-full">
                        <svg class="w-10 h-10 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </div>

                <!-- Heading -->
                <h1 class="text-2xl font-bold text-gray-900 mb-3">
                    Sesja wygasła
                </h1>

                <!-- Description -->
                <p class="text-gray-600 mb-6">
                    Twoja sesja wygasła lub strona została już przetworzona. Zwykle dzieje się to po wylogowaniu lub podczas odświeżania strony.
                </p>

                <!-- Buttons -->
                <div class="space-y-3">
                    @auth
                        <a href="{{ route('dashboard') }}" class="block w-full px-6 py-3 bg-gradient-to-r from-emerald-500 to-teal-600 text-white rounded-lg hover:shadow-lg transition font-semibold">
                            Przejdź do panelu
                        </a>
                    @else
                        <a href="{{ route('home') }}" class="block w-full px-6 py-3 bg-gradient-to-r from-emerald-500 to-teal-600 text-white rounded-lg hover:shadow-lg transition font-semibold">
                            Przejdź do strony głównej
                        </a>
                        <a href="{{ route('login') }}" class="block w-full px-6 py-3 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition font-semibold">
                            Zaloguj się
                        </a>
                    @endauth
                </div>

                <!-- Help text -->
                <p class="text-xs text-gray-500 mt-6">
                    Jeśli nadal występują problemy, spróbuj wyczyścić pamięć podręczną przeglądarki.
                </p>
            </div>

            <!-- Back to home link -->
            <div class="text-center mt-6">
                <a href="{{ route('home') }}" class="text-sm text-gray-600 hover:text-emerald-600 transition">
                    <svg class="inline w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    Powrót do strony głównej
                </a>
            </div>
        </div>
    </div>
</body>
</html>
