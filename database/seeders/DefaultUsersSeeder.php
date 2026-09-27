<?php

namespace Database\Seeders;

use App\Models\Doctor;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class DefaultUsersSeeder extends Seeder
{
    public function run(): void
    {
        $adminRole = Role::firstOrCreate(['name' => 'Admin',   'guard_name' => 'web']);
        $doctorRole = Role::firstOrCreate(['name' => 'Doctor',  'guard_name' => 'web']);
        $tecnicoRole = Role::firstOrCreate(['name' => 'Tecnico', 'guard_name' => 'web']);

        $doctorProfile = Doctor::firstOrCreate(
            ['license_number' => 'MP-001'],
            [
                'full_name' => 'Dr. Admin Cumpito',
                'specialty' => 'Pediatría',
                'active' => true,
            ]
        );

        // En producción nunca se usa la contraseña conocida "password" (el
        // repositorio es visible): se toma DEFAULT_ADMIN_PASSWORD o se genera
        // una aleatoria que se muestra una sola vez.
        $password = (string) (config('app.default_admin_password')
            ?: (app()->isProduction() ? Str::password(20) : 'password'));

        $user = User::firstOrCreate(
            ['email' => 'admin@clinica.com'],
            [
                'name' => 'Admin Cumpito',
                'password' => Hash::make($password),
                'email_verified_at' => now(),
                'phone_number' => '555-000-0001',
                'doctor_id' => $doctorProfile->id,
            ]
        );

        // No destructivo: un usuario existente no se toca (ni contraseña ni roles).
        if ($user->wasRecentlyCreated) {
            $user->syncRoles([$adminRole, $doctorRole, $tecnicoRole]);

            $this->command->info("✔ Usuario: admin@clinica.com / {$password}  →  Admin | Doctor | Tecnico");
            if (app()->isProduction()) {
                $this->command->warn('Guarda esta contraseña ahora: no se volverá a mostrar (reseteo: php artisan soporte:resetear-password).');
            }
        } else {
            $this->command->info('✔ Usuario admin@clinica.com ya existía: contraseña sin cambios.');
        }
    }
}
