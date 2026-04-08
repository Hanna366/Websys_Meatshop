<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class UpdateFailed extends Notification
{
    public function __construct(
        public array $updateInfo
    ) {}

    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Update Failed - ' . $this->updateInfo['version'])
            ->greeting('Hello ' . $notifiable->name)
            ->line('There was an issue updating your Meat Shop POS system.')
            ->line('Target Version: ' . $this->updateInfo['version'])
            ->line('Error: ' . ($this->updateInfo['error'] ?? 'Unknown error occurred'))
            ->line('Please contact support or try updating manually.')
            ->action('View Update Status', route('admin.versions.index'))
            ->line('Your system is still running on the previous version and should continue to work normally.');
    }

    public function toArray($notifiable): array
    {
        return [
            'title' => 'Update Failed',
            'message' => "Failed to update to version {$this->updateInfo['version']}",
            'type' => 'error',
            'data' => $this->updateInfo
        ];
    }
}
