<?php

use App\Models\ClinicSetting;
use App\Models\Consultation;
use App\Models\OmsCatalogoGrafica;
use App\Models\PrescriptionTemplate;
use App\Models\User;
use App\Models\Vaccine;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\GrowthChartTestDataSeeder;
use Illuminate\Support\Facades\Hash;

/*
 * Regla: los seeders pueden correr 1 o 1 millón de veces sin borrar datos,
 * sin pisar ediciones hechas en la aplicación y sin cambiar contraseñas.
 */

function seedAll(object $test): void
{
    $test->artisan('db:seed', ['--class' => DatabaseSeeder::class, '--force' => true])->assertSuccessful();
}

beforeEach(function () {
    $this->app->detectEnvironment(fn () => 'production');
    seedAll($this);
});

afterEach(function () {
    app()->detectEnvironment(fn () => 'testing');
});

it('no pisa la configuración de la clínica', function () {
    ClinicSetting::query()->firstOrFail()->update(['phone' => '76387108', 'logo_path' => 'clinic/logo.png']);

    seedAll($this);

    $clinic = ClinicSetting::query()->firstOrFail();
    expect(ClinicSetting::count())->toBe(1)
        ->and($clinic->phone)->toBe('76387108')
        ->and($clinic->logo_path)->toBe('clinic/logo.png');
});

it('no cambia la contraseña ni los roles del admin existente', function () {
    $admin = User::where('email', 'admin@clinica.com')->firstOrFail();
    $admin->password = 'ClaveDeSoporte#2026';
    $admin->save();
    $admin->syncRoles(['Admin']);

    seedAll($this);

    $admin->refresh();
    expect(Hash::check('ClaveDeSoporte#2026', $admin->password))->toBeTrue()
        ->and($admin->getRoleNames()->all())->toBe(['Admin']);
});

it('respeta las ediciones de la doctora en las plantillas de receta', function () {
    $template = PrescriptionTemplate::query()->with('items')->firstOrFail();
    $item = $template->items->first();
    $item->update(['dose' => 'dosis ajustada por la doctora']);
    $itemCount = $template->items->count();

    seedAll($this);

    expect($item->fresh()?->dose)->toBe('dosis ajustada por la doctora')
        ->and($template->items()->count())->toBe($itemCount);
});

it('no borra vacunas cargadas por el admin ni pisa el catálogo OMS', function () {
    Vaccine::create([
        'name' => 'Hepatitis B',
        'disease_prevented' => 'Hepatitis B',
        'recommended_age' => 'Al nacer',
        'dose_sequence' => 1,
        'min_age_months' => 0,
    ]);
    $grafica = OmsCatalogoGrafica::query()->firstOrFail();
    $grafica->update(['nombre' => 'Nombre editado por el admin']);

    seedAll($this);

    expect(Vaccine::where('name', 'Hepatitis B')->exists())->toBeTrue()
        ->and($grafica->fresh()?->nombre)->toBe('Nombre editado por el admin');
});

it('no duplica registros al correr varias veces', function () {
    $counts = fn () => [Vaccine::count(), PrescriptionTemplate::count(), OmsCatalogoGrafica::count(), User::count()];
    $before = $counts();

    seedAll($this);
    seedAll($this);

    expect($counts())->toBe($before);
});

it('el seeder de datos de prueba no toca nada en producción aunque se invoque directo', function () {
    $this->artisan('db:seed', ['--class' => GrowthChartTestDataSeeder::class, '--force' => true])->assertSuccessful();

    expect(Consultation::count())->toBe(0);
});
