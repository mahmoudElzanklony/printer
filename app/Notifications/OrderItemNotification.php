<?php

namespace App\Notifications;

use App\Services\SendEmail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use NotificationChannels\Fcm\FcmChannel;
use NotificationChannels\Fcm\FcmMessage;
use NotificationChannels\Fcm\Resources\Notification as FcmNotification;

class OrderItemNotification extends Notification implements ShouldBroadcast
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
//        return ['database', 'broadcast', FcmChannel::class];
        return ['database', 'broadcast'];
    }


    public function toDatabase(object $notifiable)
    {

        return [
            'data'=>json_encode(
                [
                    'ar'=>$this->order->order->user->username.'قام بالغاء خدمه رقم '.$this->order->id.' بنجاح من الاوردر التابع له رقم '.$this->order->order->id,
                    'en'=>$this->order->order->user->username.'cancel service successfully and its id is '.$this->order->id.' from order that id is'.$this->order->order->id,
                ],JSON_UNESCAPED_UNICODE),
            'sender'=>auth()->id()
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
            'data'=>json_encode(
                [
                    'ar'=>$this->order->order->user->username.'قام بالغاء خدمه رقم '.$this->order->id.' بنجاح من الاوردر التابع له رقم '.$this->order->order->id,
                    'en'=>$this->order->order->user->username.'cancel service successfully and its id is '.$this->order->id.' from order that id is'.$this->order->order->id,
                ],JSON_UNESCAPED_UNICODE),
            'sender'=>auth()->id()
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
            'data'=>json_encode(
                [
                    'ar'=>$this->order->order->user->username.'قام بالغاء خدمه رقم '.$this->order->id.' بنجاح من الاوردر التابع له رقم '.$this->order->order->id,
                    'en'=>$this->order->order->user->username.'cancel service successfully and its id is '.$this->order->id.' from order that id is'.$this->order->order->id,
                ],JSON_UNESCAPED_UNICODE),
            'sender'=>auth()->id()
        ];
    }

    public function toFcm($notifiable): FcmMessage
    {
        $body_ar = $this->order->order->user->username.' قام بالغاء خدمه رقم '.$this->order->id.' بنجاح من الاوردر التابع له رقم '.$this->order->order->id;
        $body_en = $this->order->order->user->username.' cancel service successfully and its id is '.$this->order->id.' from order that id is '.$this->order->order->id;

        return (new FcmMessage(
            notification: new FcmNotification(
                title: 'إلغاء خدمة',
                body: $body_ar
            )
        ))
            ->data([
                'ar' => $body_ar,
                'en' => $body_en,
            ]);
    }
}
