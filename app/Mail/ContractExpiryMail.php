<?php

namespace App\Mail;

use App\Models\Contract;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ContractExpiryMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $contract;

    public function __construct(Contract $contract)
    {
        $this->contract = $contract;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Alert: Contract Expiring Soon for ' . $this->contract->employee->employee_code,
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.contract_expiry',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
