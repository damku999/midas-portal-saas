<?php

namespace App\Events\Communication;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class EmailQueued
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public string $recipientEmail;

    public string $recipientName;

    public string $subject;

    public string $templateName;

    public array $templateData;

    public array $attachments;

    public int $priority;

    public string $idempotencyKey;

    public ?int $relatedEntityId;

    /**
     * Create a new event instance.
     */
    public function __construct(
        string $recipientEmail,
        string $recipientName,
        string $subject,
        string $templateName,
        array $templateData = [],
        array $attachments = [],
        int $priority = 5,
        string $idempotencyKey = '',
        ?int $relatedEntityId = null
    ) {
        $this->recipientEmail = $recipientEmail;
        $this->recipientName = $recipientName;
        $this->subject = $subject;
        $this->templateName = $templateName;
        $this->templateData = $templateData;
        $this->attachments = $attachments;
        $this->priority = $priority;
        $this->idempotencyKey = $idempotencyKey ?: uniqid('email_', true);
        $this->relatedEntityId = $relatedEntityId;
    }
}
