<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /** Seed fictional equipment; no accounts or orders are created automatically. */
    public function run(): void
    {
        if (!app()->environment(['local', 'testing'])) {
            $this->command?->warn('Demo catalog is restricted to local and testing environments.');

            return;
        }

        $this->call(CatalogSeeder::class);
    }
}
