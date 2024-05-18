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
    public function __construct($user , $old_wallet)
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
        return ['database','mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toDatabase(object $notifiable)
    {

        return [
            'data'=>json_encode(
                [
                    'ar'=>'تم شحن رصيد المحفظه بقيمه '.$this->user->wallet - $this->old_wallet.' و اصبح الرصيد الحالي هو '.$this->user->wallet,
                    'en'=>'The wallet balance has been charged '.$this->user->wallet - $this->old_wallet.' The current balance becomes '.$this->user->wallet,
                ],JSON_UNESCAPED_UNICODE),
            'sender'=>auth()->id()
        ];
    }
    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable)
    {
        $oldWallet = $this->old_wallet; // Access the old wallet value
        $newWallet = $this->user->wallet; // Access the current wallet value
        $amountCharged = $newWallet - $oldWallet; // Calculate the charged amount

        $subject = [
            'en' => 'The wallet balance has been charged at ' . env('APP_NAME'),
            'ar' => 'تم شحن رصيد المحفظه في ' . env('APP_NAME'),
        ];

        $message = [
            'en' => 'The wallet balance has been charged ' . $amountCharged . '. The current balance becomes ' . $newWallet,
            'ar' => 'تم شحن رصيد المحفظه بقيمه ' . $amountCharged . ' و اصبح الرصيد الحالي هو ' . $newWallet,
        ];

        // Determine user's preferred language (assuming a mechanism exists)
        $userLocale = app()->getLocale(); // Example using Laravel's localization

        $details = [
            'title' => $subject[$userLocale],
            'body' => $message[$userLocale],
            'link'=>'','link_msg'=>''
            // Add other details for your custom email content here
        ];
        \Mail::to($this->user->email)->send(new Myemail($details));
        //SendEmail::send('تم شحن رصيد المحفظه في '.env('APP_NAME'),'تم شحن رصيد المحفظه بقيمه '.$this->user->wallet - $this->old_wallet.' و اصبح الرصيد الحالي هو '.$this->user->wallet,'','',$this->user->email);
        //SendEmail::send('The wallet balance has been charged at '.env('APP_NAME'),'The wallet balance has been charged '.$this->user->wallet - $this->old_wallet.' The current balance becomes '.$this->user->wallet,'','',$this->user->email);
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
