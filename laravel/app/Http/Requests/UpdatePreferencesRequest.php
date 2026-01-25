<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePreferencesRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'diet_type' => ['required', Rule::in(['omnivore', 'vegetarian', 'vegan', 'keto'])],
            'daily_calories' => ['required', 'integer', 'min:1000', 'max:5000'],
            'allergies' => ['nullable', 'array'],
            'allergies.*' => ['string', 'max:100'],
            'exclude_ingredients' => ['nullable', 'array'],
            'exclude_ingredients.*' => ['string', 'max:100'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'diet_type.required' => 'Please select a diet type',
            'diet_type.in' => 'Invalid diet type selected',
            'daily_calories.required' => 'Daily calorie target is required',
            'daily_calories.integer' => 'Daily calories must be a whole number',
            'daily_calories.min' => 'Daily calories must be at least 1000',
            'daily_calories.max' => 'Daily calories cannot exceed 5000',
            'allergies.array' => 'Allergies must be provided as a list',
            'exclude_ingredients.array' => 'Excluded ingredients must be provided as a list',
        ];
    }
}
