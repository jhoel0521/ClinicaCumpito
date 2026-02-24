<?php

namespace App\Providers;

use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(
            \App\Contracts\DoctorServiceContract::class,
            \App\Services\DoctorService::class
        );

        $this->app->bind(
            \App\Contracts\CatalogServiceContract::class,
            \App\Services\CatalogService::class
        );

        $this->app->bind(
            \App\Contracts\TemplateServiceContract::class,
            \App\Services\TemplateService::class
        );

        $this->app->bind(
            \App\Contracts\ConsultationServiceContract::class,
            \App\Services\ConsultationService::class
        );

        $this->app->bind(
            \App\Contracts\VitalSignServiceContract::class,
            \App\Services\VitalSignService::class
        );

        $this->app->bind(
            \App\Contracts\SoapNoteServiceContract::class,
            \App\Services\SoapNoteService::class
        );

        $this->app->bind(
            \App\Contracts\PatientVaccineServiceContract::class,
            \App\Services\PatientVaccineService::class
        );

        $this->app->bind(
            \App\Contracts\PrescriptionServiceContract::class,
            \App\Services\PrescriptionService::class
        );

        $this->app->bind(
            \App\Contracts\PrescriptionItemServiceContract::class,
            \App\Services\PrescriptionItemService::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureDefaults();
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(
            fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null
        );
    }
}
