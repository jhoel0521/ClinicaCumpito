<?php

namespace Database\Seeders;

use App\Models\Doctor;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

/**
 * Crea dos usuarios predeterminados para el entorno de desarrollo:
 *
 *  - admin@clinica.com   / password  → rol Admin
 *  - doctor@clinica.com  / password  → rol Doctor, con perfil de médico
 *
 * Este seeder es idempotente: usa firstOrCreate para no duplicar registros.
 */
class DefaultUsersSeeder extends Seeder
{
    public function run(): void
    {
        $adminRole = Role::firstOrCreate(['name' => 'Admin', 'guard_name' => 'web']);
        $doctorRole = Role::firstOrCreate(['name' => 'Doctor', 'guard_name' => 'web']);

        // ── Admin ─────────────────────────────────────────────────────────────
        $admin = User::firstOrCreate(
            ['email' => 'admin@clinica.com'],
            [
                'name' => 'Administrador',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'phone_number' => '000-000-0000',
            ]
        );
        $admin->syncRoles([$adminRole, $doctorRole]);

        // ── Doctor 1 ──────────────────────────────────────────────────────────
        $doctor1 = Doctor::firstOrCreate(
            ['license_number' => 'MP-001'],
            [
                'full_name' => 'Dr. Carlos García',
                'specialty' => 'Pediatría',
                'active' => true,
            ]
        );

        $user1 = User::firstOrCreate(
            ['email' => 'doctor@clinica.com'],
            [
                'name' => 'Dr. Carlos García',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'phone_number' => '555-100-0001',
                'doctor_id' => $doctor1->id,
            ]
        );
        $user1->syncRoles([$doctorRole]);

        // ── Doctor 2 ──────────────────────────────────────────────────────────
        $doctor2 = Doctor::firstOrCreate(
            ['license_number' => 'MP-002'],
            [
                'full_name' => 'Dra. Ana López',
                'specialty' => 'Medicina General',
                'active' => true,
            ]
        );

        $user2 = User::firstOrCreate(
            ['email' => 'doctora@clinica.com'],
            [
                'name' => 'Dra. Ana López',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'phone_number' => '555-100-0002',
                'doctor_id' => $doctor2->id,
            ]
        );
        $user2->syncRoles([$doctorRole]);

        $this->command->info('✔ Usuarios por defecto creados:');
        $this->command->table(
            ['Email', 'Rol', 'Contraseña'],
            [
                ['admin@clinica.com', 'Admin', 'password'],
                ['doctor@clinica.com', 'Doctor (Dr. Carlos García)', 'password'],
                ['doctora@clinica.com', 'Doctor (Dra. Ana López)', 'password'],
            ]
        );
    }
}
