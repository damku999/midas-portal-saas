<?php

namespace App\Exports;

use App\Models\Customer;
use App\Models\PremiumType;
use Illuminate\Support\Carbon;
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
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class CrossSellingExport implements WithMultipleSheets
{
    protected $filters;

    public function __construct(array $filters)
    {
        $this->filters = $filters;
    }

    public function sheets(): array
    {
        return [
            'Summary' => new CrossSellingSummarySheet($this->filters),
            'Detailed Data' => new CrossSellingDataSheet($this->filters),
        ];
    }
}

class CrossSellingSummarySheet implements FromCollection, ShouldAutoSize, WithEvents, WithHeadings, WithStyles
{
    protected $filters;

    public function __construct(array $filters)
    {
        $this->filters = $filters;
    }

    public function collection()
    {
        // Get basic summary data
        $premiumTypes = PremiumType::select('id', 'name');
        if (! empty($this->filters['premium_type_id'])) {
            $premiumTypes = $premiumTypes->whereIn('id', $this->filters['premium_type_id']);
        }
        $premiumTypes = $premiumTypes->get();

        $customers = Customer::with(['insurance.premiumType'])->get();

        $summary = [];
        $summary[] = ['Total Customers', $customers->count()];
        $summary[] = ['Total Premium', number_format($customers->sum(function ($customer) {
            return $customer->insurance->sum('final_premium_with_gst');
        }), 2)];
        $summary[] = ['Total Earnings', number_format($customers->sum(function ($customer) {
            return $customer->insurance->sum('actual_earnings');
        }), 2)];

        $summary[] = ['', '']; // Empty row

        // Premium type breakdown
        foreach ($premiumTypes as $premiumType) {
            $yesCount = $customers->filter(function ($customer) use ($premiumType) {
                return $customer->insurance->contains('premium_type_id', $premiumType->id);
            })->count();

            $noCount = $customers->count() - $yesCount;

            $totalAmount = $customers->sum(function ($customer) use ($premiumType) {
                return $customer->insurance->where('premium_type_id', $premiumType->id)->sum('final_premium_with_gst');
            });

            $summary[] = [$premiumType->name.' - Yes Count', $yesCount];
            $summary[] = [$premiumType->name.' - No Count', $noCount];
            $summary[] = [$premiumType->name.' - Total Amount', number_format($totalAmount, 2)];
            $summary[] = ['', '']; // Empty row
        }

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

class CrossSellingDataSheet implements FromCollection, ShouldAutoSize, WithEvents, WithHeadings, WithStyles
{
    protected $filters;

    public function __construct(array $filters)
    {
        $this->filters = $filters;
    }

    public function collection()
    {
        $premiumTypes = PremiumType::select('id', 'name');
        if (! empty($this->filters['premium_type_id'])) {
            $premiumTypes = $premiumTypes->whereIn('id', $this->filters['premium_type_id']);
        }
        $premiumTypes = $premiumTypes->get();

        $customer_obj = Customer::with(['insurance.premiumType', 'insurance.broker', 'insurance.relationshipManager', 'insurance.insuranceCompany'])->orderBy('name');
        $hasDateFilter = false;

        // Apply comprehensive filters matching ReportService
        if (! empty($this->filters['issue_start_date']) || ! empty($this->filters['issue_end_date'])) {
            $customer_obj = $customer_obj->whereHas('insurance', function ($query) {
                if (! empty($this->filters['issue_start_date'])) {
                    try {
                        $startDate = Carbon::createFromFormat(app_date_format(), $this->filters['issue_start_date'])->format('Y-m-d');
                        $query->where('start_date', '>=', $startDate);
                    } catch (\Exception $e) {
                        $query->where('start_date', '>=', $this->filters['issue_start_date']);
                    }
                }
                if (! empty($this->filters['issue_end_date'])) {
                    try {
                        $endDate = Carbon::createFromFormat(app_date_format(), $this->filters['issue_end_date'])->format('Y-m-d');
                        $query->where('start_date', '<=', $endDate);
                    } catch (\Exception $e) {
                        $query->where('start_date', '<=', $this->filters['issue_end_date']);
                    }
                }
            });
            $hasDateFilter = true;
        }

        // Business entity filters
        if (! empty($this->filters['broker_id'])) {
            $customer_obj = $customer_obj->whereHas('insurance', function ($query) {
                $query->where('broker_id', $this->filters['broker_id']);
            });
        }

        if (! empty($this->filters['relationship_manager_id'])) {
            $customer_obj = $customer_obj->whereHas('insurance', function ($query) {
                $query->where('relationship_manager_id', $this->filters['relationship_manager_id']);
            });
        }

        if (! empty($this->filters['insurance_company_id'])) {
            $customer_obj = $customer_obj->whereHas('insurance', function ($query) {
                $query->where('insurance_company_id', $this->filters['insurance_company_id']);
            });
        }

        if (! empty($this->filters['customer_id'])) {
            $customer_obj = $customer_obj->where('id', $this->filters['customer_id']);
        }

        $customers = $customer_obj->get();
        $oneYearAgo = Carbon::now()->subYear();

        $results = $customers->map(function ($customer) use ($premiumTypes, $oneYearAgo, $hasDateFilter) {
            $customerData = ['customer_name' => $customer->name];

            if (! $hasDateFilter) {
                $customerData['total_premium_last_year'] = $customer->insurance
                    ->where('start_date', '>=', $oneYearAgo)
                    ->sum('final_premium_with_gst');
                $customerData['actual_earnings_last_year'] = $customer->insurance
                    ->where('start_date', '>=', $oneYearAgo)
                    ->sum('actual_earnings');
            } else {
                $customerData['total_premium_last_year'] = $customer->insurance
                    ->sum('final_premium_with_gst');
                $customerData['actual_earnings_last_year'] = $customer->insurance
                    ->sum('actual_earnings');
            }

            foreach ($premiumTypes as $premiumType) {
                $hasPremiumType = $customer->insurance->contains(function ($insurance) use ($premiumType) {
                    return $insurance->premiumType->id === $premiumType->id;
                });

                $premiumTotal = $customer->insurance
                    ->where('premium_type_id', $premiumType->id)
                    ->sum('final_premium_with_gst');

                $customerData[$premiumType->name] = $hasPremiumType ? 'Yes' : 'No';
                $customerData[$premiumType->name.' (Sum Insured)'] = $premiumTotal;
            }

            return $customerData;
        });

        return $results;
    }

    public function headings(): array
    {
        $premiumTypes = PremiumType::select('id', 'name');
        if (! empty($this->filters['premium_type_id'])) {
            $premiumTypes = $premiumTypes->whereIn('id', $this->filters['premium_type_id']);
        }
        $premiumTypes = $premiumTypes->get();

        $header = ['Customer Name', 'Total Premium (Last Year)', 'Actual Earnings (Last Year)'];

        foreach ($premiumTypes as $premiumType) {
            $header[] = $premiumType->name;
            $header[] = $premiumType->name.' (Sum Insured)';
        }

        return $header;
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
                foreach (range('A', $lastColumn) as $column) {
                    $sheet->getColumnDimension($column)->setAutoSize(true);
                }

                // Add auto-filter to header row
                $sheet->setAutoFilter("A1:{$lastColumn}1");
            },
        ];
    }
}
