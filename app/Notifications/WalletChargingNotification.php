<?php

namespace App\Notifications;

use App\Mail\Myemail;
use App\Services\SendEmail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WalletChargingNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    private $user;
    private $old_wallet;

    public function __construct($user, $old_wallet)
    {
        //
        $this->user = $user;
        $this->old_wallet = $old_wallet;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toDatabase(object $notifiable)
    {

        $difference = $this->user->wallet - $this->old_wallet;
        $amount = abs($difference);
        if ($difference >= 0) {
            $ar = 'تم شحن رصيد المحفظة بقيمة '.$amount.' و اصبح الرصيد الحالي هو '.$this->user->wallet;
            $en = 'The wallet balance has been charged '.$amount.'. The current balance is '.$this->user->wallet;
        } else {
            $ar = 'تم خصم رصيد المحفظة بقيمة '.$amount.' و اصبح الرصيد الحالي هو '.$this->user->wallet;
            $en = 'The wallet balance has been deducted '.$amount.'. The current balance is '.$this->user->wallet;
        }

        return [
            'data' => json_encode(
                [
                    'ar' => $ar,
                    'en' => $en,
                ], JSON_UNESCAPED_UNICODE),
            'sender' => auth()->id()
        ];
    }

    /**
     * Get the broadcast representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toBroadcast(object $notifiable): array
    {
        $difference = $this->user->wallet - $this->old_wallet;
        $amount = abs($difference);
        if ($difference >= 0) {
            $ar = 'تم شحن رصيد المحفظة بقيمة '.$amount.' و اصبح الرصيد الحالي هو '.$this->user->wallet;
            $en = 'The wallet balance has been charged '.$amount.'. The current balance is '.$this->user->wallet;
        } else {
            $ar = 'تم خصم رصيد المحفظة بقيمة '.$amount.' و اصبح الرصيد الحالي هو '.$this->user->wallet;
            $en = 'The wallet balance has been deducted '.$amount.'. The current balance is '.$this->user->wallet;
        }

        return [
            'data' => json_encode(
                [
                    'ar' => $ar,
                    'en' => $en,
                ], JSON_UNESCAPED_UNICODE),
            'sender' => auth()->id()
        ];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $difference = $this->user->wallet - $this->old_wallet;
        $amount = abs($difference);
        if ($difference >= 0) {
            $ar = 'تم شحن رصيد المحفظة بقيمة '.$amount.' و اصبح الرصيد الحالي هو '.$this->user->wallet;
            $en = 'The wallet balance has been charged '.$amount.'. The current balance is '.$this->user->wallet;
        } else {
            $ar = 'تم خصم رصيد المحفظة بقيمة '.$amount.' و اصبح الرصيد الحالي هو '.$this->user->wallet;
            $en = 'The wallet balance has been deducted '.$amount.'. The current balance is '.$this->user->wallet;
        }

        return [
            'data' => json_encode(
                [
                    'ar' => $ar,
                    'en' => $en,
                ], JSON_UNESCAPED_UNICODE),
            'sender' => auth()->id()
        ];
    }
}
