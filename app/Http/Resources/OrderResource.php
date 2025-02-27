<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
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
            'location' => SavedLocationResource::make($this->whenLoaded('location')),
            'phone_number' => $this->phone_number,
            'status' => OrderStatusResource::make($this->whenLoaded('last_status')),
            'note' => $this->note != null ? json_decode($this->note, true) : $this->note,
            'user' => UserResource::make($this->whenLoaded('user')),
            'rate' => OrderRateResource::make($this->whenLoaded('rate')),
            'items' => OrderItemsResource::collection($this->whenLoaded('items')),
            'tracking' => OrderStatusResource::collection($this->whenLoaded('statues')),
            'coupon_order' => OrderCouponResource::make($this->whenLoaded('coupon_order')),
            'payment' => PaymentResource::make($this->whenLoaded('payment')),
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
        ];
    }
}
