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
        $money = round((float) $this->money, 2);
        $tax = round((float) $this->tax / 100, 2);

        return [
            'id' => $this->id,
            'money' => $money,
            'tax' => $this->tax.'%',
            'tax_money' => round((float) ($money * $tax), 2),
            'money_without_tax' => round((float) ($money - ($money * $tax)), 2),
            'type' => $this->type,
        ];
    }
}
