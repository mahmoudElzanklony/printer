<?php

namespace App\Http\patterns\builder;

use App\Actions\HandleRefundMoneyAction;
use App\Http\Enum\OrderStatuesEnum;
use App\Models\orders_items;
use App\Models\taxes;
use App\Services\Messages;

class RemoveOrderItemBuilder
{
    public $order_item;

    private $data;

    private $total_price_item = 0;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function init()
    {
        $allowed = [OrderStatuesEnum::pending->value, OrderStatuesEnum::review->value];
        $this->order_item = orders_items::query()->with(['order' => function ($e) {
            $e->with('last_status', 'payment');
        }, 'properties'])->find($this->data['order_item_id']);
        // check if removed before
        if ($this->order_item->is_cancelled != null) {
            return Messages::error(__('errors.already_removed'));
        }
        // check that order in pending mode or review mode
        if (! (in_array($this->order_item->order->last_status->status->value, $allowed))) {
            return Messages::error(__('errors.order_on_working_mode_you_cant_delete'));
        }

        return true;

    }

    public function detect_full_cost()
    {
        // Calculate base cost (service price + properties prices)
        $base_cost = $this->order_item->price;
        foreach ($this->order_item->properties as $property) {
            $base_cost += $property->price;
        }

        // Calculate total without tax
        $total_without_tax = $base_cost * $this->order_item->paper_number * $this->order_item->copies_number;

        // Get tax percentage and calculate tax amount
        $tax_percentage = taxes::query()->first()->percentage ?? 0;
        $tax_rate = $tax_percentage / 100;
        $tax_amount = $total_without_tax * $tax_rate;

        // Total refund includes tax
        $this->total_price_item = $total_without_tax + $tax_amount;

        return $this;
    }

    public function cancel_item()
    {
        // remove this item
        $this->order_item->update([
            'is_cancelled' => json_encode(['who' => auth()->user()->roleName(), 'reason' => $this->data['reason']], JSON_UNESCAPED_UNICODE),
        ]);

        return $this;
    }

    public function handle_payment()
    {
        HandleRefundMoneyAction::handle($this->order_item->order, $this->total_price_item);

        /*payments::query()->find($this->order_item->order->payment->id)->update([
            'money'=>$this->order_item->order->payment->money - $this->total_price_item
        ]);*/
        return Messages::success(__('messages.saved_successfully'));
    }

    public function cancel_order()
    {

    }
}
