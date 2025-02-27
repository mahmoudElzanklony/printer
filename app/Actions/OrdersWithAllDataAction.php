<?php

namespace App\Actions;

use App\Http\Enum\OrderStatuesEnum;
use App\Models\orders;

class OrdersWithAllDataAction
{
    public static function get($is_order = true)
    {
        return orders::query()->with('payment')
            ->when($is_order == false, function ($query) {
                $query->where('status', OrderStatuesEnum::cart);
            })
            ->with(['coupon_order.coupon', 'statues', 'items', 'rate'])
            //->when($is_order == false, function ($query) {$query->with('payment');})
            ->when(auth()->user()->roleName() != 'client',
                fn ($e) => $e->with('user'))
            ->when(auth()->user()->roleName() == 'client',
                fn ($e) => $e->where('user_id', auth()->id()))
            ->orderBy('id', 'DESC');
    }
}
