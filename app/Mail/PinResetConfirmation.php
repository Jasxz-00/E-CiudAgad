<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PinResetConfirmation extends Mailable
{
    use Queueable, SerializesModels;

    public User $user;

    public string $trackingNumber;

    public string $resetDateTime;

    public function __construct(User $user)
    {
        $this->user = $user;
        $this->trackingNumber = $user->tracking_number;
        $this->resetDateTime = now()->format('F d, Y \a\t h:i A');
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'PIN Reset Confirmation - E-CiudAgad',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.pin-reset-confirmation',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
