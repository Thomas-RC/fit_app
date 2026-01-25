<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateVertexAIRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Will be further checked by admin middleware
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'credentials_file' => ['required', 'file', 'mimes:json', 'max:10240'], // 10MB max
            'project_id' => ['required', 'string', 'max:255'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'credentials_file.required' => 'Please upload the service account JSON file',
            'credentials_file.file' => 'The credentials must be a file',
            'credentials_file.mimes' => 'The credentials file must be in JSON format',
            'credentials_file.max' => 'The credentials file size cannot exceed 10MB',
            'project_id.required' => 'Google Cloud Project ID is required',
            'project_id.max' => 'Project ID cannot exceed 255 characters',
        ];
    }
}
