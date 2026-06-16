<?php

namespace App\Notifications;

use App\Models\Lpj;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LpjStatusChanged extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Lpj $lpj,
        public string $oldStatus,
        public string $newStatus,
        public string $actionBy,
        public ?string $description = null
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject("LPJ Status Updated: {$this->lpj->tor->activity_name}")
            ->greeting("Hello {$notifiable->full_name},")
            ->line("The status of LPJ '{$this->lpj->tor->activity_name}' has been updated.")
            ->line("**Previous Status:** " . ucwords(str_replace('_', ' ', $this->oldStatus)))
            ->line("**New Status:** " . ucwords(str_replace('_', ' ', $this->newStatus)))
            ->line("**Action By:** {$this->actionBy}");

        // Add description if provided
        if ($this->description) {
            $mail->line("**Note:** {$this->description}");
        }

        $mail->action('View LPJ', url("/api/lpj/{$this->lpj->lpj_id}"))
            ->line('Thank you for using our application!');

        return $mail;
    }

    public function toArray(object $notifiable): array
    {
        return [
            'lpj_id' => $this->lpj->lpj_id,
            'tor_title' => $this->lpj->tor->activity_name,
            'old_status' => $this->oldStatus,
            'new_status' => $this->newStatus,
            'action_by' => $this->actionBy,
            'description' => $this->description,
            'message' => "LPJ '{$this->lpj->tor->activity_name}' status changed from {$this->oldStatus} to {$this->newStatus}",
        ];
    }
}
