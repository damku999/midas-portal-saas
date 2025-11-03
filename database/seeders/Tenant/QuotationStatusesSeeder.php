<?php

namespace Database\Seeders\Tenant;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class QuotationStatusesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear existing data
        DB::table('quotation_statuses')->truncate();

        // Insert quotation statuses data
        DB::table('quotation_statuses')->insert([
            [
                'name' => 'Draft',
                'description' => 'Quotation is in draft mode',
                'color' => '#6c757d',
                'is_active' => 1,
                'is_final' => 0,
                'sort_order' => 1,
                'created_at' => now(),
                'updated_at' => now(),
                'created_by' => null,
                'updated_by' => null,
                'deleted_by' => null,
                'deleted_at' => null,
            ],
            [
                'name' => 'Generated',
                'description' => 'Quotation has been generated',
                'color' => '#17a2b8',
                'is_active' => 1,
                'is_final' => 0,
                'sort_order' => 2,
                'created_at' => now(),
                'updated_at' => now(),
                'created_by' => null,
                'updated_by' => null,
                'deleted_by' => null,
                'deleted_at' => null,
            ],
            [
                'name' => 'Sent',
                'description' => 'Quotation has been sent to customer',
                'color' => '#ffc107',
                'is_active' => 1,
                'is_final' => 0,
                'sort_order' => 3,
                'created_at' => now(),
                'updated_at' => now(),
                'created_by' => null,
                'updated_by' => null,
                'deleted_by' => null,
                'deleted_at' => null,
            ],
            [
                'name' => 'Accepted',
                'description' => 'Quotation has been accepted by customer',
                'color' => '#28a745',
                'is_active' => 1,
                'is_final' => 1,
                'sort_order' => 4,
                'created_at' => now(),
                'updated_at' => now(),
                'created_by' => null,
                'updated_by' => null,
                'deleted_by' => null,
                'deleted_at' => null,
            ],
            [
                'name' => 'Rejected',
                'description' => 'Quotation has been rejected by customer',
                'color' => '#dc3545',
                'is_active' => 1,
                'is_final' => 1,
                'sort_order' => 5,
                'created_at' => now(),
                'updated_at' => now(),
                'created_by' => null,
                'updated_by' => null,
                'deleted_by' => null,
                'deleted_at' => null,
            ],
        ]);
    }
}
