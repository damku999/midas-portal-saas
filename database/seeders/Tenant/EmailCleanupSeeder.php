<?php

namespace Database\Seeders\Tenant;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EmailCleanupSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Fix duplicate emails by appending customer ID
     * This prepares data for unique email constraint
     */
    public function run(): void
    {
        $this->fixDuplicateEmails();
    }

    /**
     * Fix duplicate emails by appending customer ID
     */
    private function fixDuplicateEmails(): void
    {
        $duplicateEmails = DB::table('customers')
            ->select('email', DB::raw('COUNT(*) as count'))
            ->whereNotNull('email')
            ->groupBy('email')
            ->having('count', '>', 1)
            ->get();

        foreach ($duplicateEmails as $duplicate) {
            $customers = DB::table('customers')
                ->where('email', $duplicate->email)
                ->orderBy('id')
                ->get();

            // Keep first customer with original email, update others
            foreach ($customers->skip(1) as $index => $customer) {
                $newEmail = str_replace('@', "+{$customer->id}@", $customer->email);
                DB::table('customers')
                    ->where('id', $customer->id)
                    ->update(['email' => $newEmail]);
            }
        }
    }
}
