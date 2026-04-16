<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ApprovalNotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $userName,
        public string $status,   // 'approved' | 'rejected'
        public ?string $notes = null,
    ) {}

    public function envelope(): Envelope
    {
        $subject = $this->status === 'approved'
            ? '[TRAD Catalog] Akun Anda Telah Disetujui ✅'
            : '[TRAD Catalog] Pendaftaran Akun Anda';

        return new Envelope(subject: $subject);
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.approval-notification',
            with: [
                'userName' => $this->userName,
                'status'   => $this->status,
                'notes'    => $this->notes,
            ],
        );
    }
}