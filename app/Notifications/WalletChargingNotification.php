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
        $channels = ['database', 'mail'];

        if (app()->environment('local')) {
            $channels = ['database'];
        }
        return $channels;
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
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable)
    {

        $difference = $this->user->wallet - $this->old_wallet;
        $amount = abs($difference);
        if ($difference >= 0) {
            $arBody = 'تم شحن رصيد المحفظة بقيمة '.$amount.' و اصبح الرصيد الحالي هو '.$this->user->wallet;
            $enBody = 'The wallet balance has been charged '.$amount.'. The current balance is '.$this->user->wallet;
            $subjectEn = 'The wallet balance has been charged at '.env('APP_NAME');
            $subjectAr = 'تم شحن رصيد المحفظة في '.env('APP_NAME');
        } else {
            $arBody = 'تم خصم رصيد المحفظة بقيمة '.$amount.' و اصبح الرصيد الحالي هو '.$this->user->wallet;
            $enBody = 'The wallet balance has been deducted '.$amount.'. The current balance is '.$this->user->wallet;
            $subjectEn = 'The wallet balance has been deducted at '.env('APP_NAME');
            $subjectAr = 'تم خصم رصيد المحفظة في '.env('APP_NAME');
        }

        SendEmail::send($subjectAr, $arBody, '', '', $this->user->email);

        return (new MailMessage)
            ->subject($subjectEn)
            ->view('emails.email',
                ['details' => ['title' => $subjectEn, 'body' => $enBody, 'link' => '', 'link_msg' => '']]);

    }

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
