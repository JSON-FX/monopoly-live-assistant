<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SessionCreateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Only authenticated users can create sessions
        return $this->user() !== null;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // For session creation, all required data comes from authentication context
            // start_time will be set to now(), user_id from authenticated user
            // No additional input validation needed for basic session creation
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            // No specific validation messages needed for empty rules
        ];
    }
}
