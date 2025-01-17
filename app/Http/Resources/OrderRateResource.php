<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderRateResource extends JsonResource
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
          'print_rate'=>$this->print_rate,
          'delivery_rate'=>$this->delivery_rate,
          'comment'=>$this->comment,
          'order'=>OrderResource::make($this->whenLoaded('order')),
          'created_at'=>$this->created_at->format('Y-m-d H:i:s'),
        ];
    }
}
