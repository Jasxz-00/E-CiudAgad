<?php

namespace App\Notifications;

use App\Models\DocumentRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NewRequestNotification extends Notification
{
    use Queueable;

    public function __construct(public DocumentRequest $documentRequest)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $resident = $this->documentRequest->resident;

        return [
            'title' => 'New Document Request',
            'message' => $resident
                ? "{$resident->full_name} filed a request for {$this->documentRequest->documentType?->name} (Queue #: {$this->documentRequest->queue_number})."
                : "A new request was filed (Queue #: {$this->documentRequest->queue_number}).",
            'queue_number' => $this->documentRequest->queue_number,
            'url' => route('personnel.request.show', $this->documentRequest->id),
        ];
    }
}