<?php

namespace App\Mail;

use App\Models\Claim;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ClaimNotificationMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $claim;

    public $notificationType;

    public $additionalData;

    /**
     * Create a new message instance.
     */
    public function __construct(Claim $claim, string $notificationType, array $additionalData = [])
    {
        $this->claim = $claim;
        $this->notificationType = $notificationType;
        $this->additionalData = $additionalData;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $subject = $this->getSubject();

        return new Envelope(
            subject: $subject,
            from: email_from_address(),
            to: [$this->claim->customer->email],
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.claim-notification',
            with: [
                'claim' => $this->claim,
                'notificationType' => $this->notificationType,
                'additionalData' => $this->additionalData,
            ]
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }

    /**
     * Get the email subject based on notification type.
     */
    private function getSubject(): string
    {
        switch ($this->notificationType) {
            case 'stage_update':
                return "Claim Status Update - {$this->claim->claim_number}";
            case 'claim_number_assigned':
                return "Claim Number Assigned - {$this->claim->claim_number}";
            case 'document_request':
                return "Document Request - {$this->claim->claim_number}";
            case 'claim_created':
                return "Claim Registration Confirmation - {$this->claim->claim_number}";
            case 'claim_closed':
                return "Claim Closed - {$this->claim->claim_number}";
            default:
                return "Claim Notification - {$this->claim->claim_number}";
        }
    }
}
