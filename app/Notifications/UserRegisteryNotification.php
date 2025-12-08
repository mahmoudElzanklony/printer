<?php

namespace App\Notifications;

use App\Models\User;
use App\Services\SendEmail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use NotificationChannels\Fcm\FcmChannel;
use NotificationChannels\Fcm\FcmMessage;
use NotificationChannels\Fcm\Resources\Notification as FcmNotification;

class UserRegisteryNotification extends Notification implements ShouldBroadcast
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    private $user;
    private $is_client = false;

    public function __construct($user,$is_client = false)
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
//        return ['database', 'broadcast', FcmChannel::class];
        return ['database', 'broadcast'];
    }


    public function toDatabase(object $notifiable)
    {
        if($this->is_client){
            return [
                'data' => json_encode(['ar' => 'تمت عملية التسجيل بنجاح في '.env('APP_NAME').'تمت عملية التسجيل الخاصه بك بنجاح و رقم التفعيل هو '.$this->user->otp_secret,
                    'en' =>  'Register process done successfully at '.env('APP_NAME').' and your otp number is '.$this->user->otp_secret], JSON_UNESCAPED_UNICODE),
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
     * Get the broadcast representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toBroadcast(object $notifiable): array
    {
        if($this->is_client){
            return [
                'data' => json_encode(['ar' => 'تمت عملية التسجيل بنجاح في '.env('APP_NAME').'تمت عملية التسجيل الخاصه بك بنجاح و رقم التفعيل هو '.$this->user->otp_secret,
                    'en' =>  'Register process done successfully at '.env('APP_NAME').' and your otp number is '.$this->user->otp_secret], JSON_UNESCAPED_UNICODE),
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
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        if($this->is_client){
            return [
                'data' => json_encode(['ar' => 'تمت عملية التسجيل بنجاح في '.env('APP_NAME').'تمت عملية التسجيل الخاصه بك بنجاح و رقم التفعيل هو '.$this->user->otp_secret,
                    'en' =>  'Register process done successfully at '.env('APP_NAME').' and your otp number is '.$this->user->otp_secret], JSON_UNESCAPED_UNICODE),
                'sender' => $this->user->id
            ];
        }else {
            return [
                'data' => json_encode(['ar' => $this->user->username . ' قام بالتسجيل بنجاح الي المنصة ', 'en' => $this->user->username . ' registered in our app'], JSON_UNESCAPED_UNICODE),
                'sender' => $this->user->id
            ];
        }
    }

    public function toFcm($notifiable): FcmMessage
    {
        if($this->is_client){
            $body_ar = 'تمت عملية التسجيل بنجاح في '.env('APP_NAME').' تمت عملية التسجيل الخاصه بك بنجاح و رقم التفعيل هو '.$this->user->otp_secret;
            $body_en = 'Register process done successfully at '.env('APP_NAME').' and your otp number is '.$this->user->otp_secret;
            $title = 'تسجيل جديد';
        } else {
            $body_ar = $this->user->username . ' قام بالتسجيل بنجاح الي المنصة';
            $body_en = $this->user->username . ' registered in our app';
            $title = 'مستخدم جديد';
        }

        return (new FcmMessage(
            notification: new FcmNotification(
                title: $title,
                body: $body_ar
            )
        ))
            ->data([
                'ar' => $body_ar,
                'en' => $body_en,
            ]);
    }
}
