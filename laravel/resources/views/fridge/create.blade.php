@extends('layouts.app')

@section('title', 'Add Fridge Item')

@section('content')
<div class="py-12">
    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">Add Fridge Item</h1>
            <p class="text-gray-600 mt-2">Manually add a product to your fridge</p>
        </div>

        <!-- Form -->
        <form action="{{ route('fridge.store') }}" method="POST" class="bg-white rounded-lg shadow-lg p-8">
            @csrf

            <!-- Product Name -->
            <div class="mb-6">
                <label for="product_name" class="block text-sm font-medium text-gray-700 mb-2">
                    Product Name <span class="text-red-500">*</span>
                </label>
                <input
                    type="text"
                    name="product_name"
                    id="product_name"
                    value="{{ old('product_name') }}"
                    required
                    class="w-full border border-gray-300 rounded-md px-4 py-3 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 @error('product_name') border-red-500 @enderror"
                    placeholder="e.g., Milk, Eggs, Chicken..."
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
                        Quantity
                    </label>
                    <input
                        type="number"
                        name="quantity"
                        id="quantity"
                        value="{{ old('quantity') }}"
                        step="0.01"
                        min="0"
                        max="9999.99"
                        class="w-full border border-gray-300 rounded-md px-4 py-3 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 @error('quantity') border-red-500 @enderror"
                        placeholder="e.g., 1.5"
                    >
                    @error('quantity')
                        <p class="text-red-600 text-sm mt-2">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Unit -->
                <div>
                    <label for="unit" class="block text-sm font-medium text-gray-700 mb-2">
                        Unit
                    </label>
                    <select
                        name="unit"
                        id="unit"
                        class="w-full border border-gray-300 rounded-md px-4 py-3 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 @error('unit') border-red-500 @enderror"
                    >
                        <option value="">Select unit...</option>
                        <option value="kg" {{ old('unit') === 'kg' ? 'selected' : '' }}>kg</option>
                        <option value="g" {{ old('unit') === 'g' ? 'selected' : '' }}>g</option>
                        <option value="L" {{ old('unit') === 'L' ? 'selected' : '' }}>L</option>
                        <option value="ml" {{ old('unit') === 'ml' ? 'selected' : '' }}>ml</option>
                        <option value="pieces" {{ old('unit') === 'pieces' ? 'selected' : '' }}>pieces</option>
                        <option value="packs" {{ old('unit') === 'packs' ? 'selected' : '' }}>packs</option>
                    </select>
                    @error('unit')
                        <p class="text-red-600 text-sm mt-2">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Expiration Date -->
            <div class="mb-6">
                <label for="expires_at" class="block text-sm font-medium text-gray-700 mb-2">
                    Expiration Date
                </label>
                <input
                    type="date"
                    name="expires_at"
                    id="expires_at"
                    value="{{ old('expires_at') }}"
                    min="{{ date('Y-m-d') }}"
                    class="w-full border border-gray-300 rounded-md px-4 py-3 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 @error('expires_at') border-red-500 @enderror"
                >
                @error('expires_at')
                    <p class="text-red-600 text-sm mt-2">{{ $message }}</p>
                @enderror
                <p class="text-sm text-gray-500 mt-2">Optional - leave empty if unknown</p>
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
                            <strong>Tip:</strong> For faster entry, you can use the "Scan Fridge" feature to automatically detect products from a photo!
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
                    Cancel
                </a>
                <button
                    type="submit"
                    class="flex-1 px-6 py-3 bg-gradient-to-r from-emerald-500 to-teal-600 text-white rounded-md hover:shadow-lg transition font-semibold"
                >
                    Add to Fridge
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
