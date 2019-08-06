<?php

namespace App\Notifications;

use App\Game;
use App\Payment;
use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Collection;

class GameConfirmed extends Notification
{
    use Queueable;

    public $user;
    public $game;
    public $unconfirmed_players;
    public $confirmed_players;

    /**
     * Create a new notification instance.
     *
     * @param User $user
     * @param Game $game
     * @param Collection User $unconfirmed_players
     * @param Collection User $confirmed_players
     */
    public function __construct(User $user, Game $game, $unconfirmed_players, $confirmed_players)
    {
        $this->user = $user;
        $this->game = $game;
        $this->unconfirmed_players = $unconfirmed_players;
        $this->confirmed_players = $confirmed_players;
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
        $unconfirmed_players = "";
        $confirmed_players = "";
        foreach ($this->unconfirmed_players as $player) {
            $unconfirmed_players .= $player->fullname." \n";
        }
        foreach ($this->confirmed_players as $player) {
            $confirmed_players .= $player->fullname." \n";
        }
        return (new MailMessage)
            ->line("{$this->user->fullname} just confirmed your game together at {$this->game->club->name} 
                    starting from {$this->game->start->format('d M Y H:i')} and ending at {$this->game->end->format('H:i')}.")
            ->line("Players Confirmed : {$this->confirmed_players->count()}")
            ->line($confirmed_players)
            ->line("Players Pending : {$this->unconfirmed_players->count()}")
            ->line($unconfirmed_players)
            ->line("Thank you for using ".config('app.name')."!");
    }

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
