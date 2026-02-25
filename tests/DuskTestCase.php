<?php

namespace Tests;

use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Illuminate\Support\Collection;
use Laravel\Dusk\TestCase as BaseTestCase;
use PHPUnit\Framework\Attributes\BeforeClass;

abstract class DuskTestCase extends BaseTestCase
{
    /**
     * Prepare for Dusk test execution.
     * Inicia ChromeDriver y el servidor de la app si no está corriendo.
     */
    #[BeforeClass]
    public static function prepare(): void
    {
        if (! static::runningInSail()) {
            static::startChromeDriver(['--port=9515']);

            // Arrancar el servidor con env dusk.local (usa vitaltrack_dusk)
            // si no hay nada escuchando en el puerto 8000.
            if (! static::appIsRunning()) {
                static::serve('127.0.0.1', 8000, 'dusk.local');
                sleep(2); // Dar tiempo al servidor para arrancar
            }
        }
    }

    /**
     * Verifica si ya hay un proceso respondiendo en localhost:8000.
     */
    protected static function appIsRunning(): bool
    {
        $connection = @fsockopen('127.0.0.1', 8000, $errno, $errstr, 1);
        if ($connection) {
            fclose($connection);

            return true;
        }

        return false;
    }

    /**
     * Create the RemoteWebDriver instance.
     */
    protected function driver(): RemoteWebDriver
    {
        $options = (new ChromeOptions)->addArguments(collect([
            $this->shouldStartMaximized() ? '--start-maximized' : '--window-size=1920,1080',
            '--disable-search-engine-choice-screen',
            '--disable-smooth-scrolling',
            '--no-sandbox',
            '--disable-dev-shm-usage',
        ])->unless($this->hasHeadlessDisabled(), function (Collection $items) {
            return $items->merge([
                '--disable-gpu',
                '--headless=new',
            ]);
        })->all());

        // Usar Brave si está instalado (es Chromium, compatible con ChromeDriver)
        $bravePath = 'C:/Program Files/BraveSoftware/Brave-Browser/Application/brave.exe';
        if (file_exists($bravePath)) {
            $options->setBinary($bravePath);
        }

        return RemoteWebDriver::create(
            $_ENV['DUSK_DRIVER_URL'] ?? env('DUSK_DRIVER_URL') ?? 'http://localhost:9515',
            DesiredCapabilities::chrome()->setCapability(
                ChromeOptions::CAPABILITY, $options
            )
        );
    }
}
