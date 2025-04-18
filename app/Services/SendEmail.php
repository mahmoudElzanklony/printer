<?php

namespace App\Services;

use App\Mail\Myemail;

class SendEmail
{
    public static function send($title, $body, $link = '', $link_msg = '', $to = '')
    {
        $details = [
            'title' => $title,
            'body' => $body,
            'link' => $link,
            'link_msg' => $link_msg,
        ];
        if ($to != '' && env('MAIL_STATUS') != 'local') {
            \Mail::to($to)->send(new Myemail($details));
        }

    }
}
