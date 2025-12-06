<?php

namespace App\Notifications;

use App\Services\SendEmail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use NotificationChannels\Fcm\FcmChannel;
use NotificationChannels\Fcm\FcmMessage;
use NotificationChannels\Fcm\Resources\Notification as FcmNotification;

class OrderNotification extends Notification implements ShouldBroadcast
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    private $order;

    public function __construct($order)
    {
        //
        $this->order = $order;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'broadcast', FcmChannel::class];
    }

    public function toDatabase(object $notifiable)
    {

        return [
            'data' => json_encode(
                [
                    'ar' => $this->order->user->username.' قام بأنشاء اوردر رقم '.$this->order->id.' في عنوان '.$this->order->location->address,
                    'en' => $this->order->user->username.' made an order with id : '.$this->order->id.' and order address is '.$this->order->location?->address,
                ], JSON_UNESCAPED_UNICODE),
            'sender' => auth()->id(),
        ];
    }

    /**
     * Get the broadcast representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toBroadcast(object $notifiable): array
    {
        return [
            'data' => json_encode(
                [
                    'ar' => $this->order->user->username.' قام بأنشاء اوردر رقم '.$this->order->id.' في عنوان '.$this->order->location->address,
                    'en' => $this->order->user->username.' made an order with id : '.$this->order->id.' and order address is '.$this->order->location?->address,
                ], JSON_UNESCAPED_UNICODE),
            'sender' => auth()->id(),
        ];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'data' => json_encode(
                [
                    'ar' => $this->order->user->username.' قام بأنشاء اوردر رقم '.$this->order->id.' في عنوان '.$this->order->location->address,
                    'en' => $this->order->user->username.' made an order with id : '.$this->order->id.' and order address is '.$this->order->location?->address,
                ], JSON_UNESCAPED_UNICODE),
            'sender' => auth()->id(),
        ];
    }

    public function toFcm($notifiable): FcmMessage
    {
        $body_ar = $this->order->user->username.' قام بأنشاء اوردر رقم '.$this->order->id.' في عنوان '.$this->order->location->address;
        $body_en = $this->order->user->username.' made an order with id : '.$this->order->id.' and order address is '.$this->order->location?->address;

        return (new FcmMessage(
            notification: new FcmNotification(
                title: 'طلب جديد',
                body: $body_ar
            )
        ))
            ->data([
                'ar' => $body_ar,
                'en' => $body_en,
            ]);
    }
}
