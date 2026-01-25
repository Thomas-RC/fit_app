@extends('layouts.app')

@section('title', 'Scan Fridge')

@section('content')
<div class="py-12">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div x-data="{
            state: 'upload', // upload, preview, analyzing, results
            file: null,
            preview: null,
            analyzing: false,
            progress: 0,
            progressMessage: 'Uploading...',
            results: [],
            error: null,

            handleFileSelect(event) {
                const file = event.target.files[0];
                if (!file) return;

                this.error = null;

                // Validate file size (max 5MB)
                if (file.size > 5 * 1024 * 1024) {
                    this.error = 'File size must be less than 5MB';
                    return;
                }

                // Validate file type
                if (!file.type.match('image/(jpg|jpeg|png|webp)')) {
                    this.error = 'File must be JPG, PNG, or WEBP';
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
                this.progressMessage = 'Uploading image...';
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

                    this.progressMessage = 'AI analyzing image...';
                    this.animateProgress(60, 2000);

                    const data = await response.json();

                    if (!response.ok || !data.success) {
                        throw new Error(data.error || 'Failed to analyze image');
                    }

                    this.progressMessage = 'Processing results...';
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
                    this.progressMessage = 'Done!';

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
                        this.error = data.message || 'Failed to save products';
                    }
                } catch (error) {
                    this.error = 'Failed to save products: ' + error.message;
                }
            }
        }">
            <!-- Header -->
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-gray-900">Scan Your Fridge</h1>
                <p class="text-gray-600 mt-2">Use AI to automatically detect products from a photo</p>
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
            <div x-show="state === 'upload'" class="bg-white rounded-lg shadow-lg p-8">
                <div class="text-center">
                    <div class="text-6xl mb-6">📸</div>
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">Upload a Photo</h2>
                    <p class="text-gray-600 mb-8">Take a photo of your fridge and let AI detect all the products</p>

                    <!-- Drop Zone -->
                    <label class="block">
                        <input
                            type="file"
                            @change="handleFileSelect"
                            accept="image/jpeg,image/jpg,image/png,image/webp"
                            class="hidden"
                        >
                        <div class="border-2 border-dashed border-gray-300 rounded-lg p-12 cursor-pointer hover:border-emerald-500 hover:bg-emerald-50 transition">
                            <div class="text-gray-600">
                                <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                    <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                                <p class="text-lg font-medium mb-2">Click to upload or drag and drop</p>
                                <p class="text-sm">JPG, PNG, or WEBP (max 5MB)</p>
                            </div>
                        </div>
                    </label>

                    <div class="mt-8 flex gap-4 justify-center">
                        <a href="{{ route('fridge.index') }}" class="px-6 py-3 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50 font-medium">
                            Back to Fridge
                        </a>
                    </div>
                </div>
            </div>

            <!-- State 2: Preview -->
            <div x-show="state === 'preview'" x-cloak class="bg-white rounded-lg shadow-lg p-8">
                <div class="text-center">
                    <h2 class="text-2xl font-bold text-gray-900 mb-6">Preview Image</h2>

                    <!-- Image Preview -->
                    <div class="mb-8">
                        <img :src="preview" alt="Preview" class="max-w-full max-h-96 mx-auto rounded-lg shadow">
                    </div>

                    <div class="flex gap-4 justify-center">
                        <button
                            @click="resetScan"
                            class="px-6 py-3 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50 font-medium"
                        >
                            Choose Different Photo
                        </button>
                        <button
                            @click="analyzeImage"
                            class="px-6 py-3 bg-gradient-to-r from-emerald-500 to-teal-600 text-white rounded-md hover:shadow-lg transition font-semibold"
                        >
                            ✨ Analyze with AI
                        </button>
                    </div>
                </div>
            </div>

            <!-- State 3: Analyzing -->
            <div x-show="state === 'analyzing'" x-cloak class="bg-white rounded-lg shadow-lg p-12">
                <div class="text-center">
                    <div class="mb-8">
                        <!-- Spinner -->
                        <svg class="animate-spin h-16 w-16 mx-auto text-emerald-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </div>

                    <h2 class="text-2xl font-bold text-gray-900 mb-4">Analyzing Your Fridge...</h2>
                    <p class="text-gray-600 mb-8" x-text="progressMessage"></p>

                    <!-- Progress Bar -->
                    <div class="max-w-md mx-auto mb-4">
                        <div class="bg-gray-200 rounded-full h-4 overflow-hidden">
                            <div
                                class="bg-gradient-to-r from-emerald-500 to-teal-600 h-full rounded-full transition-all duration-300"
                                :style="`width: ${progress}%`"
                            ></div>
                        </div>
                        <p class="text-sm text-gray-500 mt-2" x-text="`${Math.round(progress)}%`"></p>
                    </div>

                    <p class="text-sm text-gray-500">This usually takes 5-10 seconds</p>
                </div>
            </div>

            <!-- State 4: Results -->
            <div x-show="state === 'results'" x-cloak class="bg-white rounded-lg shadow-lg p-8">
                <div class="mb-8">
                    <h2 class="text-2xl font-bold text-gray-900 mb-2">Detected Products</h2>
                    <p class="text-gray-600">Review and edit the detected items before saving</p>
                </div>

                <!-- Products List -->
                <div class="space-y-4 mb-6">
                    <template x-for="product in results" :key="product.id">
                        <div class="border border-gray-200 rounded-lg p-4 bg-gray-50">
                            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                                <!-- Product Name -->
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Product Name</label>
                                    <input
                                        type="text"
                                        x-model="product.product_name"
                                        class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                                        placeholder="Product name"
                                    >
                                </div>

                                <!-- Quantity -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Quantity</label>
                                    <input
                                        type="number"
                                        x-model="product.quantity"
                                        step="0.01"
                                        class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                                        placeholder="1.5"
                                    >
                                </div>

                                <!-- Unit -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Unit</label>
                                    <select
                                        x-model="product.unit"
                                        class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                                    >
                                        <option value="kg">kg</option>
                                        <option value="g">g</option>
                                        <option value="L">L</option>
                                        <option value="ml">ml</option>
                                        <option value="pieces">pieces</option>
                                        <option value="packs">packs</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Expires Days & Remove -->
                            <div class="flex gap-4 mt-4">
                                <div class="flex-1">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Expires in (days)</label>
                                    <input
                                        type="number"
                                        x-model="product.expires_days"
                                        class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                                        placeholder="7"
                                    >
                                </div>
                                <div class="flex items-end">
                                    <button
                                        @click="removeProduct(product.id)"
                                        class="px-4 py-2 bg-red-100 text-red-700 rounded-md hover:bg-red-200"
                                    >
                                        Remove
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
                        class="w-full px-4 py-3 border-2 border-dashed border-gray-300 rounded-lg text-gray-600 hover:border-emerald-500 hover:text-emerald-600 hover:bg-emerald-50 transition"
                    >
                        + Add Another Product
                    </button>
                </div>

                <!-- Actions -->
                <div class="flex gap-4">
                    <button
                        @click="resetScan"
                        class="flex-1 px-6 py-3 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50 font-medium"
                    >
                        Scan Again
                    </button>
                    <button
                        @click="saveToFridge"
                        class="flex-1 px-6 py-3 bg-gradient-to-r from-emerald-500 to-teal-600 text-white rounded-md hover:shadow-lg transition font-semibold"
                    >
                        Save to Fridge
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
