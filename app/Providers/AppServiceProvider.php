<?php

namespace App\Providers;

use App\Policies\CatalogPolicy;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\URL;
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

        $this->app->bind(
            \App\Contracts\LaboratoryRequestServiceContract::class,
            \App\Services\LaboratoryRequestService::class
        );

        $this->app->bind(
            \App\Contracts\LaboratoryRequestItemServiceContract::class,
            \App\Services\LaboratoryRequestItemService::class
        );

        $this->app->bind(
            \App\Contracts\ZScoreServiceContract::class,
            \App\Services\ZScoreService::class
        );

        $this->app->bind(
            \App\Contracts\ConsultationSnapshotServiceContract::class,
            \App\Services\ConsultationSnapshotService::class
        );

        $this->app->bind(
            \App\Contracts\GrowthChartServiceContract::class,
            \App\Services\GrowthChartService::class
        );

        $this->app->bind(
            \App\Contracts\ScannedConsultationServiceContract::class,
            \App\Services\ScannedConsultationService::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureDefaults();
        $this->configureUrlScheme();

        Gate::define('manage-catalog', [CatalogPolicy::class, 'manage']);
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

    /**
     * Force URL scheme based on APP_URL.
     */
    protected function configureUrlScheme(): void
    {
        $scheme = parse_url((string) config('app.url'), PHP_URL_SCHEME);

        if ($scheme === 'https') {
            URL::forceScheme('https');

            return;
        }

        if ($scheme === 'http') {
            URL::forceScheme('http');
        }
    }
}
