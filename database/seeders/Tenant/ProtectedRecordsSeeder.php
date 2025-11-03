<?php

namespace Database\Seeders\Tenant;

use App\Models\Branch;
use App\Models\Broker;
use App\Models\Customer;
use App\Models\InsuranceCompany;
use App\Models\Lead;
use App\Models\ReferenceUser;
use App\Models\RelationshipManager;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProtectedRecordsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * This seeder marks all existing webmonks.in@gmail.com and *@webmonks.in
     * records as protected across all tables.
     */
    public function run(): void
    {
        $this->command->info('Starting protected records seeding...');

        $protectedEmails = config('protection.protected_emails', ['webmonks.in@gmail.com']);
        $protectedDomains = config('protection.protected_domains', ['webmonks.in']);

        $stats = [
            'users' => 0,
            'customers' => 0,
            'leads' => 0,
            'brokers' => 0,
            'branches' => 0,
            'reference_users' => 0,
            'relationship_managers' => 0,
            'insurance_companies' => 0,
        ];

        // Protect Users
        $stats['users'] += $this->protectRecords(
            User::class,
            'users',
            $protectedEmails,
            $protectedDomains
        );

        // Protect Customers
        $stats['customers'] += $this->protectRecords(
            Customer::class,
            'customers',
            $protectedEmails,
            $protectedDomains
        );

        // Protect Leads
        $stats['leads'] += $this->protectRecords(
            Lead::class,
            'leads',
            $protectedEmails,
            $protectedDomains
        );

        // Protect Brokers
        $stats['brokers'] += $this->protectRecords(
            Broker::class,
            'brokers',
            $protectedEmails,
            $protectedDomains
        );

        // Protect Branches
        $stats['branches'] += $this->protectRecords(
            Branch::class,
            'branches',
            $protectedEmails,
            $protectedDomains
        );

        // Protect Reference Users
        $stats['reference_users'] += $this->protectRecords(
            ReferenceUser::class,
            'reference_users',
            $protectedEmails,
            $protectedDomains
        );

        // Protect Relationship Managers
        $stats['relationship_managers'] += $this->protectRecords(
            RelationshipManager::class,
            'relationship_managers',
            $protectedEmails,
            $protectedDomains
        );

        // Protect Insurance Companies
        $stats['insurance_companies'] += $this->protectRecords(
            InsuranceCompany::class,
            'insurance_companies',
            $protectedEmails,
            $protectedDomains
        );

        // Display summary
        $this->command->newLine();
        $this->command->info('Protected Records Summary:');
        $this->command->table(
            ['Table', 'Protected Count'],
            collect($stats)->map(fn ($count, $table) => [$table, $count])->toArray()
        );

        $totalProtected = array_sum($stats);
        $this->command->info("Total records protected: {$totalProtected}");
    }

    /**
     * Protect records in a specific table
     */
    protected function protectRecords(
        string $modelClass,
        string $tableName,
        array $protectedEmails,
        array $protectedDomains
    ): int {
        $count = 0;

        try {
            // Build query to find matching records
            $query = DB::table($tableName)
                ->whereNull('deleted_at') // Only active records
                ->where('is_protected', false); // Not already protected

            // Add email conditions
            $query->where(function ($q) use ($protectedEmails, $protectedDomains) {
                // Check for specific protected emails
                foreach ($protectedEmails as $email) {
                    $q->orWhere('email', '=', $email);
                }

                // Check for protected domains
                foreach ($protectedDomains as $domain) {
                    $q->orWhere('email', 'LIKE', "%@{$domain}");
                }
            });

            // Get records to protect
            $records = $query->get();

            foreach ($records as $record) {
                // Determine protection reason
                $reason = $this->getProtectionReason($record->email, $protectedEmails, $protectedDomains);

                // Update the record
                DB::table($tableName)
                    ->where('id', $record->id)
                    ->update([
                        'is_protected' => true,
                        'protected_reason' => $reason,
                        'updated_at' => now(),
                    ]);

                $count++;

                $this->command->info("✓ Protected {$tableName}.{$record->id} ({$record->email}) - {$reason}");
            }
        } catch (\Exception $e) {
            $this->command->error("Error protecting {$tableName}: ".$e->getMessage());
        }

        return $count;
    }

    /**
     * Determine the protection reason based on email
     */
    protected function getProtectionReason(string $email, array $protectedEmails, array $protectedDomains): string
    {
        // Check if it's a specific protected email
        if (in_array(strtolower($email), array_map('strtolower', $protectedEmails))) {
            return 'Webmonks Super Admin Account';
        }

        // Check if it's a protected domain
        foreach ($protectedDomains as $domain) {
            if (str_ends_with(strtolower($email), '@'.strtolower($domain))) {
                return 'Webmonks Domain Protected';
            }
        }

        return 'System Protected Record';
    }
}
