# PDF Generation

**Version:** 1.0
**Last Updated:** 2025-11-06
**Status**: Production

## Overview

PDF generation system using DomPDF for quotations, policies, reports, and documents.

### Supported PDF Types

1. **Quotation PDFs** - Insurance quotes for customers
2. **Comparison PDFs** - Multi-company premium comparisons
3. **Policy Documents** - Insurance policy certificates
4. **Commission Reports** - Broker commission statements
5. **Claim Documents** - Claim forms and reports

## PdfGenerationService

**File**: `app/Services/PdfGenerationService.php`

### Core Methods

**Generate Quotation PDF**:
```php
use App\Services\PdfGenerationService;

$pdfService = app(PdfGenerationService::class);

// Download PDF
return $pdfService->generateQuotationPdf($quotation);

// Generate for WhatsApp (saves to temp file)
$filePath = $pdfService->generateQuotationPdfForWhatsApp($quotation);
// Returns: storage/app/temp/quotations/Quotation_QT123_CustomerName.pdf
```

**Generate Comparison PDF**:
```php
return $pdfService->generateComparisonPdf($quotation);
```

### Implementation Details

**DomPDF Setup**:
```php
use Barryvdh\DomPDF\Facade\Pdf;

$pdf = Pdf::loadView('pdfs.quotation', $data);
$pdf->setPaper('A4', 'portrait'); // or 'landscape'
```

**Filename Sanitization**:
```php
// Automatically removes invalid characters: / \ : * ? " < > | \0
// Replaces spaces with underscores
// Limits to 100 characters
```

### PDF Templates

**Location**: `resources/views/pdfs/`

**Available Templates**:
- `quotation.blade.php` - Quote PDF template
- `comparison.blade.php` - Comparison PDF template
- `policy.blade.php` - Policy document template
- `commission-report.blade.php` - Commission report template

### Quotation PDF Structure

```blade
{{-- resources/views/pdfs/quotation.blade.php --}}
<html>
<head>
    <style>
        /* PDF-specific styles */
        body { font-family: 'DejaVu Sans', sans-serif; }
        .header { /* ... */ }
        .company-section { /* ... */ }
    </style>
</head>
<body>
    <!-- Company Logo & Details -->
    <div class="header">
        <!-- Tenant logo, contact info -->
    </div>

    <!-- Customer Details -->
    <div class="customer-info">
        <h3>Customer: {{ $quotation->customer->name }}</h3>
        <p>Mobile: {{ $quotation->customer->mobile_number }}</p>
    </div>

    <!-- Quotation Details -->
    <table>
        <thead>
            <tr>
                <th>Insurance Company</th>
                <th>Premium</th>
                <th>IDV</th>
                <th>Add-ons</th>
            </tr>
        </thead>
        <tbody>
            @foreach($quotation->quotationCompanies as $company)
            <tr>
                <td>{{ $company->insuranceCompany->name }}</td>
                <td>₹{{ number_format($company->final_premium, 2) }}</td>
                <td>₹{{ number_format($company->idv_value, 2) }}</td>
                <td>
                    @if($company->addons)
                        {{ implode(', ', $company->addons) }}
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Footer -->
    <div class="footer">
        <p>Generated on: {{ now()->format('d/m/Y H:i') }}</p>
    </div>
</body>
</html>
```

### Comparison PDF Features

**Data Preparation**:
```php
private function calculateSavings(Quotation $quotation): array
{
    $quotes = $quotation->quotationCompanies->sortBy('final_premium');
    $cheapest = $quotes->first();
    $mostExpensive = $quotes->last();

    return [
        'max_savings' => $mostExpensive->final_premium - $cheapest->final_premium,
        'cheapest_company' => $cheapest->insuranceCompany->name,
        'most_expensive_company' => $mostExpensive->insuranceCompany->name,
    ];
}
```

**Landscape Layout**:
```php
$pdf->setPaper('A4', 'landscape'); // Better for comparison tables
```

## Usage Examples

### Example 1: Generate & Download Quotation PDF

```php
// In QuotationController
public function downloadPdf(Quotation $quotation)
{
    $pdfService = app(PdfGenerationService::class);
    return $pdfService->generateQuotationPdf($quotation);
}
```

### Example 2: Generate PDF for WhatsApp Sharing

