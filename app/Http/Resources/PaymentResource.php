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
        $shipment_price = round((float) ($this->shipment_price ?? 0), 2);
        $product_price_with_tax = round((float) ($money - $shipment_price), 2);
        $tax_percentage = (float) $this->tax;
        $tax_rate = $tax_percentage / 100;

        // Calculate price without tax from price that includes tax
        $product_price_without_tax = round((float) ($product_price_with_tax / (1 + $tax_rate)), 2);
        $tax_money = round((float) ($product_price_with_tax - $product_price_without_tax), 2);
        $total_without_tax = round((float) ($product_price_without_tax + $shipment_price), 2);

        return [
            'id' => $this->id,
            'money' => $money,
            'product_price' => $product_price_with_tax,
            'shipment_price' => $shipment_price,
            'tax' => $this->tax.'%',
            'tax_money' => $tax_money,
            'money_without_tax' => $total_without_tax,
            'product_price_without_tax' => $product_price_without_tax,
            'type' => $this->type,
        ];
    }
}
