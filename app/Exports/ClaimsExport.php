<?php

namespace App\Exports;

use App\Models\Claim;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ClaimsExport implements FromQuery, ShouldAutoSize, WithHeadings, WithMapping, WithStyles
{
    protected array $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    /**
     * @return Builder
     */
    public function query()
    {
        $query = Claim::with([
            'customer:id,name,email,mobile_number',
            'customerInsurance:id,policy_no,registration_no,insurance_company_id',
            'customerInsurance.insuranceCompany:id,name',
            'currentStage:id,claim_id,stage_name',
        ]);

        // Apply the same filters as in the service
        if (! empty($this->filters['search'])) {
            $search = $this->filters['search'];
            $query->where(function (Builder $q) use ($search) {
                $q->where('claim_number', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhereHas('customer', function (Builder $customerQuery) use ($search) {
                        $customerQuery->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%")
                            ->orWhere('mobile_number', 'like', "%{$search}%");
                    })
                    ->orWhereHas('customerInsurance', function (Builder $insuranceQuery) use ($search) {
                        $insuranceQuery->where('policy_no', 'like', "%{$search}%")
                            ->orWhere('registration_no', 'like', "%{$search}%");
                    });
            });
        }

        if (! empty($this->filters['insurance_type'])) {
            $query->where('insurance_type', $this->filters['insurance_type']);
        }

        if (isset($this->filters['status']) && $this->filters['status'] !== '') {
            $query->where('status', $this->filters['status']);
        }

        if (! empty($this->filters['date_from'])) {
            $query->whereDate('incident_date', '>=', formatDateForDatabase($this->filters['date_from']));
        }

        if (! empty($this->filters['date_to'])) {
            $query->whereDate('incident_date', '<=', formatDateForDatabase($this->filters['date_to']));
        }

        return $query->orderBy('created_at', 'desc');
    }

    public function headings(): array
    {
        return [
            'Claim Number',
            'Customer Name',
            'Email',
            'Mobile Number',
            'Policy Number',
            'Vehicle/Registration Number',
            'Insurance Company',
            'Insurance Type',
            'Incident Date',
            'Current Stage',
            'WhatsApp Number',
            'Email Notifications',
            'Status',
            'Description',
            'Created Date',
            'Last Updated',
        ];
    }

    /**
     * @param  Claim  $claim
     */
    public function map($claim): array
    {
        return [
            $claim->claim_number,
            $claim->customer->name ?? 'N/A',
            $claim->customer->email ?? 'N/A',
            $claim->customer->mobile_number ?? 'N/A',
            $claim->customerInsurance->policy_no ?? 'N/A',
            $claim->customerInsurance->registration_no ?? 'N/A',
            $claim->customerInsurance->insuranceCompany->name ?? 'N/A',
            $claim->insurance_type,
            $claim->incident_date ? format_app_date($claim->incident_date) : 'N/A',
            $claim->currentStage->stage_name ?? 'No Stage',
            $claim->whatsapp_number ?? 'N/A',
            $claim->send_email_notifications ? 'Yes' : 'No',
            $claim->status ? 'Active' : 'Inactive',
            $claim->description ?? 'N/A',
            $claim->created_at ? format_app_datetime($claim->created_at) : 'N/A',
            $claim->updated_at ? format_app_datetime($claim->updated_at) : 'N/A',
        ];
    }

    /**
     * @return array
     */
    public function styles(Worksheet $sheet)
    {
        return [
            // Style the first row as bold text.
            1 => ['font' => ['bold' => true]],
        ];
    }
}
