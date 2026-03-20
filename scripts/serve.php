<?php

/**
 * Encuentra un puerto disponible comenzando desde $startPort
 * y levanta php artisan serve en ese puerto.
 */
$startPort = 8000;
$port = $startPort;

while ($port < 8020) {
    $sock = @fsockopen('127.0.0.1', $port, $errno, $errstr, 0.1);
    if ($sock === false) {
        break; // puerto libre
    }
    fclose($sock);
    $port++;
}

if ($port !== $startPort) {
    echo "\033[33m⚠  Puerto {$startPort} ocupado — usando {$port}\033[0m" . PHP_EOL;
}

passthru("php artisan serve --port={$port}");
