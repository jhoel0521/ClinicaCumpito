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
});