```php
use App\Services\PdfGenerationService;
use App\Services\WhatsAppService;

public function shareQuotationViaWhatsApp(Quotation $quotation)
{
    // Generate PDF
    $pdfService = app(PdfGenerationService::class);
    $pdfPath = $pdfService->generateQuotationPdfForWhatsApp($quotation);

    // Send via WhatsApp
    $whatsappService = app(WhatsAppService::class);
    $whatsappService->sendDocument(
        $quotation->customer->mobile_number,
        $pdfPath,
        "Your insurance quotation for {$quotation->vehicle_registration_no}"
    );

    // Clean up temp file
    unlink($pdfPath);

    return back()->with('success', 'Quotation sent via WhatsApp');
}
```

### Example 3: Generate Commission Report

```php
public function generateCommissionReport($brokerId, $month, $year)
{
    $broker = Broker::findOrFail($brokerId);

    $policies = CustomerInsurance::where('broker_id', $brokerId)
        ->whereYear('issue_date', $year)
        ->whereMonth('issue_date', $month)
        ->with(['customer', 'insuranceCompany', 'policyType'])
        ->get();

    $totalCommission = $policies->sum('my_commission_amount');

    $data = [
        'broker' => $broker,
        'month' => Carbon::createFromDate($year, $month)->format('F Y'),
        'policies' => $policies,
        'totalCommission' => $totalCommission
    ];

    $pdf = Pdf::loadView('pdfs.commission-report', $data);
    $pdf->setPaper('A4', 'portrait');

    $filename = "Commission_Report_{$broker->name}_{$month}_{$year}.pdf";

    return $pdf->download($filename);
}
```

### Example 4: Bulk PDF Generation

```php
public function generateBulkPolicyCertificates(Request $request)
{
    $policyIds = $request->input('policy_ids');
    $policies = CustomerInsurance::whereIn('id', $policyIds)->get();

    $zip = new ZipArchive();
    $zipFilename = storage_path('app/temp/policies_' . time() . '.zip');

    if ($zip->open($zipFilename, ZipArchive::CREATE) !== TRUE) {
        return back()->with('error', 'Could not create zip file');
    }

    foreach ($policies as $policy) {
        $pdf = Pdf::loadView('pdfs.policy', ['policy' => $policy]);
        $pdfContent = $pdf->output();

        $filename = "Policy_{$policy->policy_no}.pdf";
        $zip->addFromString($filename, $pdfContent);
    }

    $zip->close();

    return response()->download($zipFilename)->deleteFileAfterSend(true);
}
```

## Configuration

**DomPDF Config** (`config/dompdf.php`):
```php
return [
    'show_warnings' => false,
    'public_path' => public_path(),
    'convert_entities' => true,
    'options' => [
        'font_dir' => storage_path('fonts/'),
        'font_cache' => storage_path('fonts/'),
        'temp_dir' => storage_path('app/temp/'),
        'enable_font_subsetting' => false,
        'pdf_backend' => 'CPDF',
        'default_media_type' => 'screen',
        'default_paper_size' => 'a4',
        'default_paper_orientation' => 'portrait',
        'default_font' => 'DejaVu Sans',
        'dpi' => 96,
        'enable_php' => false,
        'enable_javascript' => false,
        'enable_remote' => true,
        'font_height_ratio' => 1.1,
        'enable_html5_parser' => true,
    ],
];
```

## Best Practices

1. **Use Web-Safe Fonts**: DejaVu Sans supports Unicode (₹ symbol)
2. **Optimize Images**: Compress logos/images before embedding
3. **Test Layouts**: PDF rendering differs from browser rendering
4. **Clean Temp Files**: Delete generated files after use
5. **Sanitize Filenames**: Use provided sanitization method
6. **Cache Templates**: Blade caching improves performance
7. **Handle Large PDFs**: Use pagination for multi-page reports

## Performance Tips

**Eager Load Relationships**:
```php
$quotation->load(['customer', 'quotationCompanies.insuranceCompany']);
```

**Use Queues for Bulk Generation**:
```php
dispatch(new GenerateBulkPdfsJob($policyIds));
```

## Related Documentation

- **[QUOTATION_SYSTEM.md](../modules/QUOTATION_SYSTEM.md)** - Quotation integration
- **[NOTIFICATION_SYSTEM.md](NOTIFICATION_SYSTEM.md)** - WhatsApp PDF sharing
- **[FILE_STORAGE_MULTI_TENANCY.md](FILE_STORAGE_MULTI_TENANCY.md)** - PDF storage

---

**Last Updated**: 2025-11-06
**Document Version**: 1.0
