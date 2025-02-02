<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderItemPropertiesResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
          'id'=>$this->id,
          'order_item_id'=>$this->order_item_id,
          'property_id'=>$this->property_id,
          'price'=>$this->price,
          'property'=>PropertyResource::make($this->whenLoaded('property')),
        ];
    }
}
