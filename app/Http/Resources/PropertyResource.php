<?php

namespace App\Http\Resources;

use App\Models\languages;
use App\Services\FormRequestHandleInputs;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PropertyResource extends JsonResource
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
            'property_heading'=>PropertyHeadingResource::make($this->whenLoaded('heading')),
            'property_heading_id'=>$this->property_id_heading,
            'price'=>$this->price,
            'created_at'=>$this->created_at->format('Y-m-d H:i:s'),
        ];
        if(request()->hasHeader('AllLangs')){
            $langs = languages::query()->select('prefix')->get();
            // data
            $name = FormRequestHandleInputs::handle_output_column_for_all_lang('name',$this->name,$langs);
            return array_merge($init,$name);
        }else{
            $data = [
                'name'=>FormRequestHandleInputs::handle_output_column($this->name),
            ];
            return array_merge($init,$data);
        }
    }
}
