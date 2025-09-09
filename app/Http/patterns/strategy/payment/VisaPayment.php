<?php

namespace App\Http\patterns\strategy\payment;

use App\Http\Enum\OrderStatuesEnum;
use App\Models\orders;
use App\Services\External\Payments\HyperpayClient;
use App\Services\Messages;

class VisaPayment implements PaymentInterface
{

    public function validate($price)
    {

        // TODO: Implement validate() method.
        $order = orders::query()
            ->where('user_id', auth()->id())
            ->where('status', OrderStatuesEnum::cart)
            ->with('payment')
            ->firstOrFailWithCustomError(__('errors.not_found_data'));
        $amount = number_format((float) $price, 2, '.', '');
        $client = new HyperpayClient();
        $email = (string) ($order->user?->email ?? '');
        $checkout = $client->createCheckout([
            'amount' => $amount,
            'currency' => config('hyperpay.currency', 'SAR'),
            'paymentType' => 'DB',
            'merchantTransactionId' => (string) $order->id,
            'merchantInvoiceId' => (string) $order->id,
            'extra' => [
                'customer.email' => $email,
                'customer.givenName' => $order->user?->username,
                'testMode' => 'EXTERNAL',
                'customParameters' => [
                    '3DS2_enrolled' => true
                ],
                'integrity' => true
            ],
        ]);


        if ($checkout === false || empty($checkout['id'])) {
            return Messages::error(__('errors.payment_failed'));
        }

        $checkoutId = $checkout['id'];

        return [
            'provider' => 'hyperpay',
            'checkoutId' => $checkoutId,
            'amount' => $amount,
            'currency' => config('services.payments.hyperpay.currency', 'SAR'),
            'order_id' => $order->id,
        ];
    }
}
