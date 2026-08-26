<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            ClinicSettingSeeder::class,          // datos de la clínica (nombre, dirección, etc.)
            RolesAndPermissionsSeeder::class,   // roles: Admin, Doctor, Tecnico
            MedicalConditionSeeder::class,       // condiciones médicas pediátricas
            LaboratoryCatalogSeeder::class,      // categorías y exámenes de laboratorio
            VaccineCatalogSeeder::class,         // esquema PAI Bolivia
            DefaultUsersSeeder::class,           // 1 usuario con todos los roles
            PrescriptionTemplateSeeder::class,   // plantillas: resfriado, vómitos, dengue, vitaminas
            WhoDataSeeder::class,
        ]);

        // Pacientes de prueba para gráficas OMS (Aitana/Thiago): solo en desarrollo, nunca en producción.
        if (! app()->isProduction()) {
            $this->call(GrowthChartTestDataSeeder::class);
        }
    }
}
