<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RolesAndPermissionsSeeder::class,   // roles: Admin, Doctor, Tecnico
            MedicalConditionSeeder::class,       // condiciones médicas pediátricas
            LaboratoryCatalogSeeder::class,      // categorías y exámenes de laboratorio
            MedicationCatalogSeeder::class,      // catálogo de medicamentos pediátricos
            VaccineCatalogSeeder::class,         // esquema PAI Bolivia
            DefaultUsersSeeder::class,           // 1 usuario con todos los roles
            PrescriptionTemplateSeeder::class,   // plantillas: resfriado, vómitos, dengue, vitaminas
            LaboratoryTemplateSeeder::class,     // plantillas de lab: rutina anual, pre-quirúrgico, chagas, etc.
            WhoDataSeeder::class,
            GrowthChartTestDataSeeder::class,
        ]);
    }
}
