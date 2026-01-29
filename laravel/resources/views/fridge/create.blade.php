@extends('layouts.app')

@section('title', 'Dodaj produkt')

@section('content')
<div class="py-12">
    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8" x-data="{
        saving: false,
        progress: 0,
        progressMessage: 'Zapisywanie...',
        error: null,

        animateProgress(target, duration) {
            const start = this.progress;
            const startTime = Date.now();

            const animate = () => {
                const elapsed = Date.now() - startTime;
                const progress = Math.min(elapsed / duration, 1);
                this.progress = start + (target - start) * progress;

                if (progress < 1) {
                    requestAnimationFrame(animate);
                }
            };

            animate();
        },

        async handleSubmit(event) {
            event.preventDefault();

            this.saving = true;
            this.progress = 0;
            this.error = null;

            const form = event.target;
            const formData = new FormData(form);

            try {
                this.progressMessage = 'Zapisywanie produktu...';
                this.animateProgress(30, 500);

                const response = await fetch(form.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                });

                this.progressMessage = 'Tłumaczenie nazwy...';
                this.animateProgress(50, 1000);

                await new Promise(resolve => setTimeout(resolve, 500));

                this.progressMessage = 'Pobieranie danych odżywczych...';
                this.animateProgress(80, 1500);

                await new Promise(resolve => setTimeout(resolve, 1000));

                this.progress = 100;
                this.progressMessage = 'Gotowe!';

                if (response.ok || response.redirected) {
                    setTimeout(() => {
                        window.location.href = '{{ route('fridge.index') }}';
                    }, 500);
                } else {
                    const data = await response.json();
                    this.error = data.message || 'Nie udało się zapisać produktu';
                    this.saving = false;
                }
            } catch (error) {
                console.error('Save error:', error);
                this.error = 'Nie udało się zapisać produktu: ' + error.message;
                this.saving = false;
            }
        }
    }">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">Dodaj produkt</h1>
            <p class="text-gray-600 mt-2">Ręcznie dodaj produkt do lodówki</p>
        </div>

        <!-- Error Message -->
        <div x-show="error" x-cloak class="mb-6 p-4 bg-red-50 border border-red-200 text-red-700 rounded-lg">
            <div class="flex items-start">
                <svg class="h-5 w-5 text-red-400 mt-0.5 mr-3" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                </svg>
                <span x-text="error"></span>
            </div>
        </div>

        <!-- Form -->
        <form action="{{ route('fridge.store') }}" method="POST" @submit="handleSubmit" class="bg-white rounded-lg shadow-lg p-8">
            @csrf

            <!-- Product Name -->
            <div class="mb-6">
                <label for="product_name" class="block text-sm font-medium text-gray-700 mb-2">
                    Nazwa produktu <span class="text-red-500">*</span>
                </label>
                <input
                    type="text"
                    name="product_name"
                    id="product_name"
                    value="{{ old('product_name') }}"
                    required
                    class="w-full border border-gray-300 rounded-md px-4 py-3 focus:ring-2 focus:ring-fit-green-500 focus:border-fit-green-500 @error('product_name') border-red-500 @enderror"
                    placeholder="np. Mleko, Jajka, Kurczak..."
                >
                @error('product_name')
                    <p class="text-red-600 text-sm mt-2">{{ $message }}</p>
                @enderror
            </div>

            <!-- Quantity & Unit -->
            <div class="grid grid-cols-2 gap-4 mb-6">
                <!-- Quantity -->
                <div>
                    <label for="quantity" class="block text-sm font-medium text-gray-700 mb-2">
                        Ilość
                    </label>
                    <input
                        type="number"
                        name="quantity"
                        id="quantity"
                        value="{{ old('quantity') }}"
                        step="0.01"
                        min="0"
                        max="9999.99"
                        class="w-full border border-gray-300 rounded-md px-4 py-3 focus:ring-2 focus:ring-fit-green-500 focus:border-fit-green-500 @error('quantity') border-red-500 @enderror"
                        placeholder="np. 1.5"
                    >
                    @error('quantity')
                        <p class="text-red-600 text-sm mt-2">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Unit -->
                <div>
                    <label for="unit" class="block text-sm font-medium text-gray-700 mb-2">
                        Jednostka
                    </label>
                    <select
                        name="unit"
                        id="unit"
                        class="w-full border border-gray-300 rounded-md px-4 py-3 focus:ring-2 focus:ring-fit-green-500 focus:border-fit-green-500 @error('unit') border-red-500 @enderror"
                    >
                        <option value="">Wybierz jednostkę...</option>
                        <option value="kg" {{ old('unit') === 'kg' ? 'selected' : '' }}>kg</option>
                        <option value="g" {{ old('unit') === 'g' ? 'selected' : '' }}>g</option>
                        <option value="L" {{ old('unit') === 'L' ? 'selected' : '' }}>L</option>
                        <option value="ml" {{ old('unit') === 'ml' ? 'selected' : '' }}>ml</option>
                        <option value="pieces" {{ old('unit') === 'pieces' ? 'selected' : '' }}>sztuki</option>
                        <option value="packs" {{ old('unit') === 'packs' ? 'selected' : '' }}>opakowania</option>
                    </select>
                    @error('unit')
                        <p class="text-red-600 text-sm mt-2">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Expiration Date -->
            <div class="mb-6">
                <label for="expires_at" class="block text-sm font-medium text-gray-700 mb-2">
                    Data ważności
                </label>
                <input
                    type="date"
                    name="expires_at"
                    id="expires_at"
                    value="{{ old('expires_at') }}"
                    min="{{ date('Y-m-d') }}"
                    class="w-full border border-gray-300 rounded-md px-4 py-3 focus:ring-2 focus:ring-fit-green-500 focus:border-fit-green-500 @error('expires_at') border-red-500 @enderror"
                >
                @error('expires_at')
                    <p class="text-red-600 text-sm mt-2">{{ $message }}</p>
                @enderror
                <p class="text-sm text-gray-500 mt-2">Opcjonalne - zostaw puste jeśli nieznana</p>
            </div>

            <!-- Info Box -->
            <div class="mb-8 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-blue-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-blue-700">
                            <strong>Wskazówka:</strong> Dane odżywcze zostaną automatycznie pobrane z Spoonacular API po zapisaniu produktu.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex gap-4">
                <a
                    href="{{ route('fridge.index') }}"
                    class="flex-1 px-6 py-3 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50 text-center font-medium"
                >
                    Anuluj
                </a>
                <button
                    type="submit"
                    :disabled="saving"
                    class="flex-1 px-6 py-3 bg-fit-green-500 text-white rounded-md hover:bg-fit-green-600 hover:shadow-lg transition font-semibold disabled:opacity-50 disabled:cursor-not-allowed"
                >
                    <span x-show="!saving">Dodaj do lodówki</span>
                    <span x-show="saving">Przetwarzanie...</span>
                </button>
            </div>
        </form>

        <!-- Progress Overlay -->
        <div x-show="saving" x-cloak class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white rounded-lg p-8 max-w-md w-full mx-4">
                <div class="text-center">
                    <!-- Spinner -->
                    <svg class="animate-spin h-16 w-16 mx-auto text-fit-green-600 mb-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>

                    <h3 class="text-xl font-bold text-gray-900 mb-3">Zapisywanie produktu...</h3>
                    <p class="text-gray-600 mb-6" x-text="progressMessage"></p>

                    <!-- Progress Bar -->
                    <div class="mb-4">
                        <div class="bg-gray-200 rounded-full h-4 overflow-hidden">
                            <div
                                class="bg-fit-green-600 h-full rounded-full transition-all duration-300"
                                :style="`width: ${progress}%`"
                            ></div>
                        </div>
                        <p class="text-sm text-gray-500 mt-2" x-text="`${Math.round(progress)}%`"></p>
                    </div>

                    <p class="text-sm text-gray-500">Pobieramy dane odżywcze z Spoonacular API...</p>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
[x-cloak] { display: none !important; }
</style>
@endsection
