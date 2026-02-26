<?php

namespace App\Mail;

use App\Models\Colocation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ColocationInvitationMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public Colocation $colocation,
        public string $acceptUrl,
        public string $refuseUrl,
        public $expiresAt
    ) {
    }

    public function envelope(): Envelope {
        return new Envelope(
            subject: 'Colocation invitation',
        );
    }

    public function content(): Content {
        return new Content(
            view: 'emails.colocation-invitation',
        );
    }
}
