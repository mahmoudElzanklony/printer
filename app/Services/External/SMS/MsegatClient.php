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
        if ($to == '' || $message == '') {
            return false;
        }

        $payload = [
            'userName' => $this->username,
            'numbers' => $to,
            'userSender' => $this->sender,
            'apiKey' => $this->apiKey,
            'msg' => $message,
        ];

        if (!empty($option)) {
            $payload = array_merge($payload, $options);
        }

        try {
            $response = Http::asForm()
                ->accept('application/json')
                ->timeout($this->timeout)
                ->post($this->baseUrl, $payload);
            $body = json_decode($response->body(), true);
            if ($body['code'] === 'M0000') { // M000 is success code in msegat service
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
        $number = trim($number);
        $number = preg_replace('/[^\d\+]/', '', $number);

        // convert leading 00 to +
        if (str_starts_with($number, '00')) {
            $number = '+'.substr($number, 2);
        }


        if (str_starts_with($number, '+')) {
            $number = substr($number, 1);
        }

        return $number;
    }
}
