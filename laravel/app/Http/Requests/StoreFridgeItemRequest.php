<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreFridgeItemRequest extends FormRequest
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
            'product_name' => ['required', 'string', 'max:255'],
            'quantity' => ['nullable', 'numeric', 'min:0', 'max:9999.99'],
            'unit' => ['nullable', 'string', 'max:50'],
            'expires_at' => ['nullable', 'date', 'after:today'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'product_name.required' => 'Product name is required',
            'product_name.max' => 'Product name cannot exceed 255 characters',
            'quantity.numeric' => 'Quantity must be a number',
            'quantity.min' => 'Quantity must be at least 0',
            'quantity.max' => 'Quantity cannot exceed 9999.99',
            'expires_at.date' => 'Expiration date must be a valid date',
            'expires_at.after' => 'Expiration date must be in the future',
        ];
    }
}
