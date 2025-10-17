<?php

namespace App\Exports;

use App\Models\Branch;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class BranchesExport implements FromCollection, WithHeadings, WithMapping
{
    public function collection()
    {
        return Branch::select('id', 'name', 'email', 'mobile_number', 'status', 'created_at')->get();
    }

    public function headings(): array
    {
        return [
            'ID',
            'Name',
            'Email',
            'Mobile Number',
            'Status',
            'Created At',
        ];
    }

    public function map($branch): array
    {
        return [
            $branch->id,
            $branch->name,
            $branch->email,
            $branch->mobile_number,
            $branch->status == 1 ? 'Active' : 'Inactive',
            $branch->created_at ? $branch->created_at->format('d-m-Y H:i:s') : '',
        ];
    }
}
