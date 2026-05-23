<?php

namespace App\Notifications;

use App\Models\Act;
use App\Models\Appreciate;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ActAppreciated extends Notification
{
    use Queueable;

    protected Appreciate $appreciate;

    /**
     * Create a new notification instance.
     */
    public function __construct(Appreciate $appreciate)
    {
        $this->appreciate = $appreciate;
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

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        /** @var Act $act */
        $act = $this->appreciate->appreciable;

        return (new MailMessage)
            ->line("Your act of kindness \"{$act->title}\" has been appreciated by {$this->appreciate->user->name}.")
            ->action('Go to Act of Kindness', route('acts.show', $act))
            ->line('Feels good!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        /** @var Act $act */
        $act = $this->appreciate->appreciable;

        return [
            'created_at' => $this->appreciate->created_at,
            'user_id' => $this->appreciate->user_id,
            'act_id' => $this->appreciate->appreciable_id,
            'act_title' => $act->title,
            'id' => $this->appreciate->id,
        ];
    }
}
