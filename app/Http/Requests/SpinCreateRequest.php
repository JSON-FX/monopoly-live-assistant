<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SpinCreateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Only authenticated users can create spins
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
            'result' => 'required|string|max:255',
            'bet_amount' => 'required|numeric|min:0',
            'pl' => 'required|numeric',
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
            'result.required' => 'The spin result is required.',
            'result.string' => 'The spin result must be a valid string.',
            'result.max' => 'The spin result may not be greater than 255 characters.',
            'bet_amount.required' => 'The bet amount is required.',
            'bet_amount.numeric' => 'The bet amount must be a valid number.',
            'bet_amount.min' => 'The bet amount must be greater than or equal to 0.',
            'pl.required' => 'The profit/loss amount is required.',
            'pl.numeric' => 'The profit/loss amount must be a valid number.',
        ];
    }
} 