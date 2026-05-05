<?php

namespace App\Mail;

use App\Models\Complaint;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NewComplaintMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $complaint;

    public function __construct(Complaint $complaint)
    {
        $this->complaint = $complaint;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'New Complaint Submitted: ' . $this->complaint->title,
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.new_complaint',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
