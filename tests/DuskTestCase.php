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
     * Handle del proceso del servidor Dusk iniciado por esta clase.
     * Se mantiene como static para que no sea recolectado por el GC mientras
     * corren los tests, evitando que el proceso hijo sea terminado.
     *
     * @var resource|false|null
     */
    private static mixed $duskServer = null;

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
            // Nota: serve() fue eliminado en Dusk 8; usamos proc_open directamente.
            if (! static::appIsRunning()) {
                static::startDuskServer();
                sleep(3); // Dar tiempo al servidor para arrancar
            }
        }
    }

    /**
     * Inicia `php artisan serve --env=dusk.local` como proceso en segundo plano.
     * El handle se guarda en $duskServer para evitar que el GC mate el proceso.
     */
    protected static function startDuskServer(): void
    {
        $artisan = dirname(__DIR__).DIRECTORY_SEPARATOR.'artisan';
        $pipes = [];

        self::$duskServer = proc_open(
            [PHP_BINARY, $artisan, 'serve', '--host=127.0.0.1', '--port=8000', '--env=dusk.local'],
            [
                0 => ['pipe', 'r'],
                1 => ['pipe', 'w'],
                2 => ['pipe', 'w'],
            ],
            $pipes,
            dirname(__DIR__)
        );
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
