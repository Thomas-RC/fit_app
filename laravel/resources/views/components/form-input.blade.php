@props([
    'name',
    'label' => null,
    'type' => 'text',
    'value' => null,
    'placeholder' => '',
    'required' => false,
    'help' => null,
    'error' => null,
])

<div {{ $attributes }}>
    @if($label)
        <label for="{{ $name }}" class="block text-sm font-medium text-gray-700 mb-2">
            {{ $label }}
            @if($required)
                <span class="text-red-500">*</span>
            @endif
        </label>
    @endif

    @if($type === 'textarea')
        <textarea
            name="{{ $name }}"
            id="{{ $name }}"
            placeholder="{{ $placeholder }}"
            {{ $required ? 'required' : '' }}
            class="w-full border border-gray-300 rounded-md px-4 py-2 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 {{ $error ? 'border-red-500' : '' }}"
            rows="4"
        >{{ old($name, $value) }}</textarea>
    @elseif($type === 'select')
        <select
            name="{{ $name }}"
            id="{{ $name }}"
            {{ $required ? 'required' : '' }}
            class="w-full border border-gray-300 rounded-md px-4 py-2 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 {{ $error ? 'border-red-500' : '' }}"
        >
            {{ $slot }}
        </select>
    @else
        <input
            type="{{ $type }}"
            name="{{ $name }}"
            id="{{ $name }}"
            value="{{ old($name, $value) }}"
            placeholder="{{ $placeholder }}"
            {{ $required ? 'required' : '' }}
            class="w-full border border-gray-300 rounded-md px-4 py-2 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 {{ $error ? 'border-red-500' : '' }}"
        >
    @endif

    @if($help)
        <p class="text-xs text-gray-500 mt-1">{{ $help }}</p>
    @endif

    @if($error)
        <p class="text-red-500 text-sm mt-1">{{ $error }}</p>
    @endif

    @error($name)
        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
    @enderror
</div>
