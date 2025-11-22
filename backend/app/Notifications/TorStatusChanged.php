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
        public string $actionBy,
        public ?string $description = null  // ← Tambah parameter ke-5 (optional)
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject("TOR Status Updated: {$this->tor->activity_name}")
            ->greeting("Hello {$notifiable->full_name},")
            ->line("The status of your TOR '{$this->tor->activity_name}' has been updated.")
            ->line("**Previous Status:** " . ucwords(str_replace('_', ' ', $this->oldStatus)))
            ->line("**New Status:** " . ucwords(str_replace('_', ' ', $this->newStatus)))
            ->line("**Action By:** {$this->actionBy}");

        // Add description if provided
        if ($this->description) {
            $mail->line("**Note:** {$this->description}");
        }

        $mail->action('View TOR', url("/api/tor/{$this->tor->tor_id}"))
            ->line('Thank you for using our application!');

        return $mail;
    }

    public function toArray(object $notifiable): array
    {
        return [
            'tor_id' => $this->tor->tor_id,
            'tor_title' => $this->tor->activity_name,
            'old_status' => $this->oldStatus,
            'new_status' => $this->newStatus,
            'action_by' => $this->actionBy,
            'description' => $this->description,
            'message' => "TOR '{$this->tor->activity_name}' status changed from {$this->oldStatus} to {$this->newStatus}",
        ];
    }
}
