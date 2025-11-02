<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DataMigrationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Migrate existing data from enum/varchar to lookup table IDs
     * This should run AFTER all lookup table seeders
     */
    public function run(): void
    {
        $this->migrateCustomerTypes();
        $this->migrateCommissionTypes();
        $this->migrateQuotationStatuses();
    }

    /**
     * Migrate customer types to lookup table IDs
     */
    private function migrateCustomerTypes(): void
    {
        if (Schema::hasTable('customers') && Schema::hasColumn('customers', 'customer_type_id')) {
            $customerTypeMapping = [
                'Corporate' => 1,
                'Retail' => 2,
                'corporate' => 1,
                'retail' => 2,
            ];

            foreach ($customerTypeMapping as $typeName => $typeId) {
                DB::table('customers')
                    ->where('type', $typeName)
                    ->update(['customer_type_id' => $typeId]);
            }
        }
    }

    /**
     * Migrate commission types to lookup table IDs
     */
    private function migrateCommissionTypes(): void
    {
        if (Schema::hasTable('customer_insurances') && Schema::hasColumn('customer_insurances', 'commission_type_id')) {
            $commissionTypeMapping = [
                'net_premium' => 1,
                'od_premium' => 2,
                'tp_premium' => 3,
            ];

            foreach ($commissionTypeMapping as $commissionName => $commissionId) {
                DB::table('customer_insurances')
                    ->where('commission_on', $commissionName)
                    ->update(['commission_type_id' => $commissionId]);
            }
        }
    }

    /**
     * Migrate quotation statuses to lookup table IDs
     */
    private function migrateQuotationStatuses(): void
    {
        if (Schema::hasTable('quotations') && Schema::hasColumn('quotations', 'quotation_status_id')) {
            $quotationStatusMapping = [
                'Draft' => 1,
                'Generated' => 2,
                'Sent' => 3,
                'Accepted' => 4,
                'Rejected' => 5,
                'draft' => 1,
                'generated' => 2,
                'sent' => 3,
                'accepted' => 4,
                'rejected' => 5,
            ];

            foreach ($quotationStatusMapping as $statusName => $statusId) {
                DB::table('quotations')
                    ->where('status', $statusName)
                    ->update(['quotation_status_id' => $statusId]);
            }
        }
    }
}
