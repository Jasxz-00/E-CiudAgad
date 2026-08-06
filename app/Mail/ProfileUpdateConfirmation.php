<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ProfileUpdateConfirmation extends Mailable
{
    use Queueable, SerializesModels;

    public User $user;

    public array $changedFields;

    public string $updateDateTime;

    public function __construct(User $user, array $changedFields)
    {
        $this->user = $user;
        $this->changedFields = $changedFields;
        $this->updateDateTime = now()->format('F d, Y \a\t h:i A');
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Profile Update Confirmation - E-CiudAgad',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.profile-update-confirmation',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
