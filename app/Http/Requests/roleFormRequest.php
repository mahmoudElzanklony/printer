<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class roleFormRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {

        return auth()->check() && auth()->user()->hasRole('admin');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {

        return [
            'id'=>'filled',
            'name' => 'required|unique:roles,name,'.request()->segment(3),
            'permissions' => 'required|array',
            'permissions.*' => 'required|exists:permissions,id'
        ];
    }
}
