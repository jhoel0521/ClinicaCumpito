<?php

namespace Database\Seeders;

use App\Models\Doctor;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
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

        $user = User::firstOrCreate(
            ['email' => 'admin@clinica.com'],
            [
                'name' => 'Admin Cumpito',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'phone_number' => '555-000-0001',
                'doctor_id' => $doctorProfile->id,
            ]
        );

        $user->syncRoles([$adminRole, $doctorRole, $tecnicoRole]);

        $this->command->info('✔ Usuario: admin@clinica.com / password  →  Admin | Doctor | Tecnico');
    }
}
