<?php

namespace Database\Seeders\Tenant;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Creates admin user using custom data from config or defaults to hardcoded values.
     * Config can be set temporarily via: config(['tenant.admin' => [...]])
     *
     * @return void
     */
    public function run()
    {
        // Check if admin user already exists (to avoid duplicates)
        $existingAdmin = User::where('role_id', 1)->first();

        if ($existingAdmin) {
            $this->command->info('Admin user already exists: '.$existingAdmin->email);
            return;
        }

        // Get admin data from config or use defaults
        $adminData = config('tenant.admin', [
            'first_name' => 'Darshan',
            'last_name' => 'Baraiya',
            'email' => 'webmonks.in@gmail.com',
            'mobile_number' => '8000071314',
            'password' => 'Webmonks239#',
        ]);

        // Create Admin User
        $user = User::create([
            'first_name' => $adminData['first_name'],
            'last_name' => $adminData['last_name'],
            'email' => $adminData['email'],
            'mobile_number' => $adminData['mobile_number'] ?? null,
            'password' => Hash::make($adminData['password']),
            'role_id' => 1,
            'email_verified_at' => now(),
            'status' => 1,
            'created_at' => now(),
            'updated_at' => now(),
            'created_by' => 1,
            'updated_by' => 1,
        ]);

        // Assign Admin role to user using Spatie Permission system
        $adminRole = Role::where('name', 'Admin')->first();

        if ($adminRole) {
            $user->assignRole($adminRole);
            $this->command->info('Admin role assigned to user: '.$user->email);
        } else {
            $this->command->warn('Admin role not found. User created without role assignment.');
        }
    }
}
