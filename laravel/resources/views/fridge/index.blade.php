@extends('layouts.app')

@section('title', 'My Fridge')

@section('content')
<div class="py-12" x-data="{
    view: 'grid',
    filter: 'all',
    search: '',
    items: {{ Js::from($items->map(fn($item) => [
        'id' => $item->id,
        'product_name' => $item->product_name,
        'quantity' => $item->quantity,
        'unit' => $item->unit,
        'added_at' => $item->added_at->format('Y-m-d'),
        'expires_at' => $item->expires_at?->format('Y-m-d'),
        'is_expired' => $item->isExpired(),
        'is_expiring_soon' => $item->isExpiringSoon(),
        'is_fresh' => $item->isFresh(),
    ])) }},
    get filteredItems() {
        return this.items.filter(item => {
            // Search filter
            if (this.search && !item.product_name.toLowerCase().includes(this.search.toLowerCase())) {
                return false;
            }

            // Status filter
            if (this.filter === 'fresh' && !item.is_fresh) return false;
            if (this.filter === 'expiring' && !item.is_expiring_soon) return false;
            if (this.filter === 'expired' && !item.is_expired) return false;

            return true;
        });
    }
}">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="flex justify-between items-center mb-8">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">My Fridge</h1>
                <p class="text-gray-600 mt-2">Track your ingredients and their expiration dates</p>
            </div>
            <div class="flex gap-3">
                <a href="{{ route('fridge.scan') }}" class="inline-flex items-center px-4 py-2 border border-emerald-500 text-emerald-600 rounded-md hover:bg-emerald-50">
                    📸 Scan Fridge
                </a>
                <a href="{{ route('fridge.create') }}" class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-emerald-500 to-teal-600 text-white rounded-md hover:shadow-lg transition font-semibold">
                    + Add Item
                </a>
            </div>
        </div>

        <!-- Success Message -->
        @if(session('success'))
            <div class="mb-6 p-4 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-lg">
                {{ session('success') }}
            </div>
        @endif

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
            <div class="bg-white rounded-lg shadow p-6">
                <div class="text-sm text-gray-600">Total Items</div>
                <div class="text-3xl font-bold text-gray-900 mt-1">{{ $totalItems }}</div>
            </div>
            <div class="bg-white rounded-lg shadow p-6">
                <div class="text-sm text-gray-600">Fresh</div>
                <div class="text-3xl font-bold text-green-600 mt-1">{{ $fresh }}</div>
            </div>
            <div class="bg-white rounded-lg shadow p-6">
                <div class="text-sm text-gray-600">Expiring Soon</div>
                <div class="text-3xl font-bold text-amber-600 mt-1">{{ $expiringSoon }}</div>
            </div>
            <div class="bg-white rounded-lg shadow p-6">
                <div class="text-sm text-gray-600">Expired</div>
                <div class="text-3xl font-bold text-red-600 mt-1">{{ $expired }}</div>
            </div>
        </div>

        <!-- Filters & Search -->
        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <div class="flex flex-col md:flex-row gap-4">
                <!-- Search -->
                <div class="flex-1">
                    <input
                        type="text"
                        x-model="search"
                        placeholder="Search products..."
                        class="w-full border border-gray-300 rounded-md px-4 py-2 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                    >
                </div>

                <!-- Filter Buttons -->
                <div class="flex gap-2">
                    <button
                        @click="filter = 'all'"
                        :class="filter === 'all' ? 'bg-emerald-500 text-white' : 'bg-gray-100 text-gray-700'"
                        class="px-4 py-2 rounded-md hover:bg-emerald-600 hover:text-white transition"
                    >
                        All
                    </button>
                    <button
                        @click="filter = 'fresh'"
                        :class="filter === 'fresh' ? 'bg-green-500 text-white' : 'bg-gray-100 text-gray-700'"
                        class="px-4 py-2 rounded-md hover:bg-green-600 hover:text-white transition"
                    >
                        Fresh
                    </button>
                    <button
                        @click="filter = 'expiring'"
                        :class="filter === 'expiring' ? 'bg-amber-500 text-white' : 'bg-gray-100 text-gray-700'"
                        class="px-4 py-2 rounded-md hover:bg-amber-600 hover:text-white transition"
                    >
                        Expiring
                    </button>
                    <button
                        @click="filter = 'expired'"
                        :class="filter === 'expired' ? 'bg-red-500 text-white' : 'bg-gray-100 text-gray-700'"
                        class="px-4 py-2 rounded-md hover:bg-red-600 hover:text-white transition"
                    >
                        Expired
                    </button>
                </div>

                <!-- View Toggle -->
                <div class="flex gap-2 border-l pl-4">
                    <button
                        @click="view = 'grid'"
                        :class="view === 'grid' ? 'bg-emerald-500 text-white' : 'bg-gray-100 text-gray-700'"
                        class="px-3 py-2 rounded-md"
                    >
                        Grid
                    </button>
                    <button
                        @click="view = 'list'"
                        :class="view === 'list' ? 'bg-emerald-500 text-white' : 'bg-gray-100 text-gray-700'"
                        class="px-3 py-2 rounded-md"
                    >
                        List
                    </button>
                </div>
            </div>
        </div>

        <!-- Items Grid/List -->
        <div x-show="filteredItems.length > 0">
            <!-- Grid View -->
            <div x-show="view === 'grid'" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <template x-for="item in filteredItems" :key="item.id">
                    <div class="bg-white rounded-lg shadow hover:shadow-lg transition p-6">
                        <!-- Status Badge -->
                        <div class="mb-3">
                            <span
                                x-show="item.is_expired"
                                class="inline-block px-3 py-1 bg-red-100 text-red-700 text-xs font-semibold rounded-full"
                            >
                                Expired
                            </span>
                            <span
                                x-show="item.is_expiring_soon && !item.is_expired"
                                class="inline-block px-3 py-1 bg-amber-100 text-amber-700 text-xs font-semibold rounded-full"
                            >
                                Expiring Soon
                            </span>
                            <span
                                x-show="item.is_fresh"
                                class="inline-block px-3 py-1 bg-green-100 text-green-700 text-xs font-semibold rounded-full"
                            >
                                Fresh
                            </span>
                        </div>

                        <!-- Product Name -->
                        <h3 class="text-xl font-semibold text-gray-900 mb-2" x-text="item.product_name"></h3>

                        <!-- Quantity -->
                        <p class="text-gray-600 mb-4">
                            <span x-text="item.quantity || 'N/A'"></span>
                            <span x-text="item.unit || ''"></span>
                        </p>

                        <!-- Dates -->
                        <div class="text-sm text-gray-500 mb-4 space-y-1">
                            <div>Added: <span x-text="item.added_at"></span></div>
                            <div x-show="item.expires_at">
                                Expires: <span x-text="item.expires_at"></span>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="flex gap-2">
                            <a
                                :href="`/fridge/${item.id}/edit`"
                                class="flex-1 text-center px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50"
                            >
                                Edit
                            </a>
                            <form :action="`/fridge/${item.id}`" method="POST" class="flex-1">
                                @csrf
                                @method('DELETE')
                                <button
                                    type="submit"
                                    onclick="return confirm('Are you sure you want to delete this item?')"
                                    class="w-full px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700"
                                >
                                    Delete
                                </button>
                            </form>
                        </div>
                    </div>
                </template>
            </div>

            <!-- List View -->
            <div x-show="view === 'list'" class="bg-white rounded-lg shadow overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Added</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Expires</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <template x-for="item in filteredItems" :key="item.id">
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900" x-text="item.product_name"></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">
                                        <span x-text="item.quantity || 'N/A'"></span>
                                        <span x-text="item.unit || ''"></span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-500" x-text="item.added_at"></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-500" x-text="item.expires_at || 'N/A'"></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span
                                        x-show="item.is_expired"
                                        class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800"
                                    >
                                        Expired
                                    </span>
                                    <span
                                        x-show="item.is_expiring_soon && !item.is_expired"
                                        class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-amber-100 text-amber-800"
                                    >
                                        Expiring Soon
                                    </span>
                                    <span
                                        x-show="item.is_fresh"
                                        class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800"
                                    >
                                        Fresh
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <a :href="`/fridge/${item.id}/edit`" class="text-emerald-600 hover:text-emerald-900 mr-4">Edit</a>
                                    <form :action="`/fridge/${item.id}`" method="POST" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button
                                            type="submit"
                                            onclick="return confirm('Are you sure?')"
                                            class="text-red-600 hover:text-red-900"
                                        >
                                            Delete
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Empty State -->
        <div x-show="filteredItems.length === 0" class="bg-white rounded-lg shadow p-12 text-center">
            <div class="text-6xl mb-4">🍽️</div>
            <h3 class="text-xl font-semibold text-gray-900 mb-2">
                <span x-show="search || filter !== 'all'">No items match your filters</span>
                <span x-show="!search && filter === 'all'">Your fridge is empty</span>
            </h3>
            <p class="text-gray-600 mb-6">
                <span x-show="search || filter !== 'all'">Try adjusting your search or filters</span>
                <span x-show="!search && filter === 'all'">Start adding ingredients to track their expiration dates</span>
            </p>
            <div class="flex gap-3 justify-center">
                <a href="{{ route('fridge.scan') }}" class="inline-flex items-center px-4 py-2 border border-emerald-500 text-emerald-600 rounded-md hover:bg-emerald-50">
                    📸 Scan Fridge
                </a>
                <a href="{{ route('fridge.create') }}" class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-emerald-500 to-teal-600 text-white rounded-md hover:shadow-lg transition font-semibold">
                    + Add Item
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
