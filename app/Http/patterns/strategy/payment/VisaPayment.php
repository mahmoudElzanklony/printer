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
        [$givenName, $surname] = $this->splitName($order->user?->username ?? '');
        $email        = (string) ($order->user?->email ?? '');
        $street1      = (string) ($order->location?->address ?? '');
        $city         = ($this->extractCityName($order) ?? '');
        $state        = ($this->extractStateName($order, $city) ?? '');
        $countryCode  = (string) ($this->extractCountryCode($order) ?? config('hyperpay.default_country', 'SA'));
        $postcode     = (string) ($this->extractPostcode($order) ?? config('hyperpay.default_postcode', ''));

        $checkout = $client->createCheckout([
            'amount'      => $amount,
            'currency'    => config('hyperpay.currency', 'SAR'),
            'paymentType' => 'DB',
            'merchantTransactionId' => (string) $order->id,
            'merchantInvoiceId'     => (string) $order->id,
            'extra'       => [
                'customer.email'     => $email,
                'customer.givenName' => $givenName,
                'customer.surname'   => $surname,

                'billing.street1'    => $street1,
                'billing.city'       => $city,
                'billing.state'      => $state,
                'billing.country'    => strtoupper($countryCode), // Alpha-2
                'billing.postcode'   => $postcode,
            ],
        ]);

        if ($checkout === false || empty($checkout['id'])) {
            return Messages::error(__('errors.payment_failed'));
        }

        $checkoutId = $checkout['id'];
        $scriptBase = config('hyperpay.checkout_js_base', 'https://test.oppwa.com/v1/paymentWidgets.js');

        abort(Messages::success(__('messages.operation_done_successfully'), [
            'provider'    => 'hyperpay',
            'checkoutId'  => $checkoutId,
            'script'      => $scriptBase.'?checkoutId='.$checkoutId,
            'amount'      => $amount,
            'currency'    => config('hyperpay.currency', 'SAR'),
            'order_id'    => $order->id,
        ]));
    }

    private function splitName(string $username): array
    {
        $username = trim($username);
        if ($username === '') {
            return ['Customer', ''];
        }
        $parts = preg_split('/\s+/', $username);
        $given = $parts[0] ?? $username;
        $sur   = implode(' ', array_slice($parts, 1)) ?: '';
        return [$given, $sur];
    }

    private function extractCityName($order): ?string
    {
        $city = $order->location?->area?->city;
        if (! $city) {
            return null;
        }
        $name = $city->name ?? null;
        if (is_string($name)) {
            // Could be plain string or JSON
            $decoded = json_decode($name, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $lang = app()->getLocale();
                return (string) ($decoded[$lang] ?? reset($decoded) ?? '');
            }
            return (string) $name;
        }
        return null;
    }

    private function extractStateName($order, ?string $city): ?string
    {
        return $city ?: null;
    }

    private function extractCountryCode($order): ?string
    {
        $country = $order->location?->area?->city?->country;
        if (! $country) {
            return null;
        }

        // Try common field names that might store ISO alpha-2
        foreach (['iso2', 'alpha2', 'code', 'short_code', 'shortname', 'prefix'] as $field) {
            if (! empty($country->{$field}) && is_string($country->{$field}) && strlen($country->{$field}) <= 3) {
                return strtoupper($country->{$field});
            }
        }

        return null;
    }

    private function extractPostcode($order): ?string
    {
        if (property_exists($order->location ?? (object) [], 'postcode')) {
            return (string) $order->location->postcode;
        }
        return null;
    }
}
