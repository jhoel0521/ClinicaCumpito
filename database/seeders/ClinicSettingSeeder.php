<?php

namespace Database\Seeders;

use App\Models\ClinicSetting;
use Illuminate\Database\Seeder;

class ClinicSettingSeeder extends Seeder
{
    public function run(): void
    {
        ClinicSetting::updateOrCreate([], [
            'name' => 'VitalTrack',
            'address' => 'Santa Cruz, Bolivia',
            'phone' => null,
            'whatsapp' => null,
            'logo_path' => null,
        ]);

        $this->command->info('✔ Configuración de clínica: VitalTrack / Santa Cruz, Bolivia.');
    }
}
