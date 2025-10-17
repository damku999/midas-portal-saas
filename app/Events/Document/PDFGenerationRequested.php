<?php

namespace App\Events\Document;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PDFGenerationRequested
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public string $documentType;

    public string $templateView;

    public array $templateData;

    public string $fileName;

    public ?string $storagePath;

    public array $pdfOptions;

    public int $priority;

    public ?string $referenceId;

    public ?int $customerId;

    public ?string $callbackEvent;

    public function __construct(
        string $documentType,
        string $templateView,
        array $templateData,
        string $fileName,
        ?string $storagePath = null,
        array $pdfOptions = [],
        int $priority = 5,
        ?string $referenceId = null,
        ?int $customerId = null,
        ?string $callbackEvent = null
    ) {
        $this->documentType = $documentType; // quotation, policy, certificate, etc.
        $this->templateView = $templateView;
        $this->templateData = $templateData;
        $this->fileName = $fileName;
        $this->storagePath = $storagePath ?? 'pdfs';
        $this->pdfOptions = array_merge([
            'format' => 'A4',
            'orientation' => 'portrait',
            'margin_top' => 10,
            'margin_right' => 10,
            'margin_bottom' => 10,
            'margin_left' => 10,
        ], $pdfOptions);
        $this->priority = $priority;
        $this->referenceId = $referenceId;
        $this->customerId = $customerId;
        $this->callbackEvent = $callbackEvent;
    }

    public function getEventData(): array
    {
        return [
            'document_type' => $this->documentType,
            'template_view' => $this->templateView,
            'template_data' => $this->templateData,
            'file_name' => $this->fileName,
            'storage_path' => $this->storagePath,
            'pdf_options' => $this->pdfOptions,
            'priority' => $this->priority,
            'reference_id' => $this->referenceId,
            'customer_id' => $this->customerId,
            'callback_event' => $this->callbackEvent,
            'requested_at' => now()->format('Y-m-d H:i:s'),
            'requested_by' => auth()->id(),
        ];
    }

    public function isQuotation(): bool
    {
        return $this->documentType === 'quotation';
    }

    public function isPolicy(): bool
    {
        return $this->documentType === 'policy';
    }

    public function isHighPriority(): bool
    {
        return $this->priority <= 3;
    }

    public function getExpectedFilePath(): string
    {
        return $this->storagePath.'/'.$this->fileName;
    }

    public function shouldQueue(): bool
    {
        return true;
    }

    public function getQueueName(): string
    {
        return $this->isHighPriority() ? 'pdf-priority' : 'pdf-normal';
    }
}
