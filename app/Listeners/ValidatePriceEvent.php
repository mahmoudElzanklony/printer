<?php

namespace App\Listeners;

use App\Events\ProceedCartEvent;
use App\Http\Enum\OrderStatuesEnum;
use App\Models\orders_tracking;
use App\Services\Messages;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class ValidatePriceEvent
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(ProceedCartEvent $event)
    {
        $total_price = $event->order->payment->money;
        $validate_payment = $event->payment_obj->validate($total_price);
        if ($validate_payment === true) {
            $event->order->update(['status' => 'working']);
            orders_tracking::query()->create([
                'order_id' => $event->order->id,
                'status' => OrderStatuesEnum::pending
            ]);
            abort(Messages::success(__('messages.saved_successfully')));
        }else{
            return $validate_payment;
        }
    }
}
