<?php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Mail\InvoiceMail;
use App\Models\Central\Invoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class InvoiceController extends Controller
{
    /**
     * Display invoice in browser.
     */
    public function show(Invoice $invoice)
    {
        $invoice->load(['tenant', 'subscription.plan', 'payment']);

        return view('central.invoices.pdf', compact('invoice'));
    }

    /**
     * Download invoice as PDF.
     */
    public function download(Invoice $invoice)
    {
        try {
            $invoice->load(['tenant', 'subscription.plan', 'payment']);

            // Generate PDF using DomPDF or similar
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('central.invoices.pdf', compact('invoice'));

            $filename = "Invoice-{$invoice->invoice_number}.pdf";

            return $pdf->download($filename);

        } catch (\Exception $e) {
            Log::error("Failed to generate PDF for invoice #{$invoice->id}: " . $e->getMessage());
            return back()->with('error', 'Failed to generate invoice PDF. Please try again.');
        }
    }

    /**
     * Stream invoice PDF (for email attachment).
     */
    public function stream(Invoice $invoice)
    {
        try {
            $invoice->load(['tenant', 'subscription.plan', 'payment']);

            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('central.invoices.pdf', compact('invoice'));

            $filename = "Invoice-{$invoice->invoice_number}.pdf";

            return $pdf->stream($filename);

        } catch (\Exception $e) {
            Log::error("Failed to stream PDF for invoice #{$invoice->id}: " . $e->getMessage());
            return back()->with('error', 'Failed to stream invoice PDF. Please try again.');
        }
    }

    /**
     * Send invoice via email.
     */
    public function sendEmail(Invoice $invoice)
    {
        try {
            $invoice->load(['tenant', 'subscription.plan', 'payment']);

            // Validate customer email
            if (empty($invoice->customer_email)) {
                return back()->with('error', 'Customer email is not available for this invoice.');
            }

            // Send invoice email with PDF attachment
            Mail::to($invoice->customer_email)->send(new InvoiceMail($invoice));

            // Log the email sent
            Log::info("Invoice #{$invoice->invoice_number} sent to {$invoice->customer_email}");

            return back()->with('success', "Invoice sent successfully to {$invoice->customer_email}");

        } catch (\Exception $e) {
            Log::error("Failed to send invoice #{$invoice->id} via email: " . $e->getMessage());
            return back()->with('error', 'Failed to send invoice. Please try again.');
        }
    }
}
