<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class VerificationCodeMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public readonly User $user, public readonly string $code) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Verify your Market Mirror email address');
    }

    public function content(): Content
    {
        return new Content(view: 'mail.verification-code', with: [
            'name' => $this->user->name,
            'code' => $this->code,
        ]);
    }
}
