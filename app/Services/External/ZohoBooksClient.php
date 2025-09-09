<?php

namespace App\Services\External;


use App\Models\orders;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ZohoBooksClient
{
    protected string $region;
    protected string $clientId;
    protected string $clientSecret;
    protected string $redirectUri;
    protected string $orgId;
    protected string $refreshToken;
    protected int $timeout;

    protected string $accountsBase;
    protected string $booksBase;

    public function __construct()
    {
        $cfg = config('services.zohobooks');

        $this->region = $cfg['region'];
        $this->clientId = $cfg['client_id'];
        $this->clientSecret = $cfg['client_secret'];
        $this->redirectUri = $cfg['redirect_uri'];
        $this->refreshToken = $cfg['refresh_token'];
        $this->timeout = (int) ($cfg['timeout']);
        $this->accountsBase = $cfg['accounts_base'];
        $this->booksBase = $cfg['books_base'];
    }

    public function createInvoiceForOrder($order): array|false
    {
        $order->loadMissing([
            'user',
            'payment',
            'items.properties.property',
            'items.service',
        ]);
        $token = $this->getAccessToken();
        if (!$token) {
            return false;
        }
        $contactId = $this->findOrCreateContact($token, $order);
        $payload = $this->buildInvoicePayload($order, $contactId);

        $res = Http::withToken($token)
            ->timeout($this->timeout)
            ->post($this->booksBase.'/invoices', [
                'JSONString' => json_encode($payload, JSON_UNESCAPED_UNICODE),
                'organization_id' => $this->orgId,
            ]);

        if ($res->successful()) {
            $json = $res->json();
            return $json['invoice'] ?? $json;
        }
        return false;
    }

    protected function buildInvoicePayload(orders $order, string $contactId): array
    {
        $lineItems = [];
        foreach ($order->items as $item) {
            if (!is_null($item->is_cancelled)) {
                continue;
            }

            $base = (float) ($item->price ?? 0);
            $propsTotal = 0.0;

            foreach ($item->properties as $prop) {
                $propsTotal += (float) ($prop->price ?? 0);
            }

            $unit = $base + $propsTotal;
            $quantity = max(1, (int) ($item->paper_number ?? 1) * (int) ($item->copies_number ?? 1));
            $serviceStr = $this->getLocalizedLabel($item->service->name ?? null) ?: 'Service #'.$item->service_id;

            $li = [
                'name' => $serviceStr,
                'rate' => round($unit, 2),
                'quantity' => $quantity,
            ];

            $lineItems[] = $li;
        }

        if (empty($lineItems)) {
            $lineItems[] = [
                'name' => 'Order #'.$order->id,
                'rate' => (float) ($order->payment->money ?? 0),
                'quantity' => 1,
            ];
        }

        return [
            'customer_id' => $contactId,
            'reference_number' => (string) $order->id,
            'line_items' => $lineItems,
        ];
    }

    protected function getLocalizedLabel($value): string
    {
        if (is_string($value)) {
            $decoded = json_decode($value, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $lang = app()->getLocale();
                return (string) ($decoded[$lang] ?? reset($decoded) ?? '');
            }
            return $value;
        }
        return (string) $value;
    }

    public function findOrCreateContact(string $token, $order = null): string|false
    {
        $user = $order?->user ?? auth()->user();
        $zohoAccount = $user?->zohoAccount;
        if ($zohoAccount) {
            return $zohoAccount->contact_id;
        }

        $contactPayload = [
            'contact_name' => $user->username,
        ];

        $res = Http::withToken($token)->timeout($this->timeout)
            ->post($this->booksBase.'/contacts', [
                'JSONString' => json_encode($contactPayload, JSON_UNESCAPED_UNICODE),
                'organization_id' => $this->orgId,
            ]);

        if ($res->successful()) {
            $json = $res->json();
            if (!empty($json['contact']['contact_id'])) {
                $user->zohoAccount()->create([
                    'user_id' => $user->id,
                    'contact_id' => $json['contact']['contact_id'],
                ]);
                return $json['contact']['contact_id'];
            }
        }

        return false;
    }

    public function getAccessToken(): string|false
    {
        $cacheKey = 'zoho_access_token';
        if ($token = Cache::get($cacheKey)) {
            return $token;
        }

        // get new access token
        $res = Http::asForm()->timeout($this->timeout)->post($this->accountsBase.'/token', [
            'refresh_token' => $this->refreshToken,
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'grant_type' => 'refresh_token',
        ]);

        if (!$res->successful()) {
            return false;
        }

        $json = $res->json();

        $token = $json['access_token'];
        Cache::put($cacheKey, $token, $json['expires_in'] - 60);
        return $token;
    }
}
