<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RolesAndPermissionsSeeder::class,
            MedicalConditionSeeder::class,
            LaboratoryCatalogSeeder::class,
            MedicationCatalogSeeder::class,
            VaccineCatalogSeeder::class,
            DefaultUsersSeeder::class,
            WhoDataSeeder::class,
        ]);
    }
}
