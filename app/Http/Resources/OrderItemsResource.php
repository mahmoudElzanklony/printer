<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderItemsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'service' => ServiceResource::make($this->service),
            'properties' => OrderItemPropertiesResource::collection($this->whenLoaded('properties')),
            'is_cancelled' => $this->is_cancelled != null ? json_decode($this->is_cancelled, true) : $this->is_cancelled,
            'file' => 'orders_files/'.$this->file,
            'price' => $this->price,
            'price_include_properties' => $this->price + $this->whenLoaded('properties', fn () => $this->properties->sum('price'), 0),
            'paper_number' => $this->paper_number,
            'copies_number' => $this->copies_number,
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
        ];
    }
}
