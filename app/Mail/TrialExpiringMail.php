<?php

namespace App\Mail;

use App\Models\Central\Subscription;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TrialExpiringMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public Subscription $subscription,
        public int $daysRemaining
    ) {
        //
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $companyName = $this->subscription->tenant->data['company_name'] ?? 'Company';

        return new Envelope(
            subject: "Your {$this->daysRemaining}-Day Trial is Ending Soon - Upgrade to Continue",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.trial-expiring',
            with: [
                'subscription' => $this->subscription,
                'daysRemaining' => $this->daysRemaining,
                'plan' => $this->subscription->plan,
                'companyName' => $this->subscription->tenant->data['company_name'] ?? 'Your Company',
                'upgradeUrl' => 'https://' . $this->subscription->tenant->domains->first()->domain . '/subscription/plans',
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
}
