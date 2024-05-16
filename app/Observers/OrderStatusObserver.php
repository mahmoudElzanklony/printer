<?php

namespace App\Observers;

use App\Models\orders_tracking;
use App\Notifications\OrderStatusNotification;
use App\Notifications\UserRegisteryNotification;

class OrderStatusObserver
{
    /**
     * Handle the orders_tracking "created" event.
     */
    public function created(orders_tracking $orders_tracking): void
    {
        auth()->user()->notify(new OrderStatusNotification($orders_tracking));
    }

    /**
     * Handle the orders_tracking "updated" event.
     */
    public function updated(orders_tracking $orders_tracking): void
    {
        //
    }

    /**
     * Handle the orders_tracking "deleted" event.
     */
    public function deleted(orders_tracking $orders_tracking): void
    {
        //
    }

    /**
     * Handle the orders_tracking "restored" event.
     */
    public function restored(orders_tracking $orders_tracking): void
    {
        //
    }

    /**
     * Handle the orders_tracking "force deleted" event.
     */
    public function forceDeleted(orders_tracking $orders_tracking): void
    {
        //
    }
}
