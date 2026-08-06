<?php

namespace App\Notifications;

use App\Models\Concern;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NewConcernNotification extends Notification
{
    use Queueable;

    public function __construct(public Concern $concern)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'New Concern',
            'message' => $this->concern->resident
                ? "{$this->concern->resident->full_name} submitted a concern: {$this->concern->subject}."
                : "A resident submitted a concern: {$this->concern->subject}.",
            'url' => route('personnel.dashboard'),
        ];
    }
}