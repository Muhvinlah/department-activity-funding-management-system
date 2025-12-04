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
            ->subject("Status Dokumen TOR diperbarui: {$this->tor->activity_name}")
            ->greeting("Halo {$notifiable->full_name},")
            ->line("Status dokumen TOR '{$this->tor->activity_name}' telah diperbarui.")
            ->line("**Status sebelumnya:** " . ucwords(str_replace('_', ' ', $this->oldStatus)))
            ->line("**Status baru:** " . ucwords(str_replace('_', ' ', $this->newStatus)))
            ->line("**Diperbarui oleh:** {$this->actionBy}");

        // Add description if provided
        if ($this->description) {
            $mail->line("**Catatan:** {$this->description}");
        }

        // $mail->action('Lihat TOR', url("/app/approval/tor/{$this->tor->tor_id}"))
        //     ->line('Terima kasih telah menggunakan aplikasi ini!');

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
