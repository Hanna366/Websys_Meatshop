<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class UpdateCompleted extends Notification
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
            ->subject('Update Completed Successfully - ' . $this->updateInfo['version'])
            ->greeting('Hello ' . $notifiable->name)
            ->line('Your Meat Shop POS system has been successfully updated.')
            ->line('Updated to Version: ' . $this->updateInfo['version'])
            ->line('Update completed at: ' . now()->format('Y-m-d H:i:s'))
            ->line('Your system now includes the latest features and improvements.')
            ->action('View System', route('dashboard'))
            ->line('Thank you for keeping your system up to date!');
    }

    public function toArray($notifiable): array
    {
        return [
            'title' => 'Update Completed',
            'message' => "Successfully updated to version {$this->updateInfo['version']}",
            'type' => 'success',
            'data' => $this->updateInfo
        ];
    }
}
