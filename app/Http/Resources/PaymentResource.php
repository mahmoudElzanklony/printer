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
        $money = number_format((float) $this->money, 2, '.', '');
        $tax = number_format((float) $this->tax / 100, 2, '.', '');

        return [
            'id' => $this->id,
            'money' => $money,
            'tax' => $this->tax,
            'tax_money' => ($money * $tax),
            'money_without_tax' => $money - ($money * $tax),
            'type' => $this->type,
        ];
    }
}
