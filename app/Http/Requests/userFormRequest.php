<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class userFormRequest extends FormRequest
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
        if(auth()->check()) {
            return [
                'email' => 'filled|unique:users,email,' . auth()->id(),
                'username' => 'filled',
                'phone' => 'filled|unique:users,phone,'. auth()->id(),
                'password' => 'filled',
            ];
        }else{
            return [
                'email' => 'required|unique:users,email',
                'username' => 'required',
                'phone' => 'required|unique:users,phone',
                'password' => 'required',
            ];
        }
    }


}
