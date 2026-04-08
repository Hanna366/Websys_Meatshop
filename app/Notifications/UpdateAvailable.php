<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class UpdateAvailable extends Notification
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
            ->subject('New Update Available - ' . $this->updateInfo['latest_version'])
            ->greeting('Hello ' . $notifiable->name)
            ->line('A new update is available for your Meat Shop POS system.')
            ->line('Current Version: ' . $this->updateInfo['current_version'])
            ->line('Latest Version: ' . $this->updateInfo['latest_version'])
            ->line('Update Type: ' . ucfirst($this->updateInfo['update_info']['type'] ?? 'minor'))
            ->action('View Update Details', route('admin.versions.index'))
            ->line('Please update your system to get the latest features and security improvements.');
    }

    public function toArray($notifiable): array
    {
        return [
            'title' => 'New Update Available',
            'message' => "Version {$this->updateInfo['latest_version']} is now available",
            'type' => 'update',
            'data' => $this->updateInfo
        ];
    }
}
