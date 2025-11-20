<?php

namespace App\Notifications;

use App\Models\Tor;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TorStatusChanged extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Tor $tor,
        public string $oldStatus,
        public string $newStatus,
        public string $actionBy
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("TOR Status Updated: {$this->tor->title}")
            ->greeting("Hello {$notifiable->name},")
            ->line("The status of your TOR '{$this->tor->title}' has been updated.")
            ->line("**Previous Status:** {$this->oldStatus}")
            ->line("**New Status:** {$this->newStatus}")
            ->line("**Action By:** {$this->actionBy}")
            ->action('View TOR', url("/tor/{$this->tor->id}"))
            ->line('Thank you for using our application!');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'tor_id' => $this->tor->id,
            'tor_title' => $this->tor->title,
            'old_status' => $this->oldStatus,
            'new_status' => $this->newStatus,
            'action_by' => $this->actionBy,
            'message' => "TOR '{$this->tor->title}' status changed from {$this->oldStatus} to {$this->newStatus}",
        ];
    }
}