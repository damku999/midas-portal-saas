<?php

namespace App\Exports;

use App\Models\Report;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class CustomerInsurancesExport1 implements WithMultipleSheets
{
    protected $filters;

    public function __construct(array $filters)
    {
        $this->filters = $filters;
    }

    public function sheets(): array
    {
        $reportType = $this->filters['report_name'] ?? 'Insurance Detail';

        return [
            'Summary' => new InsuranceSummarySheet($this->filters, $reportType),
            'Detailed Data' => new InsuranceDataSheet($this->filters),
        ];
    }
}

class InsuranceSummarySheet implements FromCollection, ShouldAutoSize, WithEvents, WithHeadings, WithStyles
{
    protected $filters;

    protected $reportType;

    public function __construct(array $filters, string $reportType)
    {
        $this->filters = $filters;
        $this->reportType = $reportType;
    }

    public function collection()
    {
        $customerInsurances = Report::getInsuranceReport($this->filters);

        $summary = [];
        $summary[] = ['Total Policies', $customerInsurances->count()];
        $summary[] = ['Total Premium Amount', '₹ '.number_format($customerInsurances->sum('final_premium_with_gst'), 2)];
        $summary[] = ['Total Actual Earnings', '₹ '.number_format($customerInsurances->sum('actual_earnings'), 2)];
        $summary[] = ['Average Premium', '₹ '.number_format($customerInsurances->avg('final_premium_with_gst'), 2)];

        $summary[] = ['', '']; // Empty row

        // Premium type breakdown
        $premiumTypeBreakdown = $customerInsurances->groupBy('premiumType.name');
        foreach ($premiumTypeBreakdown as $type => $policies) {
            $summary[] = [$type.' - Count', $policies->count()];
            $summary[] = [$type.' - Total Premium', '₹ '.number_format($policies->sum('final_premium_with_gst'), 2)];
            $summary[] = ['', '']; // Empty row
        }

        // Insurance company breakdown
        $companyBreakdown = $customerInsurances->groupBy('insuranceCompany.name');
        $summary[] = ['Insurance Companies:', ''];
        foreach ($companyBreakdown as $company => $policies) {
            $summary[] = [$company, $policies->count().' policies - ₹'.number_format($policies->sum('final_premium_with_gst'), 2)];
        }

        $summary[] = ['', '']; // Empty row

        // Status breakdown
        $activeCount = $customerInsurances->where('status', 1)->count();
        $notRenewedCount = $customerInsurances->where('status', 0)->count();
        $summary[] = ['Active Policies', $activeCount];
        $summary[] = ['Not Renewed', $notRenewedCount];

        return collect($summary);
    }

    public function headings(): array
    {
        return ['Metric', 'Value'];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true, 'size' => 14, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '2B7EC8']], // WebMonks Blue
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet;

                // Style all data rows with alternating colors
                $lastRow = $sheet->getHighestRow();

                // Header styling
                $sheet->getStyle('A1:B1')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 14, 'color' => ['rgb' => 'FFFFFF']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '2B7EC8']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                    'borders' => [
                        'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '000000']],
                    ],
                ]);

                // Alternate row colors for data
                for ($row = 2; $row <= $lastRow; $row++) {
                    $fillColor = ($row % 2 == 0) ? 'E8F4FD' : 'FFFFFF'; // Light blue and white alternating
                    $sheet->getStyle("A{$row}:B{$row}")->applyFromArray([
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $fillColor]],
                        'borders' => [
                            'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'CCCCCC']],
                        ],
                        'font' => ['size' => 11],
                    ]);
                }

                // Make metric column bold
                $sheet->getStyle("A2:A{$lastRow}")->applyFromArray([
                    'font' => ['bold' => true],
                ]);

                // Auto-fit columns
                $sheet->getColumnDimension('A')->setAutoSize(true);
                $sheet->getColumnDimension('B')->setAutoSize(true);
            },
        ];
    }
}

class InsuranceDataSheet implements FromCollection, ShouldAutoSize, WithEvents, WithHeadings, WithStyles
{
    protected $filters;

    public function __construct(array $filters)
    {
        $this->filters = $filters;
    }

