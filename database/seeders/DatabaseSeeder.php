<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();
        $this->call([
            PosteSeeder::class,
            RolePermissionSeeder::class,
            UserSeeder::class,
            CompanySettingSeeder::class,
            TypesCongesSeeder::class,
            ReglesCongesSeeder::class,
            DemandesCongesSeeder::class,
            SoldesCongesSeeder::class,
            HistoriquesCongesSeeder::class,
        ]);
    }
}
