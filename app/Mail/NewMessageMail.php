<?php

namespace App\Mail;

use App\Models\Message;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NewMessageMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $messageObj;

    public function __construct(Message $message)
    {
        $this->messageObj = $message;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'New Message Received: ' . $this->messageObj->subject,
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.new_message',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
