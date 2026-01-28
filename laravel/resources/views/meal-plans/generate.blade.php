@extends('layouts.app')

@section('title', 'Generuj plan posiłków')

@section('content')
<div class="py-12">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        <div x-data="{
            step: 1,
            date: '',
            generating: false,
            progress: 0,
            progressMessage: 'Inicjalizacja...',

            nextStep() {
                if (this.step === 1 && !this.date) {
                    showToast('Proszę wybrać datę', 'error', 4000);
                    return;
                }
                if (this.step < 3) {
                    this.step++;
                }
            },

            prevStep() {
                if (this.step > 1) {
                    this.step--;
                }
            },

            async generate() {
                this.generating = true;
                this.progress = 0;
                this.progressMessage = 'Analizowanie lodówki...';
                this.animateProgress(30, 2000);

                try {
                    const formData = new FormData();
                    formData.append('date', this.date);
                    formData.append('_token', '{{ csrf_token() }}');

                    this.progressMessage = 'Szukanie idealnych przepisów...';
                    this.animateProgress(60, 3000);

                    const response = await fetch('{{ route('meal-plans.generate') }}', {
                        method: 'POST',
                        body: formData
                    });

                    this.progressMessage = 'Obliczanie wartości odżywczych...';
                    this.animateProgress(90, 1000);

                    if (response.redirected) {
                        this.progress = 100;
                        this.progressMessage = 'Gotowe!';
                        window.location.href = response.url;
                    } else {
                        const text = await response.text();
                        console.error('Generation failed:', text);
                        showToast('Nie udało się wygenerować planu posiłków. Spróbuj ponownie.', 'error', 6000);
                        this.generating = false;
                    }
                } catch (error) {
                    console.error('Error:', error);
                    showToast('Wystąpił błąd podczas generowania planu: ' + error.message, 'error', 6000);
                    this.generating = false;
                }
            },

            animateProgress(target, duration) {
                const start = this.progress;
                const startTime = Date.now();

                const animate = () => {
                    const elapsed = Date.now() - startTime;
                    const progress = Math.min(elapsed / duration, 1);
                    this.progress = start + (target - start) * progress;

                    if (progress < 1 && this.generating) {
                        requestAnimationFrame(animate);
                    }
                };

                animate();
            }
        }">
            <!-- Header -->
            <div class="mb-8">
                <a href="{{ route('meal-plans.index') }}" class="text-fit-green-600 hover:text-fit-green-700 flex items-center gap-2 mb-4">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    Powrót do planów posiłków
                </a>
                <h1 class="text-3xl font-bold text-gray-900">Generuj plan posiłków</h1>
                <p class="text-gray-600 mt-2">Planowanie posiłków oparte na AI, dostosowane do twoich preferencji i zawartości lodówki</p>
            </div>

            <!-- Error Message -->
            @if(session('error'))
                <div class="mb-6 p-4 bg-red-50 border border-red-200 text-red-700 rounded-lg">
                    {{ session('error') }}
                </div>
            @endif

            <!-- Progress Steps -->
            <div class="mb-8">
                <div class="flex items-center justify-between">
                    <div class="flex-1 flex items-center">
                        <div class="flex items-center justify-center w-10 h-10 rounded-full text-white font-semibold"
                            :class="step >= 1 ? 'bg-fit-green-600' : 'bg-gray-300'">
                            1
                        </div>
                        <div class="flex-1 h-1 mx-2"
                            :class="step > 1 ? 'bg-fit-green-600' : 'bg-gray-300'">
                        </div>
                    </div>
                    <div class="flex-1 flex items-center">
                        <div class="flex items-center justify-center w-10 h-10 rounded-full text-white font-semibold"
                            :class="step >= 2 ? 'bg-fit-green-600' : 'bg-gray-300'">
                            2
                        </div>
                        <div class="flex-1 h-1 mx-2"
                            :class="step > 2 ? 'bg-fit-green-600' : 'bg-gray-300'">
                        </div>
                    </div>
                    <div class="flex items-center justify-center w-10 h-10 rounded-full text-white font-semibold"
                        :class="step >= 3 ? 'bg-fit-green-600' : 'bg-gray-300'">
                        3
                    </div>
                </div>
                <div class="flex justify-between mt-2 text-sm text-gray-600">
                    <span>Wybierz datę</span>
                    <span>Sprawdź ustawienia</span>
                    <span>Potwierdź</span>
                </div>
            </div>

            <!-- Step 1: Date Selection -->
            <div x-show="step === 1" class="fit-card p-8">
                <div class="text-center mb-8">
                    <div class="text-5xl mb-4">📅</div>
                    <h2 class="text-2xl font-bold text-gray-900 mb-2">Wybierz datę</h2>
                    <p class="text-gray-600">Wybierz datę dla swojego planu posiłków</p>
                </div>

                <div class="max-w-md mx-auto mb-8">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Data</label>
                    <input
                        type="date"
                        x-model="date"
                        min="{{ date('Y-m-d') }}"
                        class="w-full border border-gray-300 rounded-md px-4 py-3 focus:ring-2 focus:ring-fit-green-500 focus:border-fit-green-500 text-lg"
                        required
                    >
                    <p class="text-sm text-gray-500 mt-2" x-show="date">
                        Plan zostanie wygenerowany na <strong x-text="new Date(date + 'T00:00:00').toLocaleDateString('pl-PL', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' })"></strong>
                    </p>
                </div>

                <div class="flex justify-end">
                    <button
                        @click="nextStep"
                        class="btn-fit-primary"
                    >
                        Kontynuuj
                    </button>
                </div>
            </div>

            <!-- Step 2: Review Settings -->
            <div x-show="step === 2" x-cloak class="fit-card p-8">
                <div class="text-center mb-8">
                    <div class="text-5xl mb-4">⚙️</div>
                    <h2 class="text-2xl font-bold text-gray-900 mb-2">Sprawdź ustawienia</h2>
                    <p class="text-gray-600">Upewnij się, że wszystko wygląda dobrze</p>
                </div>

                <div class="space-y-4 mb-8">
                    <!-- Fridge Items -->
                    <div class="border border-gray-200 rounded-lg p-6">
                        <div class="flex items-center justify-between mb-2">
                            <h3 class="text-lg font-semibold text-gray-900">Zawartość lodówki</h3>
                            <a href="{{ route('fridge.index') }}" class="text-sm text-fit-green-600 hover:text-fit-green-700">Edytuj</a>
                        </div>
                        <div class="flex items-center gap-3">
                            <div class="text-3xl">🥗</div>
                            <div>
                                <div class="text-2xl font-bold text-gray-900">{{ $fridgeItemsCount }}</div>
                                <div class="text-sm text-gray-600">produktów w lodówce</div>
                            </div>
                        </div>
                        @if($fridgeItemsCount === 0)
                            <div class="mt-3 p-3 bg-amber-50 border border-amber-200 rounded-md">
                                <p class="text-sm text-amber-700">
                                    <strong>Wskazówka:</strong> Dodaj produkty do lodówki, aby otrzymać bardziej spersonalizowane sugestie posiłków!
                                </p>
                            </div>
                        @endif
                    </div>

                    <!-- Preferences -->
                    <div class="border border-gray-200 rounded-lg p-6">
                        <div class="flex items-center justify-between mb-2">
                            <h3 class="text-lg font-semibold text-gray-900">Preferencje żywieniowe</h3>
                            <a href="{{ route('preferences.show') }}" class="text-sm text-fit-green-600 hover:text-fit-green-700">Edytuj</a>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <div class="text-sm text-gray-600">Typ diety</div>
                                <div class="font-semibold text-gray-900 capitalize">{{ $preferences->diet_type ?? 'omnivore' }}</div>
                            </div>
                            <div>
                                <div class="text-sm text-gray-600">Dzienne kalorie</div>
                                <div class="font-semibold text-gray-900">{{ $preferences->daily_calories ?? 2000 }} kcal</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex gap-4">
                    <button
                        @click="prevStep"
                        class="flex-1 px-6 py-3 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50 font-medium"
                    >
                        Wstecz
                    </button>
                    <button
                        @click="nextStep"
                        class="flex-1 btn-fit-primary"
                    >
                        Kontynuuj
                    </button>
                </div>
            </div>

            <!-- Step 3: Confirmation -->
            <div x-show="step === 3 && !generating" x-cloak class="fit-card p-8">
                <div class="text-center mb-8">
                    <div class="text-6xl mb-4">✨</div>
                    <h2 class="text-2xl font-bold text-gray-900 mb-2">Gotowe do wygenerowania!</h2>
                    <p class="text-gray-600 mb-6">
                        Twój spersonalizowany plan posiłków zostanie stworzony przy użyciu technologii AI
                    </p>

                    <div class="grid grid-cols-3 gap-4 max-w-lg mx-auto mb-8">
                        <div class="text-center">
                            <div class="text-3xl mb-2">📸</div>
                            <div class="text-sm text-gray-600">Wykorzystuje zawartość lodówki</div>
                        </div>
                        <div class="text-center">
                            <div class="text-3xl mb-2">🎯</div>
                            <div class="text-sm text-gray-600">Dopasowany do twoich preferencji</div>
                        </div>
                        <div class="text-center">
                            <div class="text-3xl mb-2">📊</div>
                            <div class="text-sm text-gray-600">Spełnia cele kaloryczne</div>
                        </div>
                    </div>
                </div>

                <div class="flex gap-4">
                    <button
                        @click="prevStep"
                        class="flex-1 px-6 py-3 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50 font-medium"
                    >
                        Wstecz
                    </button>
                    <button
                        @click="generate"
                        class="flex-1 btn-fit-primary"
                    >
                        ✨ Generuj plan posiłków
                    </button>
                </div>
            </div>

            <!-- Generating State -->
            <div x-show="generating" x-cloak class="fit-card p-12">
                <div class="text-center">
                    <div class="mb-8">
                        <!-- Spinner -->
                        <svg class="animate-spin h-16 w-16 mx-auto text-fit-green-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </div>

                    <h2 class="text-2xl font-bold text-gray-900 mb-4">Generowanie planu posiłków...</h2>
                    <p class="text-gray-600 mb-8" x-text="progressMessage"></p>

                    <!-- Progress Bar -->
                    <div class="max-w-md mx-auto mb-4">
                        <div class="bg-gray-200 rounded-full h-4 overflow-hidden">
                            <div
                                class="bg-fit-green-600 h-full rounded-full transition-all duration-300"
                                :style="`width: ${progress}%`"
                            ></div>
                        </div>
                        <p class="text-sm text-gray-500 mt-2" x-text="`${Math.round(progress)}%`"></p>
                    </div>

                    <p class="text-sm text-gray-500">Zazwyczaj zajmuje to 10-15 sekund</p>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
[x-cloak] { display: none !important; }
</style>
@endsection
