<?php

namespace App\Http\patterns\strategy\Messages;

use App\Services\External\SMS\MsegatClient;

class SMSMessages implements MessagesInterface
{

    public function send($data) : bool
    {
        // TODO: Implement send() method.
        $to = $data['user']->phone ?? $data['to'];
        $message = $data['message'] ?? $data['body'];
        $client = new MsegatClient();
        return $client->send($to , $message);
    }
}
