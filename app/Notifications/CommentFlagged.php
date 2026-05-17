<?php

namespace App\Notifications;

use App\Models\Flag;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CommentFlagged extends Notification
{
    use Queueable;

    protected Flag $flag;

    /**
     * Create a new notification instance.
     */
    public function __construct(Flag $flag)
    {
        $this->flag = $flag;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->error()
            ->line('Your item was flagged.')
            ->action('Review', route('flags.show', $this->flag))
            ->line('You should review it and possible change your mind');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'created_at' => $this->flag->created_at,
            'comment' => $this->flag->comment,
            'id' => $this->flag->id,
        ];
    }
}
