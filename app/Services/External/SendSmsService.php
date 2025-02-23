<?php

namespace App\Services\External;

use GuzzleHttp\Client;

class SendSmsService
{
    public function __construct()
    {
        $this->client = new Client();
    }

    public function send($numbers, $message)
    {
        $url = 'https://www.msegat.com/gw/sendsms.php';
        $response = $this->client->post($url, [
            'userName' => env('SMS_API_USERNAME'),
            'userSender' => env('SMS_API_SENDER'),
            'numbers' => $numbers,
            'apiKey' => env('SMS_API_KEY'),
            'msg' => $message,

        ]);
    }
}
