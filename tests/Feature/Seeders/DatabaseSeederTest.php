<?php

use App\Models\Patient;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\GrowthChartTestDataSeeder;

describe('DatabaseSeeder', function () {
    it('crea los pacientes de prueba de gráficas OMS fuera de producción', function () {
        $this->seed(GrowthChartTestDataSeeder::class);

        expect(Patient::whereIn('full_name', ['Aitana Aguilar', 'Thiago Méndez'])->count())->toBe(2);
    });

    it('no crea los pacientes de prueba de gráficas OMS en producción', function () {
        $this->app->detectEnvironment(fn () => 'production');

        $this->artisan('db:seed', ['--class' => DatabaseSeeder::class, '--force' => true])->assertSuccessful();

        expect(Patient::whereIn('full_name', ['Aitana Aguilar', 'Thiago Méndez'])->count())->toBe(0);
    })->after(fn () => app()->detectEnvironment(fn () => 'testing'));

    it('no crea el admin por defecto con la contraseña conocida en producción', function () {
        $this->app->detectEnvironment(fn () => 'production');

        $this->artisan('db:seed', ['--class' => Database\Seeders\DefaultUsersSeeder::class, '--force' => true])
            ->assertSuccessful();

        $admin = App\Models\User::where('email', 'admin@clinica.com')->firstOrFail();

        expect(Illuminate\Support\Facades\Hash::check('password', $admin->password))->toBeFalse()
            ->and($admin->hasRole('Admin'))->toBeTrue();
    })->after(fn () => app()->detectEnvironment(fn () => 'testing'));
});
