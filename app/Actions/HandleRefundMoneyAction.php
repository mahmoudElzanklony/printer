<?php

namespace App\Actions;

class HandleRefundMoneyAction
{
    public static function handle($order, $refund_money)
    {
        if ($order) {
            // Decode the existing note JSON
            $noteData = json_decode($order->note, true);

            // Ensure the note is an array
            if (is_numeric($noteData['system_refund'])) {
                $noteData['system_refund'] = $noteData['system_refund'] + ($refund_money);
            } else {
                $noteData['system_refund'] = (string) ($refund_money);
            }
            // Update the orders table
            $order->update(['note' => json_encode($noteData, JSON_UNESCAPED_UNICODE)]);
        }
    }
}
