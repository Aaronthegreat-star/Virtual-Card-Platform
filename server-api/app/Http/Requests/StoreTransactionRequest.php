<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Vcard;

class StoreTransactionRequest extends FormRequest {
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array {

        if ($this->custom_options != null){
            $rulesRef = ['regex:/^9\d{8}$/', 'exists:vcards,phone_number'];
            $valueRules = ['required', 'numeric', 'regex:/^\d{0,9}(\.\d{1,3})?$/'];
        }else{
                
            switch($this->payment_type) {
                case 'VCARD':
                    $rulesRef = ['regex:/^9\d{8}$/', 'exists:vcards,phone_number'];
                    $maxDebit = Vcard::where('phone_number', '=', $this->vcard)->value('max_debit');
                    $limitRules = ['max:' . $maxDebit];
                    break;
                case 'MBWAY':
                    $rulesRef = ['regex:/^9\d{8}$/'];
                    $limitRules = ['max:50'];
                    break;
                case 'PAYPAL':
                    $rulesRef = ['email'];
                    $limitRules = ['max:100'];
                    break;
                case 'IBAN':
                    $rulesRef = ['regex:/^[A-Za-z]{2}\d{23}$/'];
                    $limitRules = ['max:1000'];
                    break;
                case 'MB':
                    $rulesRef = ['regex:/^\d{5}-\d{9}$/'];
                    $limitRules = ['max:500'];
                    break;
                case 'VISA':
                    $rulesRef = ['regex:/^4\d{15}$/'];
                    $limitRules = ['max:200'];
                    break;
                default:
                    $rulesRef = [];
                    $limitRules = [];
                    break;
            }

            $valueRules = ['required', 'numeric', 'regex:/^\d{0,9}(\.\d{1,3})?$/',...$limitRules];

        }




        return [
            'vcard' => 'required|integer|digits:9|regex:/^9\d{8}$/',
            'value' => $valueRules,
            'type' => 'required|in:C,D',
            'payment_type' => 'required|in:VCARD,MBWAY,PAYPAL,IBAN,MB,VISA',
            'payment_reference' => ['required', ...$rulesRef],
            'category_id' => 'nullable|integer',
            'description' => 'nullable|string|max:255',
            'custom_options' => 'nullable|string'
        ];
    }
}