    public function collection()
    {
        $customerInsurances = Report::getInsuranceReport($this->filters);

        return $customerInsurances->map(function ($customerInsurance) {
            return [
                'Customer' => $customerInsurance->customer->name,
                'Branch' => $customerInsurance->branch->name,
                'Broker' => $customerInsurance->broker->name,
                'RM' => $customerInsurance->relationshipManager->name,
                'Insurance Company' => $customerInsurance->insuranceCompany->name,
                'Premium Type' => $customerInsurance->premiumType->name,
                'Policy Type' => $customerInsurance->policyType->name,
                'Fuel Type' => @$customerInsurance->fuelType->name,
                'Issue Date' => $customerInsurance->issue_date,
                'Policy Number' => $customerInsurance->policy_no,
                'Registration Number' => $customerInsurance->registration_no,
                'RTO' => $customerInsurance->rto,
                'Make & Model' => $customerInsurance->make_model,
                'Commission On' => $customerInsurance->commission_on,
                'Start Date' => $customerInsurance->start_date,
                'Expired Date' => $customerInsurance->expired_date,
                'TP Expiry Date' => $customerInsurance->tp_expiry_date,
                'OD Premium' => $customerInsurance->od_premium,
                'TP Premium' => $customerInsurance->tp_premium,
                'Net Premium' => $customerInsurance->net_premium,
                'Final Premium With GST' => $customerInsurance->final_premium_with_gst,
                'SGST 1' => $customerInsurance->sgst1,
                'CGST 1' => $customerInsurance->cgst1,
                'CGST 2' => $customerInsurance->cgst2,
                'SGCT 2' => $customerInsurance->sgst2,
                'My Commission Percentage' => $customerInsurance->my_commission_percentage,
                'My Commission Amount' => $customerInsurance->my_commission_amount,
                'Transfer Commission Percentage' => $customerInsurance->transfer_commission_percentage,
                'Transfer Commission Amount' => $customerInsurance->transfer_commission_amount,
                'Actual Earnings' => $customerInsurance->actual_earnings,
                'NCB Percentage' => $customerInsurance->ncb_percentage,
                'Mode Of Payment' => $customerInsurance->mode_of_payment,
                'Cheque Number' => $customerInsurance->cheque_no,
                'Insurance Status' => $customerInsurance->insurance_status,
                'Gross Vehicle Weight' => $customerInsurance->gross_vehicle_weight,
                'MFG. Year' => $customerInsurance->mfg_year,
            ];
        });
    }

    public function headings(): array
    {
        return [
            'Customer',
            'Branch',
            'Broker',
            'RM',
            'Insurance Company',
            'Premium Type',
            'Policy Type',
            'Fuel Type',
            'Issue Date',
            'Policy Number',
            'Registration Number',
            'RTO',
            'Make & Model',
            'Commission On',
            'Start Date',
            'Expired Date',
            'TP Expiry Date',
            'OD Premium',
            'TP Premium',
            'Net Premium',
            'Final Premium With GST',
            'SGST 1',
            'CGST 1',
            'CGST 2',
            'SGCT 2',
            'My Commission Percentage',
            'My Commission Amount',
            'Transfer Commission Percentage',
            'Transfer Commission Amount',
            'Actual Earnings',
            'NCB Percentage',
            'Mode Of Payment',
            'Cheque Number',
            'Insurance Status',
            'Gross Vehicle Weight',
            'MFG. Year',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true, 'size' => 12, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '2B7EC8']], // WebMonks Blue
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet;

                $lastRow = $sheet->getHighestRow();
                $lastColumn = $sheet->getHighestColumn();

                // Header styling
                $sheet->getStyle("A1:{$lastColumn}1")->applyFromArray([
                    'font' => ['bold' => true, 'size' => 12, 'color' => ['rgb' => 'FFFFFF']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '2B7EC8']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                    'borders' => [
                        'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '000000']],
                    ],
                ]);

                // Alternate row colors for data
                for ($row = 2; $row <= $lastRow; $row++) {
                    $fillColor = ($row % 2 == 0) ? 'F0F8FF' : 'FFFFFF'; // Very light blue and white alternating
                    $sheet->getStyle("A{$row}:{$lastColumn}{$row}")->applyFromArray([
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $fillColor]],
                        'borders' => [
                            'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'DDDDDD']],
                        ],
                        'font' => ['size' => 10],
                    ]);
                }

                // Auto-fit all columns
                $columnIndex = 'A';
                while ($columnIndex !== $lastColumn) {
                    $sheet->getColumnDimension($columnIndex)->setAutoSize(true);
                    $columnIndex++;
                }
                // Don't forget the last column
                $sheet->getColumnDimension($lastColumn)->setAutoSize(true);

                // Add auto-filter to header row
                $sheet->setAutoFilter("A1:{$lastColumn}1");

                // Set page orientation
                $sheet->getPageSetup()->setOrientation(PageSetup::ORIENTATION_LANDSCAPE);
            },
        ];
    }
}
