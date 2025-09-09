<?php

namespace App\Services\External\Payments;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class HyperpayClient
{
    protected string $baseUrl;
    protected string $apiBaseUrl;
    protected string $accessToken;
    protected string $entityId;
    protected string $currency;
    protected int $timeout;

    public function __construct()
    {
        $cfg = config('services.payments.hyperpay');
        $this->apiBaseUrl = $cfg['api_base_url'];
        $this->baseUrl = $cfg['base_url'];
        $this->accessToken = $cfg['access_token'];
        $this->entityId = $cfg['entity_id'];
        $this->currency = $cfg['currency'] ?? 'SAR';
        $this->timeout = $cfg['timeout'] ?? 15;
    }

    public function createCheckout(array $params) : array | false
    {
        $payload = array_merge([
            'entityId'     => $this->entityId,
            'amount'       => $params['amount'] ?? '0.00',
            'currency'     => $params['currency'],
            'paymentType'  => $params['paymentType'] ?? 'DB',
            'merchantTransactionId' => $params['merchantTransactionId'] ?? '',
            'merchantInvoiceId'     => $params['merchantInvoiceId'] ?? '',
        ], $params['extra'] ?? []);

//        dd($payload);


        try {
            $headers = [
                'Authorization' => 'Bearer '.$this->accessToken,
                'entityId'      => $this->entityId,
            ];
            $res = Http::withHeaders($headers)->asForm()
                ->timeout($this->timeout)
                ->post($this->baseUrl.'/checkouts', $payload);
            if ($res->successful()) {
                $json = $res->json();
//                dd($json);
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
                ->get($this->apiBaseUrl.'/payments/'.$paymentId, [
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

//            dd($res->json(),isSucc($res['result']['code'] ?? ''));
            if ($this->isSuccessful($res['result']['code'] ?? '')) {
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

    private function isSuccessful(?string $code): bool
    {
        if (empty($code)) {
            return false;
        }
        return preg_match('/^(000.000.|000.100.1|000.[36]|000.400.[1][12]0)/', $code) === 1;
    }

}
