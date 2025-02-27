<?php

namespace App\Observers;

use App\Models\orders;
use App\Models\payments;

class PaymentObserver
{
    /**
     * Handle the payments "created" event.
     */
    public function created(payments $payments): void
    {
        //
    }

    /**
     * Handle the payments "updated" event.
     */
    public function updated(payments $payments): void
    {
        $originalMoney = $payments->getOriginal('money');

        // Get the updated money value
        $updatedMoney = $payments->money;

        $order = orders::query()->find($payments->paymentable_id);
        if ($order) {
            // Decode the existing note JSON
            $noteData = json_decode($order->note, true);

            // Ensure the note is an array
            if (! is_array($noteData)) {
                $noteData = [];
                // Update the system_refund key
                $noteData['system_refund'] = (string) ($originalMoney - $updatedMoney);
            } else {
                if (is_numeric($noteData['system_refund'])) {
                    $noteData['system_refund'] = $noteData['system_refund'] + ($originalMoney - $updatedMoney);
                }
            }

            // Update the orders table
            $order->update(['note' => json_encode($noteData, JSON_UNESCAPED_UNICODE)]);
        }

    }

    /**
     * Handle the payments "deleted" event.
     */
    public function deleted(payments $payments): void
    {
        //
    }

    /**
     * Handle the payments "restored" event.
     */
    public function restored(payments $payments): void
    {
        //
    }

    /**
     * Handle the payments "force deleted" event.
     */
    public function forceDeleted(payments $payments): void
    {
        //
    }
}
