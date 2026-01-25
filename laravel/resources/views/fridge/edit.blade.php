@extends('layouts.app')

@section('title', 'Edit Fridge Item')

@section('content')
<div class="py-12">
    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">Edit Fridge Item</h1>
            <p class="text-gray-600 mt-2">Update product details</p>
        </div>

        <!-- Form -->
        <form action="{{ route('fridge.update', $fridgeItem) }}" method="POST" class="bg-white rounded-lg shadow-lg p-8">
            @csrf
            @method('PUT')

            <!-- Product Name -->
            <div class="mb-6">
                <label for="product_name" class="block text-sm font-medium text-gray-700 mb-2">
                    Product Name <span class="text-red-500">*</span>
                </label>
                <input
                    type="text"
                    name="product_name"
                    id="product_name"
                    value="{{ old('product_name', $fridgeItem->product_name) }}"
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
                        value="{{ old('quantity', $fridgeItem->quantity) }}"
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
                        <option value="kg" {{ old('unit', $fridgeItem->unit) === 'kg' ? 'selected' : '' }}>kg</option>
                        <option value="g" {{ old('unit', $fridgeItem->unit) === 'g' ? 'selected' : '' }}>g</option>
                        <option value="L" {{ old('unit', $fridgeItem->unit) === 'L' ? 'selected' : '' }}>L</option>
                        <option value="ml" {{ old('unit', $fridgeItem->unit) === 'ml' ? 'selected' : '' }}>ml</option>
                        <option value="pieces" {{ old('unit', $fridgeItem->unit) === 'pieces' ? 'selected' : '' }}>pieces</option>
                        <option value="packs" {{ old('unit', $fridgeItem->unit) === 'packs' ? 'selected' : '' }}>packs</option>
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
                    value="{{ old('expires_at', $fridgeItem->expires_at?->format('Y-m-d')) }}"
                    class="w-full border border-gray-300 rounded-md px-4 py-3 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 @error('expires_at') border-red-500 @enderror"
                >
                @error('expires_at')
                    <p class="text-red-600 text-sm mt-2">{{ $message }}</p>
                @enderror
                <p class="text-sm text-gray-500 mt-2">Optional - leave empty if unknown</p>
            </div>

            <!-- Added Date (Read-only) -->
            <div class="mb-8">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Added Date
                </label>
                <div class="px-4 py-3 bg-gray-50 border border-gray-200 rounded-md text-gray-700">
                    {{ $fridgeItem->added_at->format('F j, Y \a\t g:i A') }}
                </div>
                <p class="text-sm text-gray-500 mt-2">This date cannot be changed</p>
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
                    Update Item
                </button>
            </div>
        </form>

        <!-- Delete Section -->
        <div class="mt-8 bg-white rounded-lg shadow-lg p-8 border-2 border-red-200">
            <h3 class="text-lg font-semibold text-gray-900 mb-2">Danger Zone</h3>
            <p class="text-gray-600 mb-4">Once you delete this item, there is no going back. Please be certain.</p>
            <form action="{{ route('fridge.destroy', $fridgeItem) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this item? This action cannot be undone.');">
                @csrf
                @method('DELETE')
                <button
                    type="submit"
                    class="px-6 py-3 bg-red-600 text-white rounded-md hover:bg-red-700 font-semibold"
                >
                    Delete Item
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
