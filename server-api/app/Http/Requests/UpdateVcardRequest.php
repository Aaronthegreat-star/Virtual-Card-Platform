<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateVcardRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'phone_number' => "required|integer|digits:9|regex:/^9\d{8}$/|exists:vcards,phone_number",
            'name' => 'required|string|min:3|max:255',
            'email' => 'required|email|max:255',
            // 'password' => 'required|string|min:3|max:50',
            // 'confirmation_code' => 'required|integer|digits:4',
            'max_debit' => 'nullable|integer|min:0|max:100000'
        ];
    }
}
