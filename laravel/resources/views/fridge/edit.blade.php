@extends('layouts.app')

@section('title', 'Edytuj produkt')

@section('content')
<div class="py-12">
    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">Edytuj produkt</h1>
            <p class="text-gray-600 mt-2">Aktualizuj szczegóły produktu</p>
        </div>

        <!-- Form -->
        <form action="{{ route('fridge.update', $fridgeItem) }}" method="POST" class="bg-white rounded-lg shadow-lg p-8">
            @csrf
            @method('PUT')

            <!-- Product Name -->
            <div class="mb-6">
                <label for="product_name" class="block text-sm font-medium text-gray-700 mb-2">
                    Nazwa produktu <span class="text-red-500">*</span>
                </label>
                <input
                    type="text"
                    name="product_name"
                    id="product_name"
                    value="{{ old('product_name', $fridgeItem->product_name) }}"
                    required
                    class="w-full border border-gray-300 rounded-md px-4 py-3 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 @error('product_name') border-red-500 @enderror"
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
                        value="{{ old('quantity', $fridgeItem->quantity) }}"
                        step="0.01"
                        min="0"
                        max="9999.99"
                        class="w-full border border-gray-300 rounded-md px-4 py-3 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 @error('quantity') border-red-500 @enderror"
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
                        class="w-full border border-gray-300 rounded-md px-4 py-3 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 @error('unit') border-red-500 @enderror"
                    >
                        <option value="">Wybierz jednostkę...</option>
                        <option value="kg" {{ old('unit', $fridgeItem->unit) === 'kg' ? 'selected' : '' }}>kg</option>
                        <option value="g" {{ old('unit', $fridgeItem->unit) === 'g' ? 'selected' : '' }}>g</option>
                        <option value="L" {{ old('unit', $fridgeItem->unit) === 'L' ? 'selected' : '' }}>L</option>
                        <option value="ml" {{ old('unit', $fridgeItem->unit) === 'ml' ? 'selected' : '' }}>ml</option>
                        <option value="szt" {{ old('unit', $fridgeItem->unit) === 'szt' ? 'selected' : '' }}>szt</option>
                        <option value="opak" {{ old('unit', $fridgeItem->unit) === 'opak' ? 'selected' : '' }}>opak</option>
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
                    value="{{ old('expires_at', $fridgeItem->expires_at?->format('Y-m-d')) }}"
                    class="w-full border border-gray-300 rounded-md px-4 py-3 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 @error('expires_at') border-red-500 @enderror"
                >
                @error('expires_at')
                    <p class="text-red-600 text-sm mt-2">{{ $message }}</p>
                @enderror
                <p class="text-sm text-gray-500 mt-2">Opcjonalne - pozostaw puste jeśli nieznane</p>
            </div>

            <!-- Added Date (Read-only) -->
            <div class="mb-8">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Data dodania
                </label>
                <div class="px-4 py-3 bg-gray-50 border border-gray-200 rounded-md text-gray-700">
                    {{ $fridgeItem->added_at->translatedFormat('j F Y, H:i') }}
                </div>
                <p class="text-sm text-gray-500 mt-2">Ta data nie może być zmieniona</p>
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
                    class="flex-1 px-6 py-3 bg-gradient-to-r from-emerald-500 to-teal-600 text-white rounded-md hover:shadow-lg transition font-semibold"
                >
                    Zaktualizuj produkt
                </button>
            </div>
        </form>

        <!-- Delete Section -->
        <div class="mt-8 bg-white rounded-lg shadow-lg p-8 border-2 border-red-200" x-data="{ showDeleteConfirm: false }">
            <h3 class="text-lg font-semibold text-gray-900 mb-2">Strefa niebezpieczna</h3>
            <p class="text-gray-600 mb-4">Po usunięciu produktu nie będzie można go przywrócić. Upewnij się, że chcesz to zrobić.</p>
            <form action="{{ route('fridge.destroy', $fridgeItem) }}" method="POST" @submit.prevent="if(showDeleteConfirm) { $el.submit(); } else { showToast('Kliknij ponownie aby potwierdzić usunięcie', 'error', 4000); showDeleteConfirm = true; setTimeout(() => showDeleteConfirm = false, 5000); }">
                @csrf
                @method('DELETE')
                <button
                    type="submit"
                    :class="showDeleteConfirm ? 'bg-red-700' : 'bg-red-600'"
                    class="px-6 py-3 text-white rounded-md hover:bg-red-700 font-semibold transition"
                >
                    <span x-show="!showDeleteConfirm">Usuń produkt</span>
                    <span x-show="showDeleteConfirm">⚠️ Kliknij ponownie aby potwierdzić</span>
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
