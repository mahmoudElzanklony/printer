<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class savedPropertiesFormRequest extends FormRequest
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
            'id'=>'filled|integer',
            'name'=>'required|string',
            'category_id'=>'required|integer|exists:categories,id',
            'properties'=>'required|array',
            'properties.*.id'=>'filled|integer',
            'properties.*.property_id'=>'required|integer|exists:properties,id',
        ];
    }
}
