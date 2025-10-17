<?php

namespace App\Mail;

use App\Models\Customer;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CustomerPasswordResetMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public Customer $customer,
        public string $token
    ) {}

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            null, // from
            [], // to
            [], // cc
            [], // bcc
            [], // replyTo
            'Reset Your Customer Portal Password' // subject
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
            'emails.customer.password-reset', // markdown
            [
                'customer' => $this->customer,
                'token' => $this->token,
                'resetUrl' => route('customer.password.reset', ['token' => $this->token, 'email' => $this->customer->email]),
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
