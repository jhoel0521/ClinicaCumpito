<?php

use App\Models\Vaccine;
use Database\Seeders\VaccineCatalogSeeder;

describe('VaccineCatalogSeeder (esquema ajustado por la clienta)', function () {

    beforeEach(function () {
        $this->seed(VaccineCatalogSeeder::class);
    });

    it('ya no incluye Hepatitis B en el esquema', function () {
        expect(Vaccine::where('name', 'Hepatitis B')->exists())->toBeFalse();
    });

    it('mantiene BCG al nacer', function () {
        expect(
            Vaccine::where('name', 'BCG')->where('min_age_months', 0)->exists(),
        )->toBeTrue();
    });

    it('el esquema de Influenza es 6 meses, 12 meses y anual', function () {
        $influenza = Vaccine::where('name', 'Influenza')
            ->orderBy('dose_sequence')
            ->get(['dose_sequence', 'min_age_months', 'recommended_age']);

        expect($influenza)->toHaveCount(3)
            ->and($influenza[0]->dose_sequence)->toBe(1)
            ->and($influenza[0]->min_age_months)->toBe(6)
            ->and($influenza[1]->dose_sequence)->toBe(2)
            ->and($influenza[1]->min_age_months)->toBe(12)
            ->and($influenza[2]->dose_sequence)->toBe(3)
            ->and($influenza[2]->recommended_age)->toContain('Anual');
    });

    it('es idempotente con el nuevo esquema', function () {
        $this->seed(VaccineCatalogSeeder::class);

        expect(Vaccine::where('name', 'Hepatitis B')->exists())->toBeFalse()
            ->and(Vaccine::where('name', 'Influenza')->count())->toBe(3);
    });
});
