<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Hash;
use App\Models\Vcard;

class UpdateUserConfirmationCodeRequest extends FormRequest
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
            'current_password' => 'current_password:api',
            'confirmation_code' => 'required|integer|digits:3',
            'confirmation_code_confirm' => 'required|integer|digits:3|same:confirmation_code',
        ];
    }

    public function messages(): array
    {
        return [
            'confirmation_code_confirm.same' => 'Confirmation Code Confirmation should match the Confirmation Code',
        ];
    }
}
