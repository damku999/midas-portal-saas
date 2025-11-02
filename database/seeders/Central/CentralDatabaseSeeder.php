<?php

namespace Database\Seeders\Central;

use Illuminate\Database\Seeder;

class CentralDatabaseSeeder extends Seeder
{
    /**
     * Seed the central database (multi-tenancy management).
     *
     * @return void
     */
    public function run()
    {
        $this->call([
            CentralAdminSeeder::class,
        ]);
    }
}
