<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="antialiased" data-auth-layout>
        <div data-auth-card>
            <!-- Logo y Link a Home -->
            <a href="{{ route('home') }}" class="flex flex-col items-center gap-3 mb-8 group" wire:navigate>
                <div class="h-12 w-12 bg-gradient-to-br from-teal-500 to-teal-600 dark:from-teal-400 dark:to-teal-500 rounded-2xl flex items-center justify-center group-hover:shadow-lg transition-all">
                    <x-app-logo-icon class="size-6 fill-white" />
                </div>
                <div class="text-center">
                    <h1 class="text-lg font-bold text-zinc-900 dark:text-white">{{ config('app.name', 'VitalTrack') }}</h1>
                    <p class="text-xs text-zinc-500 dark:text-zinc-400">Gestión Clínica Pediátrica</p>
                </div>
            </a>

            <!-- Contenido Principal -->
            <div class="flex flex-col gap-6">
                {{ $slot }}
            </div>
        </div>

        @fluxScripts
    </body>
</html>
