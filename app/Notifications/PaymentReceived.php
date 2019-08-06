<?php

namespace App\Notifications;

use App\Game;
use App\Payment;
use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class PaymentReceived extends Notification
{
    use Queueable;

    public $user;
    public $game;

    /**
     * Create a new notification instance.
     *
     * @param User $user
     * @param Game $game
     * @param Payment $payment
     */
    public function __construct(User $user, Game $game,Payment $payment)
    {
        $this->user = $user;
        $this->game = $game;
        $this->payment = $payment;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->greeting("Dear {$this->user->fullname}")
            ->line("Your payment of KES ". number_format($this->payment->amount)." for account number {$this->payment->account} has been received successfully.")
            ->line("Your game at {$this->game->club->name} starts from {$this->game->start->format('d M Y H:i')} and ends at
                    {$this->game->end->format('H:i')}.")
            ->line("Thank you for using ".config('app.name')."!");
    }


//    public function toSms($notifiable)
//    {
//        return $this->getTemplateFor(
//            'invitation',
//            'sms',
//            ['user' => $this->user, 'loan_limit' => $this->loan_limit]);
//    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
