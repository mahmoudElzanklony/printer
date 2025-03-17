<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentResource extends JsonResource
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
            'money' => $this->money,
            'tax' => $this->tax,
            'tax_money' => ($this->money * $this->tax / 100),
            'money_with_tax' => $this->money + ($this->money * $this->tax / 100),
            'type' => $this->type,
        ];
    }
}
