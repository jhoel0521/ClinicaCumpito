<?php

namespace App\Providers;

use Native\Desktop\Contracts\ProvidesPhpIni;
use Native\Desktop\Facades\Window;

class NativeAppServiceProvider implements ProvidesPhpIni
{
    public function boot(): void
    {
        Window::open()
            ->title('VitalTrack')
            ->width(1280)
            ->height(800)
            ->minWidth(960)
            ->minHeight(600)
            ->rememberState();
    }

    /** @return array<string, string> */
    public function phpIni(): array
    {
        return [
            'memory_limit' => '256M',
            'upload_max_filesize' => '20M',
            'post_max_size' => '25M',
        ];
    }
}
