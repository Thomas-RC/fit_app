<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSpoonacularRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Only admins can update Spoonacular settings
        $adminEmails = config('app.admin_emails', []);
        return auth()->check() && in_array(auth()->user()->email, $adminEmails);
    }

    public function rules(): array
    {
        return [
            'api_key' => 'required|string|min:32|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'api_key.required' => 'API key is required.',
            'api_key.min' => 'API key must be at least 32 characters.',
        ];
    }
}
