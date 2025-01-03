<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class verifyFormRequest extends FormRequest
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
            'otp_secret'=>'required|string',
            'email'=>'nullable|email',
            'phone'=>'nullable|string',
        ];
    }

    public function withValidator($validator){
        $validator->after(function ($validator){
            // Ensure at least one of email or phone is provided
            if (empty($this->input('email')) && empty($this->input('phone'))) {
                $validator->errors()->add('email_or_phone', 'Either email or phone should be provided');
            }
        });
    }
}
