<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TwoFactorMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $code,
        public string $userName,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '[TRA Catalog] Kode Verifikasi Akun Anda',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.two-factor',
            with: [
                'code'     => $this->code,
                'userName' => $this->userName,
            ],
        );
    }
}