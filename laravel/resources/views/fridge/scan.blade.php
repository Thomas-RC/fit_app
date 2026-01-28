@extends('layouts.app')

@section('title', 'Skanuj lodówkę')

@section('content')
<div class="py-12">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div x-data="{
            state: 'upload', // upload, preview, analyzing, results
            file: null,
            preview: null,
            analyzing: false,
            progress: 0,
            progressMessage: 'Przesyłanie...',
            results: [],
            error: null,

            handleFileSelect(event) {
                const file = event.target.files[0];
                if (!file) return;

                this.error = null;

                // Validate file size (max 5MB)
                if (file.size > 5 * 1024 * 1024) {
                    this.error = 'Rozmiar pliku musi być mniejszy niż 5MB';
                    return;
                }

                // Validate file type
                if (!file.type.match('image/(jpg|jpeg|png|webp)')) {
                    this.error = 'Plik musi być w formacie JPG, PNG lub WEBP';
                    return;
                }

                this.file = file;
                this.state = 'preview';

                // Create preview
                const reader = new FileReader();
                reader.onload = (e) => {
                    this.preview = e.target.result;
                };
                reader.readAsDataURL(file);
            },

            async analyzeImage() {
                if (!this.file) return;

                this.state = 'analyzing';
                this.analyzing = true;
                this.progress = 0;
                this.error = null;

                // Simulate progress
                this.progressMessage = 'Przesyłanie zdjęcia...';
                this.animateProgress(20, 1000);

                try {
                    const formData = new FormData();
                    formData.append('photo', this.file);

                    // Upload and analyze
                    const response = await fetch('{{ route('fridge.upload-photo') }}', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: formData
                    });

                    this.progressMessage = 'AI analizuje zdjęcie...';
                    this.animateProgress(60, 2000);

                    const data = await response.json();

                    if (!response.ok || !data.success) {
                        throw new Error(data.error || 'Nie udało się przeanalizować zdjęcia');
                    }

                    this.progressMessage = 'Przetwarzanie wyników...';
                    this.animateProgress(90, 500);

                    // Transform products for editing
                    this.results = data.products.map((product, index) => ({
                        id: index,
                        product_name: product.product_name,
                        quantity: product.quantity || '',
                        unit: product.unit || 'pieces',
                        expires_days: product.expires_days || ''
                    }));

                    this.progress = 100;
                    this.progressMessage = 'Gotowe!';

                    setTimeout(() => {
                        this.state = 'results';
                        this.analyzing = false;
                    }, 500);

                } catch (error) {
                    console.error('Analysis error:', error);
                    this.error = error.message;
                    this.state = 'preview';
                    this.analyzing = false;
                }
            },

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

            addProduct() {
                this.results.push({
                    id: Date.now(),
                    product_name: '',
                    quantity: '',
                    unit: 'pieces',
                    expires_days: ''
                });
            },

            removeProduct(id) {
                this.results = this.results.filter(p => p.id !== id);
            },

            resetScan() {
                this.state = 'upload';
                this.file = null;
                this.preview = null;
                this.results = [];
                this.error = null;
                this.analyzing = false;
                this.progress = 0;
            },

            async saveToFridge() {
                if (this.results.length === 0) return;

                try {
                    const response = await fetch('{{ route('fridge.store-batch') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            products: this.results
                        })
                    });

                    if (response.ok) {
                        window.location.href = '{{ route('fridge.index') }}';
                    } else {
                        const data = await response.json();
                        this.error = data.message || 'Nie udało się zapisać produktów';
                    }
                } catch (error) {
                    this.error = 'Nie udało się zapisać produktów: ' + error.message;
                }
            }
        }">
            <!-- Header -->
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-gray-900">Zeskanuj lodówkę</h1>
                <p class="text-gray-600 mt-2">Użyj AI, aby automatycznie wykryć produkty ze zdjęcia</p>
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

            <!-- State 1: Upload -->
            <div x-show="state === 'upload'" class="fit-card p-8">
                <div class="text-center">
                    <div class="text-6xl mb-6">📸</div>
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">Prześlij zdjęcie</h2>
                    <p class="text-gray-600 mb-8">Zrób zdjęcie lodówki, a AI wykryje wszystkie produkty</p>

                    <!-- Mobile: Dwa przyciski (aparat + galeria) -->
                    <div class="md:hidden space-y-3">
                        <!-- Aparat -->
                        <label class="block">
                            <input
                                type="file"
                                accept="image/*"
                                capture="environment"
                                @change="handleFileSelect"
                                class="hidden"
                                id="camera-input"
                            >
                            <div class="w-full btn-fit-primary text-center cursor-pointer flex items-center justify-center gap-2">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                                Zrób zdjęcie
                            </div>
                        </label>

                        <!-- Galeria -->
                        <label class="block">
                            <input
                                type="file"
                                accept="image/jpeg,image/jpg,image/png,image/webp"
                                @change="handleFileSelect"
                                class="hidden"
                                id="gallery-input"
                            >
                            <div class="w-full px-6 py-3 border-2 border-fit-green-500 text-fit-green-600 rounded-md hover:bg-fit-green-50 font-medium text-center cursor-pointer flex items-center justify-center gap-2">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                                Wybierz z galerii
                            </div>
                        </label>
                    </div>

                    <!-- Desktop: Drag & drop -->
                    <div class="hidden md:block">
                        <label class="block">
                            <input
                                type="file"
                                @change="handleFileSelect"
                                accept="image/jpeg,image/jpg,image/png,image/webp"
                                class="hidden"
                            >
                            <div class="border-2 border-dashed border-fit-green-500 rounded-lg p-12 cursor-pointer hover:border-fit-green-600 hover:bg-fit-green-50 transition">
                                <div class="text-gray-600">
                                    <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                        <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                    <p class="text-lg font-medium mb-2">Kliknij, aby przesłać lub przeciągnij i upuść</p>
                                    <p class="text-sm">JPG, PNG lub WEBP (maks. 5MB)</p>
                                </div>
                            </div>
                        </label>
                    </div>

                    <div class="mt-8 flex gap-4 justify-center">
                        <a href="{{ route('fridge.index') }}" class="px-6 py-3 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50 font-medium">
                            Powrót do lodówki
                        </a>
                    </div>
                </div>
            </div>

            <!-- State 2: Preview -->
            <div x-show="state === 'preview'" x-cloak class="fit-card p-8">
                <div class="text-center">
                    <h2 class="text-2xl font-bold text-gray-900 mb-6">Podgląd zdjęcia</h2>

                    <!-- Image Preview -->
                    <div class="mb-8">
                        <img :src="preview" alt="Podgląd" class="max-w-full max-h-96 mx-auto rounded-lg shadow">
                    </div>

                    <div class="flex gap-4 justify-center">
                        <button
                            @click="resetScan"
                            class="px-6 py-3 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50 font-medium"
                        >
                            Wybierz inne zdjęcie
                        </button>
                        <button
                            @click="analyzeImage"
                            class="btn-fit-primary"
                        >
                            ✨ Analizuj za pomocą AI
                        </button>
                    </div>
                </div>
            </div>

            <!-- State 3: Analyzing -->
            <div x-show="state === 'analyzing'" x-cloak class="fit-card p-12">
                <div class="text-center">
                    <div class="mb-8">
                        <!-- Spinner -->
                        <svg class="animate-spin h-16 w-16 mx-auto text-fit-green-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </div>

                    <h2 class="text-2xl font-bold text-gray-900 mb-4">Analizowanie lodówki...</h2>
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

                    <p class="text-sm text-gray-500">Zazwyczaj zajmuje to 5-10 sekund</p>
                </div>
            </div>

            <!-- State 4: Results -->
            <div x-show="state === 'results'" x-cloak class="fit-card p-8">
                <div class="mb-8">
                    <h2 class="text-2xl font-bold text-gray-900 mb-2">Wykryte produkty</h2>
                    <p class="text-gray-600">Sprawdź i edytuj wykryte produkty przed zapisaniem</p>
                </div>

                <!-- Products List -->
                <div class="space-y-4 mb-6">
                    <template x-for="product in results" :key="product.id">
                        <div class="border border-gray-200 rounded-lg p-4 bg-gray-50">
                            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                                <!-- Product Name -->
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Nazwa produktu</label>
                                    <input
                                        type="text"
                                        x-model="product.product_name"
                                        class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-2 focus:ring-fit-green-500 focus:border-fit-green-500"
                                        placeholder="Nazwa produktu"
                                    >
                                </div>

                                <!-- Quantity -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Ilość</label>
                                    <input
                                        type="number"
                                        x-model="product.quantity"
                                        step="0.01"
                                        class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-2 focus:ring-fit-green-500 focus:border-fit-green-500"
                                        placeholder="1.5"
                                    >
                                </div>

                                <!-- Unit -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Jednostka</label>
                                    <select
                                        x-model="product.unit"
                                        class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-2 focus:ring-fit-green-500 focus:border-fit-green-500"
                                    >
                                        <option value="kg">kg</option>
                                        <option value="g">g</option>
                                        <option value="L">L</option>
                                        <option value="ml">ml</option>
                                        <option value="pieces">sztuki</option>
                                        <option value="packs">opakowania</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Expires Days & Remove -->
                            <div class="flex gap-4 mt-4">
                                <div class="flex-1">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Wygasa za (dni)</label>
                                    <input
                                        type="number"
                                        x-model="product.expires_days"
                                        class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-2 focus:ring-fit-green-500 focus:border-fit-green-500"
                                        placeholder="7"
                                    >
                                </div>
                                <div class="flex items-end">
                                    <button
                                        @click="removeProduct(product.id)"
                                        class="px-4 py-2 bg-red-100 text-red-700 rounded-md hover:bg-red-200"
                                    >
                                        Usuń
                                    </button>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>

                <!-- Add Product Button -->
                <div class="mb-8">
                    <button
                        @click="addProduct"
                        class="w-full px-4 py-3 border-2 border-dashed border-gray-300 rounded-lg text-gray-600 hover:border-fit-green-500 hover:text-fit-green-600 hover:bg-fit-green-50 transition"
                    >
                        + Dodaj kolejny produkt
                    </button>
                </div>

                <!-- Actions -->
                <div class="flex gap-4">
                    <button
                        @click="resetScan"
                        class="flex-1 px-6 py-3 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50 font-medium"
                    >
                        Skanuj ponownie
                    </button>
                    <button
                        @click="saveToFridge"
                        class="flex-1 btn-fit-primary"
                    >
                        Zapisz do lodówki
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
[x-cloak] { display: none !important; }
</style>
@endsection
