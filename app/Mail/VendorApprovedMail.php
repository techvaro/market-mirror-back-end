<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class VendorApprovedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public readonly User $user, public readonly ?string $shopName = null) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your Market Mirror shop has been approved',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.vendor-approved',
            with: [
                'vendorName' => $this->user->name,
                'shopName' => $this->shopName,
            ],
        );
    }
}
