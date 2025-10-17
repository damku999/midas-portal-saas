<?php

namespace App\Exports;

use App\Models\CustomerInsurance;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class CustomerInsurancesExport implements FromCollection, ShouldAutoSize, WithHeadings, WithStyles
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        $customerInsurances = CustomerInsurance::with(
            'branch',
            'broker',
            'relationshipManager',
            'customer',
            'insuranceCompany',
            'premiumType',
            'policyType',
            'fuelType'
        )->get();

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
                'registration Number' => $customerInsurance->registration_no,
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
                'font' => ['bold' => true],
                'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '58C4A7']],
            ],
        ];
    }

    public function applyFilter(Worksheet $sheet, string $filterRange)
    {
        $sheet->setAutoFilter($filterRange);
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet;
                $filterRange = 'A1:AJ1'; // Adjust the filter range as per your requirements

                // Apply filter
                $this->applyFilter($sheet, $filterRange);

                // Header styles
                $sheet->getStyle('A1:AJ1')->applyFromArray([
                    'font' => [
                        'bold' => true,
                    ],
                    'fill' => [
                        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'startColor' => [
                            'rgb' => '58C4A7',
                        ],
                    ],
                ]);

                // Page setup
                $sheet->getPageSetup()->setOrientation(PageSetup::ORIENTATION_LANDSCAPE);
            },
        ];
    }
}
