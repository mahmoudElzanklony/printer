<?php

namespace App\Http\Resources;

use App\Models\languages;
use App\Services\FormRequestHandleInputs;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ShipmentPriceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {

        $init = [
            'id'=>$this->id,
            'user_id'=>$this->user_id,
            'city_id'=>$this->city_id,
            'price'=>$this->price,
            'user'=>UserResource::make($this->whenLoaded('user')),
            'city'=>CityResource::make($this->whenLoaded('city')),
            'created_at'=>$this->created_at->format('Y-m-d H:i:s'),
        ];
        if(request()->hasHeader('AllLangs')){
            $langs = languages::query()->select('prefix')->get();
            // data
            $name = FormRequestHandleInputs::handle_output_column_for_all_lang('area',$this->area,$langs);
            return array_merge($init,$name);
        }else{
            $data = [
                'area'=>FormRequestHandleInputs::handle_output_column($this->area),
            ];
            return array_merge($init,$data);
        }

    }
}
