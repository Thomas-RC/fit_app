<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Disable caching in development -->
    @if(config('app.env') === 'local')
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    @endif

    <title>{{ config('app.name', 'FIT AI') }} - @yield('title', 'AI Meal Planner')</title>

    <!-- Vite Assets (includes Tailwind CSS) -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- PWA -->
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#3cb371">
    <link rel="apple-touch-icon" href="/icons/icon-192x192.png">

    <style>
        /* Only keep styles not defined in app.css */
        [x-cloak] { display: none !important; }

        /* Gradient backgrounds */
        .bg-fit-gradient {
            background: linear-gradient(135deg, #6dd5a7, #3cb371);
        }

        /* Toast Notifications */
        .toast-enter {
            animation: slideInRight 0.3s ease-out;
        }
        .toast-exit {
            animation: slideOutRight 0.3s ease-in;
        }
        @keyframes slideInRight {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
        @keyframes slideOutRight {
            from {
                transform: translateX(0);
                opacity: 1;
            }
            to {
                transform: translateX(100%);
                opacity: 0;
            }
        }
    </style>

    @stack('styles')
</head>
<body class="font-sans antialiased bg-fit-gradient min-h-screen">
    <div class="min-h-screen">
        <!-- Navigation -->
        @auth
            <nav class="hidden md:block bg-white/95 backdrop-blur-sm shadow-md">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="flex justify-between h-16">
                        <div class="flex">
                            <!-- Logo -->
                            <div class="flex-shrink-0 flex items-center">
                                <a href="{{ route('dashboard') }}" class="text-2xl font-bold text-fit-green-500">
                                    🍽️ FIT AI
                                </a>
                            </div>

                            <!-- Navigation Links -->
                            <div class="hidden sm:ml-6 sm:flex sm:space-x-8">
                                <a href="{{ route('dashboard') }}" class="inline-flex items-center px-1 pt-1 border-b-2 {{ request()->routeIs('dashboard') ? 'text-gray-900 border-fit-green-500' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} text-sm font-medium">
                                    Panel
                                </a>
                                <a href="{{ route('fridge.index') }}" class="inline-flex items-center px-1 pt-1 border-b-2 {{ request()->routeIs('fridge.*') ? 'text-gray-900 border-fit-green-500' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} text-sm font-medium">
                                    Moja lodówka
                                </a>
                                <a href="{{ route('meal-plans.index') }}" class="inline-flex items-center px-1 pt-1 border-b-2 {{ request()->routeIs('meal-plans.*') ? 'text-gray-900 border-fit-green-500' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} text-sm font-medium">
                                    Plany posiłków
                                </a>
                                <a href="{{ route('preferences.show') }}" class="inline-flex items-center px-1 pt-1 border-b-2 {{ request()->routeIs('preferences.*') ? 'text-gray-900 border-fit-green-500' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} text-sm font-medium">
                                    Preferencje
                                </a>
                            </div>
                        </div>

                        <!-- User Menu -->
                        <div class="flex items-center gap-4">
                            <div class="ml-3 relative" x-data="{ open: false }">
                                <button @click="open = !open" class="flex items-center text-sm focus:outline-none">
                                    <img class="h-8 w-8 rounded-full" src="{{ Auth::user()->avatar }}" alt="{{ Auth::user()->name }}">
                                    <span class="ml-2 text-gray-700">{{ Auth::user()->name }}</span>
                                </button>

                                <div x-show="open" @click.away="open = false" x-cloak class="origin-top-right absolute right-0 mt-2 w-48 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 divide-y divide-gray-100">
                                    <div class="py-1">
                                        @php
                                            $adminEmails = config('app.admin_emails', []);
                                            $isAdmin = in_array(Auth::user()->email, $adminEmails);
                                        @endphp
                                        @if($isAdmin)
                                            <a href="{{ route('admin.settings') }}" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                                <svg class="w-4 h-4 mr-2 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                                </svg>
                                                Ustawienia administratora
                                            </a>
                                        @endif
                                    </div>
                                    <div class="py-1">
                                        <form method="POST" action="{{ route('logout') }}">
                                            @csrf
                                            <button type="submit" class="flex items-center w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                                </svg>
                                                Wyloguj
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </nav>

            <!-- Bottom Navigation (Mobile only) -->
            <x-navigation.bottom-nav />
        @endauth

        <!-- Page Content -->
        <main class="pb-16 md:pb-0 pb-safe">
            <!-- Flash Messages -->
            @if(session('success'))
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-6">
                    <div class="bg-emerald-50 border border-emerald-200 rounded-lg p-4 mb-4" x-data="{ show: true }" x-show="show" x-cloak>
                        <div class="flex items-start">
                            <svg class="w-5 h-5 text-emerald-600 mr-3 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <div class="flex-1">
                                <p class="text-sm font-medium text-emerald-800">{{ session('success') }}</p>
                            </div>
                            <button @click="show = false" class="ml-3 text-emerald-600 hover:text-emerald-700">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            @endif

            @if(session('error'))
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-6">
                    <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-4" x-data="{ show: true }" x-show="show" x-cloak>
                        <div class="flex items-start">
                            <svg class="w-5 h-5 text-red-600 mr-3 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <div class="flex-1">
                                <p class="text-sm font-medium text-red-800">{{ session('error') }}</p>
                            </div>
                            <button @click="show = false" class="ml-3 text-red-600 hover:text-red-700">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            @endif

            @yield('content')
        </main>
    </div>

    @stack('scripts')

    <!-- Toast Notifications Container -->
    <div x-data class="fixed top-4 right-4 z-50 space-y-3 max-w-sm w-full pointer-events-none px-4">
        <template x-for="toast in $store.toast.toasts" :key="toast.id">
            <div
                x-show="toast.show"
                x-transition:enter="toast-enter"
                x-transition:leave="toast-exit"
                :class="{
                    'bg-emerald-50 border-emerald-200': toast.type === 'success',
                    'bg-red-50 border-red-200': toast.type === 'error',
                    'bg-blue-50 border-blue-200': toast.type === 'info'
                }"
                class="pointer-events-auto border rounded-lg shadow-lg p-4 flex items-start gap-3"
            >
                <!-- Icon -->
                <div class="flex-shrink-0">
                    <svg x-show="toast.type === 'success'" class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <svg x-show="toast.type === 'error'" class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <svg x-show="toast.type === 'info'" class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>

                <!-- Message -->
                <div class="flex-1 min-w-0">
                    <p
                        x-text="toast.message"
                        :class="{
                            'text-emerald-800': toast.type === 'success',
                            'text-red-800': toast.type === 'error',
                            'text-blue-800': toast.type === 'info'
                        }"
                        class="text-sm font-medium break-words"
                    ></p>
                </div>

                <!-- Close Button -->
                <button
                    @click="$store.toast.remove(toast.id)"
                    :class="{
                        'text-emerald-600 hover:text-emerald-700': toast.type === 'success',
                        'text-red-600 hover:text-red-700': toast.type === 'error',
                        'text-blue-600 hover:text-blue-700': toast.type === 'info'
                    }"
                    class="flex-shrink-0 transition-colors"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </template>
    </div>

    <script>
        // Initialize Alpine Store for Toast Notifications
        document.addEventListener('alpine:init', () => {
            Alpine.store('toast', {
                toasts: [],
                nextId: 1,

                add(message, type = 'info', duration = 5000) {
                    const id = this.nextId++;
                    const toast = {
                        id: id,
                        message: message,
                        type: type,
                        show: true
                    };

                    this.toasts.push(toast);

                    // Auto remove after duration
                    setTimeout(() => {
                        this.remove(id);
                    }, duration);
                },

                remove(id) {
                    const index = this.toasts.findIndex(t => t.id === id);
                    if (index > -1) {
                        this.toasts[index].show = false;
                        // Remove from array after animation completes
                        setTimeout(() => {
                            this.toasts = this.toasts.filter(t => t.id !== id);
                        }, 300);
                    }
                }
            });
        });

        // Global function to show toasts from anywhere in the app
        window.showToast = function(message, type = 'info', duration = 5000) {
            if (Alpine.store('toast')) {
                Alpine.store('toast').add(message, type, duration);
            }
        };
    </script>

    <!-- Project Disclaimer Banner -->
    <div x-data="{ show: !localStorage.getItem('disclaimerClosed') }" x-show="show" x-cloak
         class="fixed bottom-0 left-0 right-0 bg-amber-500 text-white py-3 px-4 shadow-lg z-50">
        <div class="max-w-7xl mx-auto flex items-center justify-between flex-wrap gap-2">
            <p class="text-sm font-medium">
                Strona nie jest prawdziwa aplikacja, to projekt zaliczeniowy na potrzebe pracy projektowej "Zastosowanie metod sztucznej inteligencji"
            </p>
            <button @click="localStorage.setItem('disclaimerClosed', 'true'); show = false"
                    class="bg-white text-amber-600 px-4 py-1 rounded text-sm font-medium hover:bg-amber-50 transition-colors">
                Rozumiem
            </button>
        </div>
    </div>

    <!-- Service Worker Registration -->
    @if(config('app.env') !== 'local')
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/service-worker.js')
                    .then((registration) => {
                        console.log('Service Worker registered:', registration.scope);
                    })
                    .catch((error) => {
                        console.log('Service Worker registration failed:', error);
                    });
            });
        }
    </script>
    @else
    <script>
        // Unregister service worker in development
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.getRegistrations().then(function(registrations) {
                for(let registration of registrations) {
                    registration.unregister();
                    console.log('Service Worker unregistered');
                }
            });
        }
    </script>
    @endif
</body>
</html>
