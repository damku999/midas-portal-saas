<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Templated Notification Mailable
 *
 * Sends notification emails with templated content.
 * Supports HTML content and file attachments.
 */
class TemplatedNotification extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * The email subject line.
     */
    protected string $emailSubject;

    /**
     * The HTML content for the email.
     */
    public string $htmlContent;

    /**
     * File attachments for the email.
     */
    protected array $emailAttachments;

    /**
     * Create a new message instance.
     */
    public function __construct(
        string $subject,
        string $htmlContent,
        array $attachments = []
    ) {
        $this->emailSubject = $subject;
        $this->htmlContent = $htmlContent;
        $this->emailAttachments = $attachments;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        // Get email settings from app settings
        $fromEmail = $this->getEmailFromAddress();
        $fromName = $this->getEmailFromName();

        return new Envelope(
            from: new \Illuminate\Mail\Mailables\Address($fromEmail, $fromName),
            replyTo: [
                new \Illuminate\Mail\Mailables\Address($this->getEmailReplyTo(), $fromName),
            ],
            subject: $this->emailSubject,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.templated-notification',
            with: [
                'htmlContent' => $this->htmlContent,
                'companyName' => company_name(),
                'companyWebsite' => company_website(),
                'companyPhone' => company_phone(),
                'companyAdvisor' => company_advisor_name(),
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        $attachmentObjects = [];

        foreach ($this->emailAttachments as $filePath) {
            if (file_exists($filePath)) {
                $attachmentObjects[] = Attachment::fromPath($filePath);
            }
        }

        return $attachmentObjects;
    }

    /**
     * Get email from address from settings.
     */
    protected function getEmailFromAddress(): string
    {
        return email_from_address();
    }

    /**
     * Get email from name from settings.
     */
    protected function getEmailFromName(): string
    {
        return email_from_name();
    }

    /**
     * Get email reply-to address from settings.
     */
    protected function getEmailReplyTo(): string
    {
        return email_reply_to();
    }
}
