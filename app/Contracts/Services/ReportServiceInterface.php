<?php

namespace App\Contracts\Services;

use Symfony\Component\HttpFoundation\BinaryFileResponse;

interface ReportServiceInterface
{
    /**
     * Get initial data for reports page
     */
    public function getInitialData(): array;

    /**
     * Generate cross selling report with analysis
     */
    public function generateCrossSellingReport(array $parameters): array;

    /**
     * Generate customer insurance report
     */
    public function generateCustomerInsuranceReport(array $parameters): array;

    /**
     * Export cross selling report to Excel
     */
    public function exportCrossSellingReport(array $parameters): BinaryFileResponse;

    /**
     * Export customer insurance report to Excel
     */
    public function exportCustomerInsuranceReport(array $parameters): BinaryFileResponse;

    /**
     * Save user's selected columns for a report
     */
    public function saveUserReportColumns(string $reportName, array $selectedColumns, int $userId): void;

    /**
     * Load user's saved columns for a report
     */
    public function loadUserReportColumns(string $reportName, int $userId): ?array;
}
