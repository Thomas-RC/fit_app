@extends('layouts.app')

@section('title', 'Admin Settings')

@section('content')
<div class="py-12" x-data="{ activeTab: 'vertex-ai', testing: false }">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex items-center gap-3 mb-2">
                <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                </svg>
                <h1 class="text-3xl font-bold text-gray-900">Admin Settings</h1>
            </div>
            <div class="bg-amber-50 border border-amber-200 rounded-lg p-4">
                <div class="flex items-start gap-3">
                    <svg class="w-5 h-5 text-amber-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    <div>
                        <p class="text-sm font-medium text-amber-800">Sensitive Configuration</p>
                        <p class="text-sm text-amber-700">All credentials are stored encrypted in the database. Only administrators can access this page.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabs -->
        <div class="mb-8">
            <div class="border-b border-gray-200">
                <nav class="flex gap-8">
                    <button
                        @click="activeTab = 'vertex-ai'"
                        :class="activeTab === 'vertex-ai' ? 'border-emerald-500 text-emerald-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                        class="py-4 px-1 border-b-2 font-medium text-sm transition"
                    >
                        Vertex AI (Gemini)
                    </button>
                    <button
                        @click="activeTab = 'spoonacular'"
                        :class="activeTab === 'spoonacular' ? 'border-emerald-500 text-emerald-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                        class="py-4 px-1 border-b-2 font-medium text-sm transition"
                    >
                        Spoonacular API
                    </button>
                    <button
                        @click="activeTab = 'general'"
                        :class="activeTab === 'general' ? 'border-emerald-500 text-emerald-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                        class="py-4 px-1 border-b-2 font-medium text-sm transition"
                    >
                        General
                    </button>
                </nav>
            </div>
        </div>

        <!-- Vertex AI Tab -->
        <div x-show="activeTab === 'vertex-ai'" x-cloak>
            <div class="bg-white rounded-lg shadow-lg p-8">
                <div class="mb-6">
                    <h2 class="text-xl font-bold text-gray-900 mb-2">Google Cloud Vertex AI Configuration</h2>
                    <p class="text-sm text-gray-600">Configure Gemini Vision API for scanning fridge photos and detecting food items.</p>
                </div>

                <!-- Status Badge -->
                <div class="mb-6">
                    <div class="flex items-center gap-2">
                        <span class="text-sm font-medium text-gray-700">Status:</span>
                        @if($vertexAIConfigured)
                            <span class="inline-flex items-center px-3 py-1 bg-emerald-100 text-emerald-700 rounded-full text-sm font-medium">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                Configured
                            </span>
                        @else
                            <span class="inline-flex items-center px-3 py-1 bg-gray-100 text-gray-700 rounded-full text-sm font-medium">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                Not Configured
                            </span>
                        @endif
                    </div>
                    @if($vertexAIConfigured && $vertexAIUpdatedAt)
                        <p class="text-xs text-gray-500 mt-1">Last updated: {{ $vertexAIUpdatedAt->diffForHumans() }}</p>
                    @endif
                </div>

                <!-- Current Config -->
                @if($vertexAIConfigured)
                    <div class="bg-gray-50 rounded-lg p-4 mb-6">
                        <h3 class="text-sm font-semibold text-gray-700 mb-2">Current Configuration</h3>
                        <div class="space-y-1 text-sm">
                            <p><span class="font-medium text-gray-600">Project ID:</span> <code class="text-emerald-600">{{ $vertexAIProjectId }}</code></p>
                        </div>
                    </div>
                @endif

                <!-- Upload Form -->
                <form action="{{ route('admin.vertex-ai.update') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                    @csrf

                    <!-- Project ID -->
                    <div>
                        <label for="project_id" class="block text-sm font-medium text-gray-700 mb-2">
                            Google Cloud Project ID <span class="text-red-500">*</span>
                        </label>
                        <input
                            type="text"
                            name="project_id"
                            id="project_id"
                            value="{{ old('project_id', $vertexAIProjectId) }}"
                            required
                            placeholder="e.g., my-project-123456"
                            class="w-full border border-gray-300 rounded-md px-4 py-2 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                        >
                        @error('project_id')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Service Account JSON -->
                    <div>
                        <label for="credentials_file" class="block text-sm font-medium text-gray-700 mb-2">
                            Service Account JSON File <span class="text-red-500">*</span>
                        </label>
                        <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center hover:border-emerald-500 transition">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                            </svg>
                            <div class="mt-4">
                                <label for="credentials_file" class="cursor-pointer inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-sm text-gray-700 hover:bg-gray-50">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                    Choose JSON File
                                </label>
                                <input
                                    type="file"
                                    name="credentials_file"
                                    id="credentials_file"
                                    accept=".json"
                                    required
                                    class="sr-only"
                                    onchange="document.getElementById('file-name').textContent = this.files[0]?.name || 'No file selected'"
                                >
                            </div>
                            <p class="text-xs text-gray-500 mt-2" id="file-name">No file selected</p>
                            <p class="text-xs text-gray-500 mt-1">Max file size: 10MB</p>
                        </div>
                        @error('credentials_file')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                        <div class="mt-3 bg-blue-50 border border-blue-200 rounded-lg p-3">
                            <p class="text-xs text-blue-800">
                                <strong>How to get service account JSON:</strong><br>
                                1. Go to <a href="https://console.cloud.google.com/iam-admin/serviceaccounts" target="_blank" class="underline">Google Cloud Console</a><br>
                                2. Select your project → Create Service Account<br>
                                3. Grant "Vertex AI User" role<br>
                                4. Create key → JSON format → Download
                            </p>
                        </div>
                    </div>

                    <!-- Buttons -->
                    <div class="flex gap-3">
                        <button
                            type="submit"
                            class="flex-1 px-6 py-3 bg-gradient-to-r from-emerald-500 to-teal-600 text-white rounded-md hover:shadow-lg transition font-semibold"
                        >
                            <svg class="inline w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                            </svg>
                            Upload Credentials
                        </button>
                        @if($vertexAIConfigured)
                            <button
                                type="button"
                                @click="testConnection('vertex-ai')"
                                :disabled="testing"
                                class="px-6 py-3 border border-emerald-500 text-emerald-600 rounded-md hover:bg-emerald-50 transition font-semibold disabled:opacity-50"
                            >
                                <svg class="inline w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <span x-text="testing ? 'Testing...' : 'Test Connection'"></span>
                            </button>
                        @endif
                    </div>
                </form>
            </div>
        </div>

        <!-- Spoonacular Tab -->
        <div x-show="activeTab === 'spoonacular'" x-cloak>
            <div class="bg-white rounded-lg shadow-lg p-8">
                <div class="mb-6">
                    <h2 class="text-xl font-bold text-gray-900 mb-2">Spoonacular API Configuration</h2>
                    <p class="text-sm text-gray-600">Configure Spoonacular API for recipe search, meal planning, and nutrition data.</p>
                </div>

                <!-- Status Badge -->
                <div class="mb-6">
                    <div class="flex items-center gap-2">
                        <span class="text-sm font-medium text-gray-700">Status:</span>
                        @if($spoonacularConfigured)
                            <span class="inline-flex items-center px-3 py-1 bg-emerald-100 text-emerald-700 rounded-full text-sm font-medium">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                Configured
                            </span>
                        @else
                            <span class="inline-flex items-center px-3 py-1 bg-gray-100 text-gray-700 rounded-full text-sm font-medium">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                Not Configured
                            </span>
                        @endif
                    </div>
                </div>

                <!-- Current Config -->
                @if($spoonacularConfigured && $maskedSpoonacularKey)
                    <div class="bg-gray-50 rounded-lg p-4 mb-6">
                        <h3 class="text-sm font-semibold text-gray-700 mb-2">Current Configuration</h3>
                        <div class="space-y-1 text-sm">
                            <p><span class="font-medium text-gray-600">API Key:</span> <code class="text-emerald-600">{{ $maskedSpoonacularKey }}</code></p>
                        </div>
                    </div>
                @endif

                <!-- API Key Form -->
                <form action="{{ route('admin.spoonacular.update') }}" method="POST" class="space-y-6">
                    @csrf

                    <!-- API Key -->
                    <div>
                        <label for="api_key" class="block text-sm font-medium text-gray-700 mb-2">
                            API Key <span class="text-red-500">*</span>
                        </label>
                        <input
                            type="password"
                            name="api_key"
                            id="api_key"
                            required
                            placeholder="Enter your Spoonacular API key"
                            class="w-full border border-gray-300 rounded-md px-4 py-2 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 font-mono text-sm"
                        >
                        @error('api_key')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                        <div class="mt-3 bg-blue-50 border border-blue-200 rounded-lg p-3">
                            <p class="text-xs text-blue-800">
                                <strong>How to get API key:</strong><br>
                                1. Go to <a href="https://spoonacular.com/food-api" target="_blank" class="underline">Spoonacular API</a><br>
                                2. Sign up for a free account<br>
                                3. Go to Profile → Show API Key<br>
                                4. Copy and paste the key here<br>
                                <em class="block mt-1">Free tier: 150 requests/day</em>
                            </p>
                        </div>
                    </div>

                    <!-- Buttons -->
                    <div class="flex gap-3">
                        <button
                            type="submit"
                            class="flex-1 px-6 py-3 bg-gradient-to-r from-emerald-500 to-teal-600 text-white rounded-md hover:shadow-lg transition font-semibold"
                        >
                            <svg class="inline w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/>
                            </svg>
                            Save API Key
                        </button>
                        @if($spoonacularConfigured)
                            <button
                                type="button"
                                @click="testConnection('spoonacular')"
                                :disabled="testing"
                                class="px-6 py-3 border border-emerald-500 text-emerald-600 rounded-md hover:bg-emerald-50 transition font-semibold disabled:opacity-50"
                            >
                                <svg class="inline w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <span x-text="testing ? 'Testing...' : 'Test Connection'"></span>
                            </button>
                        @endif
                    </div>
                </form>
            </div>
        </div>

        <!-- General Tab -->
        <div x-show="activeTab === 'general'" x-cloak>
            <div class="bg-white rounded-lg shadow-lg p-8">
                <div class="mb-6">
                    <h2 class="text-xl font-bold text-gray-900 mb-2">General Settings</h2>
                    <p class="text-sm text-gray-600">Application-wide configuration.</p>
                </div>

                <div class="space-y-6">
                    <!-- App Info -->
                    <div>
                        <h3 class="text-sm font-semibold text-gray-700 mb-3">Application Information</h3>
                        <div class="bg-gray-50 rounded-lg p-4 space-y-2 text-sm">
                            <p><span class="font-medium text-gray-600">Name:</span> {{ config('app.name') }}</p>
                            <p><span class="font-medium text-gray-600">Environment:</span> <code class="text-emerald-600">{{ config('app.env') }}</code></p>
                            <p><span class="font-medium text-gray-600">Debug Mode:</span> <code class="text-emerald-600">{{ config('app.debug') ? 'Enabled' : 'Disabled' }}</code></p>
                            <p><span class="font-medium text-gray-600">URL:</span> <code class="text-emerald-600">{{ config('app.url') }}</code></p>
                        </div>
                    </div>

                    <!-- Admin Emails -->
                    <div>
                        <h3 class="text-sm font-semibold text-gray-700 mb-3">Admin Emails</h3>
                        <div class="bg-gray-50 rounded-lg p-4 space-y-2 text-sm">
                            @php
                                $adminEmails = config('app.admin_emails', []);
                            @endphp
                            @foreach($adminEmails as $email)
                                <p>
                                    <svg class="inline w-4 h-4 text-emerald-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    {{ trim($email) }}
                                </p>
                            @endforeach
                            <p class="text-xs text-gray-500 mt-2">Configure in .env: ADMIN_EMAILS</p>
                        </div>
                    </div>

                    <!-- Default Settings -->
                    <div>
                        <h3 class="text-sm font-semibold text-gray-700 mb-3">Default User Settings</h3>
                        <div class="bg-gray-50 rounded-lg p-4 space-y-2 text-sm">
                            <p><span class="font-medium text-gray-600">Default Daily Calories:</span> 2000 kcal</p>
                            <p><span class="font-medium text-gray-600">Default Diet:</span> Omnivore</p>
                            <p class="text-xs text-gray-500 mt-2">Users can customize in their preferences</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function testConnection(service) {
        Alpine.store('testing', true);

        const routes = {
            'vertex-ai': '{{ route('admin.vertex-ai.test') }}',
            'spoonacular': '{{ route('admin.spoonacular.test') }}'
        };

        fetch(routes[service], {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
            },
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('✅ Success!\n\n' + data.message);
            } else {
                alert('❌ Failed\n\n' + data.message);
            }
        })
        .catch(error => {
            alert('❌ Error\n\nConnection test failed: ' + error.message);
        })
        .finally(() => {
            Alpine.store('testing', false);
        });
    }
</script>
@endsection
