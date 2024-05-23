<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class UserRegisteryNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    private $user;
    private $is_client = false;
    public function __construct($user,$is_client)
    {
        //
        $this->user = $user;
        $this->is_client = $is_client;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }


    public function toDatabase(object $notifiable)
    {
        if($this->is_client){
            return [
                'data' => json_encode(['ar' => 'عملية التسجيل الخاصه بك في '.env('PrinterServices').' تمت بنجاح ورقم التفعيل الخاص بك هو '.$this->user->otp_secret,
                    'en' =>  'Register process done successfully at '.env('PrinterServices').' and your otp number is '.$this->user->otp_secret], JSON_UNESCAPED_UNICODE),
                'sender' => $this->user->id
            ];
        }else {
            return [
                'data' => json_encode(['ar' => $this->user->username . ' قام بالتسجيل بنجاح الي المنصة ', 'en' => $this->user->username . ' registered in our app'], JSON_UNESCAPED_UNICODE),
                'sender' => $this->user->id
            ];
        }
    }
    /**
     * Get the mail representation of the notification.
     */

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
