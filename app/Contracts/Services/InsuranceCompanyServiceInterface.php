<?php

namespace App\Contracts\Services;

use App\Models\InsuranceCompany;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;

interface InsuranceCompanyServiceInterface
{
    public function getInsuranceCompanies(Request $request): LengthAwarePaginator;

    public function createInsuranceCompany(array $data): InsuranceCompany;

    public function updateInsuranceCompany(InsuranceCompany $insuranceCompany, array $data): InsuranceCompany;

    public function deleteInsuranceCompany(InsuranceCompany $insuranceCompany): bool;

    public function updateStatus(int $insuranceCompanyId, int $status): bool;

    public function exportInsuranceCompanies(): \Symfony\Component\HttpFoundation\BinaryFileResponse;

    public function getActiveInsuranceCompanies(): \Illuminate\Database\Eloquent\Collection;
}
