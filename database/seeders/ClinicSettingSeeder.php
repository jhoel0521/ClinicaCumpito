<?php

namespace Database\Seeders;

use App\Models\ClinicSetting;
use Illuminate\Database\Seeder;

class ClinicSettingSeeder extends Seeder
{
    /**
     * No destructivo: solo crea la configuración si no existe. Nunca pisa el
     * nombre, teléfono, WhatsApp o logo que la clínica configuró.
     */
    public function run(): void
    {
        if (ClinicSetting::query()->exists()) {
            $this->command->info('✔ Configuración de clínica ya existente: sin cambios.');

            return;
        }

        ClinicSetting::create([
            'name' => 'Clínica Cumpito',
            'address' => 'Santa Cruz, Bolivia',
            'phone' => null,
            'whatsapp' => null,
            'logo_path' => null,
        ]);

        $this->command->info('✔ Configuración de clínica: Clínica Cumpito / Santa Cruz, Bolivia.');
    }
}
