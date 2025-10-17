<?php

namespace App\Mail;

use App\Models\Customer;
use App\Models\FamilyGroup;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class FamilyLoginCredentialsMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public Customer $customer,
        public string $password,
        public FamilyGroup $familyGroup,
        public bool $isHead = false
    ) {}

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $subject = $this->isHead
            ? 'Your Family Group Portal - Family Head Credentials'
            : 'Your Family Group Portal - Login Credentials';

        return new Envelope(
            null, // from
            [], // to
            [], // cc
            [], // bcc
            [], // replyTo
            $subject // subject
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            null, // view
            null, // html
            null, // text
            'emails.customer.family-login-credentials', // markdown
            [
                'customer' => $this->customer,
                'password' => $this->password,
                'familyGroup' => $this->familyGroup,
                'isHead' => $this->isHead,
                'loginUrl' => route('customer.login'),
                'verificationUrl' => route('customer.verify-email', $this->customer->email_verification_token),
            ] // with
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
}
