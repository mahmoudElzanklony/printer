<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class savedLocationFormRequest extends FormRequest
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
           'id'=>'filled',
           'name'=>'required',
           'country_id'=>'filled|exists:countries,id',
           'city_id'=>'filled|exists:cities,id',
           'area_id'=>'required|exists:shipment_prices,id',
           'longitude'=>'required|string',
           'latitude'=>'required|string',
           'address'=>'required|string',
           'is_default'=>'required',
        ];
    }
}
