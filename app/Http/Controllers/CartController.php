<?php

namespace App\Http\Controllers;

use App\Actions\OrdersWithAllDataAction;
use App\Actions\VerifyAccess;
use App\Events\ProceedCartEvent;
use App\Filters\EndDateFilter;
use App\Filters\orders\RateOrderFilter;
use App\Filters\orders\StatusOrderFilter;
use App\Filters\StartDateFilter;
use App\Http\Enum\OrderStatuesEnum;
use App\Http\Requests\itemCartFormRequest;
use App\Http\Requests\ordersFormRequest;
use App\Http\Resources\OrderResource;
use App\Models\orders;
use App\Models\orders_items;
use App\Models\payments;
use App\Services\Messages;
use Illuminate\Http\Request;
use Illuminate\Pipeline\Pipeline;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CartController extends OrdersController
{
    //
    public function get_all_data()
    {
        VerifyAccess::execute('pi pi-cart-plus|/orders|read');
        $data = OrdersWithAllDataAction::get(false);
        $output  = app(Pipeline::class)
            ->send($data)
            ->through([
                StartDateFilter::class,
                EndDateFilter::class,
                StatusOrderFilter::class,
                RateOrderFilter::class
            ])
            ->thenReturn()
            ->paginate(request('limit') ?? 10);
        return OrderResource::collection($output);
    }

    public function store(ordersFormRequest $request){
        return $this->create($request,'cart');
    }

    public function check_item_related_to_user($id)
    {
        $check = orders_items::query()->with(['properties','order.payment'])
            ->whereHas('order.user', function($q) {
                $q->where('id', auth()->id());
            })
            ->find($id);
        if(!$check){
            abort('This item doesnt exist or does not belong to you');
        }
        return $check;
    }

    public function update_item(itemCartFormRequest $request){
        DB::beginTransaction();
        $data = $request->validated();
        $item = $this->check_item_related_to_user($data['id']);
        $total_properties_price = 0;
        // get properties that related to this order
        foreach($item->properties as $property){
            $total_properties_price += $property->price;
        }
        $old = ($total_properties_price + $item->price) * $item->paper_number * $item->copies_number;
        $remain = ($total_properties_price + $item->price) * $data['paper_number'] * $data['copies_number'] - $old;

        $item->update($data);
        // update payment
        $item->order->payment->update(['money' => $item->order->payment->money + $remain]);
        DB::commit();
        return Messages::success(__('messages.saved_successfully'));
    }

    public function proceed_cart()
    {

        return ProceedCartEvent::dispatch($this->payment_obj);
    }
}
