<?php

namespace App\Services\External\Payments;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class HyperpayClient
{
    protected string $baseUrl;
    protected string $accessToken;
    protected string $entityId;
    protected string $currency;
    protected int $timeout;

    public function __construct()
    {
        $cfg = config('services.payments.hyperpay');
        $this->baseUrl = $cfg['baseUrl'];
        $this->accessToken = $cfg['accessToken'];
        $this->entityId = $cfg['entityId'];
        $this->currency = $cfg['currency'] ?? 'SAR';
        $this->timeout = $cfg['timeout'] ?? 15;
    }

    public function createCheckout(array $params) : array | false
    {
        $payload = array_merge([
            'entityId'     => $this->entityId,
            'amount'       => $params['amount'] ?? '0.00',
            'currency'     => $params['currency'] ?? $this->currency,
            'paymentType'  => $params['paymentType'] ?? 'DB',
            'merchantTransactionId' => $params['merchantTransactionId'] ?? '',
            'merchantInvoiceId'     => $params['merchantInvoiceId'] ?? '',
        ], $params['extra'] ?? []);

        try {
            $res = Http::withHeaders([
                'Authorization' => 'Bearer '.$this->accessToken,
            ])->asForm()
                ->timeout($this->timeout)
                ->post($this->baseUrl.'/checkouts', $payload);

            if ($res->successful()) {
                $json = $res->json();
                if (isset($json['id'])) {
                    return $json;
                }
            }

            Log::error('Hyperpay createCheckout error', [
                'status' => $res->status(),
                'body'   => $res->body(),
            ]);
        } catch (\Throwable $e) {
            Log::error('Hyperpay createCheckout exception', ['message' => $e->getMessage()]);
        }

        return false;
    }

    public function getPaymentResultById(string $paymentId): array|false
    {
        try {
            $res = Http::withHeaders([
                'Authorization' => 'Bearer '.$this->accessToken,
            ])->timeout($this->timeout)
                ->get($this->baseUrl.'/payments/'.$paymentId, [
                    'entityId' => $this->entityId,
                ]);

            if ($res->successful()) {
                return $res->json();
            }

            Log::error('Hyperpay getPaymentResultById error', [
                'status' => $res->status(),
                'body'   => $res->body(),
            ]);
        } catch (\Throwable $e) {
            Log::error('Hyperpay getPaymentResultById exception', ['message' => $e->getMessage()]);
        }

        return false;
    }

    public function getPaymentResultByResourcePath(string $resourcePath): array|false
    {
        try {
            $res = Http::withHeaders([
                'Authorization' => 'Bearer '.$this->accessToken,
            ])->timeout($this->timeout)
                ->get($this->baseUrl.$resourcePath, [
                    'entityId' => $this->entityId,
                ]);

            if ($res->successful()) {
                return $res->json();
            }

            Log::error('Hyperpay getPaymentResultByResourcePath error', [
                'status' => $res->status(),
                'body'   => $res->body(),
            ]);
        } catch (\Throwable $e) {
            Log::error('Hyperpay getPaymentResultByResourcePath exception', ['message' => $e->getMessage()]);
        }

        return false;
    }

    public static function isSuccessful(?string $code): bool
    {
        if (! $code) {
            return false;
        }
        // Common Hyperpay success result codes
        $successPrefixes = [
            '000.000.',      // Request successfully processed
            '000.100.110',   // Request successfully processed in 'Merchant in Integrator Test Mode'
            '000.100.111',
            '000.300.000',
        ];
        foreach ($successPrefixes as $p) {
            if (str_starts_with($code, $p)) {
                return true;
            }
        }
        return false;
    }

}
