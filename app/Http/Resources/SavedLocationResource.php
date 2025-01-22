<?php

namespace App\Http\Resources;

use App\Models\languages;
use App\Services\FormRequestHandleInputs;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SavedLocationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {

        return  [
            'id'=>$this->id,
            'user_id'=>$this->user_id,
            'area_id'=>$this->area_id,
            'name'=>$this->name,
            'latitude'=>$this->latitude,
            'longitude'=>$this->longitude,
            'address'=>$this->address,
            'user'=>UserResource::make($this->whenLoaded('user')),
            'area'=>ShipmentPriceResource::make($this->whenLoaded('area')),
            'is_default'=>$this->is_default,
            'created_at'=>$this->created_at->format('Y-m-d H:i:s'),
        ];

    }
}
