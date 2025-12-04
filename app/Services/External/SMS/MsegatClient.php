<?php

namespace App\Services\External\SMS;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MsegatClient
{
    protected $username;
    protected $apiKey;
    protected $sender;
    protected $baseUrl;
    protected $timeout;

    public function __construct()
    {
        $cfg = config('services.sms.msegat');
        $this->username = $cfg['username'];
        $this->apiKey = $cfg['api_key'];
        $this->sender = $cfg['sender'];
        $this->baseUrl = $cfg['base_url'];
        $this->timeout = $cfg['timeout'] ?? 10;
    }

    public function send(string $to, string $message, array $options = [])
    {
        $to = $this->normalize_number($to);
        if ($to == '' || $message == '' || !str_starts_with($to, '966')) {
            return false;
        }

//        $message = "رمز التحقق: 1234";
        $payload = [
            'userName' => $this->username,
            'apiKey' => $this->apiKey,
            'numbers' => $to,
            'userSender' => $this->sender,
            'msg' => $message,
            'lang' => 'Ar',
            'msgEncoding' => 'UTF8',
        ];

        if (!empty($option)) {
            $payload = array_merge($payload, $options);
        }
        try {
            $response = Http::asJson()
                ->accept('application/json')
                ->timeout($this->timeout)
                ->post($this->baseUrl, $payload);

            $body = $response->json();
            if ($body['code'] === 'M0000' || $body['code'] === '1') { // M000 is success code in msegat service
                return true;
            }
            return false;
        } catch (\Throwable $e) {
            Log::error('Msegat SMS Exception', [
                'message' => $e->getMessage(),
            ]);
        }
        return false;
    }

    function normalize_number(string $number)
    {
        $numbers = explode(',', $number);
        $normalized = array_filter(array_map(function ($num) {  // msegat supports bulk sending with comma separation
            $num = trim($num);
            $num = preg_replace('/[^\d\+]/', '', $num);

            // convert leading 00 to +
            if (str_starts_with($num, '00')) {
                $num = '+'.substr($num, 2);
            }

            if (str_starts_with($num, '+')) {
                $num = substr($num, 1);
            }

            return $num;
        }, $numbers), function ($num) {
            return str_starts_with($num, '966');
        });

        return implode(',', $normalized);
    }
}
