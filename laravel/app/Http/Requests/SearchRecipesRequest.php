<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SearchRecipesRequest extends FormRequest
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
            'query' => ['nullable', 'string', 'max:255'],
            'diet' => ['nullable', 'string', 'max:50'],
            'maxCalories' => ['nullable', 'integer', 'min:0', 'max:3000'],
            'cuisine' => ['nullable', 'string', 'max:50'],
            'ingredients' => ['nullable', 'array'],
            'ingredients.*' => ['string', 'max:100'],
            'number' => ['nullable', 'integer', 'min:1', 'max:50'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'query.max' => 'Search query cannot exceed 255 characters',
            'maxCalories.integer' => 'Max calories must be a number',
            'maxCalories.max' => 'Max calories cannot exceed 3000',
            'number.min' => 'Number of results must be at least 1',
            'number.max' => 'Number of results cannot exceed 50',
        ];
    }
}
