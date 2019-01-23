<?php

namespace App\Broadcasting;

use App\Jobs\SendSms;
use Illuminate\Notifications\Notification;

class SmsChannel
{
 /**
     * Send the given notification.
     *
     * @param  mixed  $notifiable
     * @param  \Illuminate\Notifications\Notification  $notification
     * @return void
     */
    public function send($notifiable, Notification $notification)
    {
        $message = $notification->toSms($notifiable);

        dispatch(new SendSms($notifiable->phone, $message));
    }
}
