<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head')
    </head>

    <body class="min-h-screen bg-white dark:bg-zinc-800">
        <flux:sidebar
            sticky
            collapsible="mobile"
            class="border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900"
        >
            <flux:sidebar.header>
                <x-app-logo :sidebar="true" href="{{ route('dashboard') }}" wire:navigate />
                <flux:sidebar.collapse class="lg:hidden" />
            </flux:sidebar.header>

            <flux:sidebar.nav>
                <flux:sidebar.group :heading="__('Platform')" class="grid">
                    <flux:sidebar.item
                        icon="home"
                        :href="route('dashboard')"
                        :current="request()->routeIs('dashboard')"
                        wire:navigate
                    >
                        {{ __('Dashboard') }}
                    </flux:sidebar.item>
                </flux:sidebar.group>

                <flux:sidebar.group :heading="__('Módulos')" class="grid">
                    <flux:sidebar.item
                        icon="users"
                        :href="route('pacientes.index')"
                        :current="request()->routeIs('pacientes.*')"
                        wire:navigate
                    >
                        {{ __('Pacientes') }}
                    </flux:sidebar.item>

                    <flux:sidebar.item
                        icon="clipboard"
                        :href="route('consultas.index')"
                        :current="request()->routeIs('consultas.*')"
                        wire:navigate
                    >
                        {{ __('Consultas') }}
                    </flux:sidebar.item>

                    @if (isset($patient) && isset($patient->id))
                        <flux:sidebar.item
                            icon="queue-list"
                            href="{{ route('pacientes.feed', $patient) }}"
                            :current="request()->routeIs('pacientes.feed')"
                            wire:navigate
                        >
                            {{ __('Feed Historia Clínica') }}
                        </flux:sidebar.item>
                    @endif

                    <flux:sidebar.item
                        icon="clipboard-document-list"
                        :href="route('settings.prescriptions')"
                        :current="request()->routeIs('settings.prescriptions')"
                        wire:navigate
                    >
                        {{ __('Recetas predefinidas') }}
                    </flux:sidebar.item>
                </flux:sidebar.group>

                <flux:sidebar.group :heading="__('Mi cuenta')" class="grid">
                    <flux:sidebar.item
                        icon="cog-6-tooth"
                        :href="route('profile.edit')"
                        :current="request()->routeIs('profile.edit', 'doctor-profile.edit', 'clinic.edit', 'appearance.edit', 'user-password.edit', 'two-factor.show')"
                        wire:navigate
                    >
                        {{ __('Ajustes') }}
                    </flux:sidebar.item>
                </flux:sidebar.group>

                @role('Admin')
                    <flux:sidebar.group :heading="__('Configuración')" class="grid">
                        <flux:sidebar.item
                            icon="beaker"
                            :href="route('settings.catalogs.laboratories')"
                            :current="request()->routeIs('settings.catalogs.laboratories')"
                            wire:navigate
                        >
                            {{ __('Laboratorios') }}
                        </flux:sidebar.item>

                        <flux:sidebar.item
                            icon="shield-check"
                            :href="route('settings.catalogs.vaccines')"
                            :current="request()->routeIs('settings.catalogs.vaccines')"
                            wire:navigate
                        >
                            {{ __('Vacunas') }}
                        </flux:sidebar.item>

                        <flux:sidebar.item
                            icon="heart"
                            :href="route('settings.catalogs.medical-conditions')"
                            :current="request()->routeIs('settings.catalogs.medical-conditions')"
                            wire:navigate
                        >
                            {{ __('Condiciones Médicas') }}
                        </flux:sidebar.item>

                        <flux:sidebar.item
                            icon="chart-bar"
                            :href="route('settings.catalogs.oms-graficas')"
                            :current="request()->routeIs('settings.catalogs.oms-graficas*')"
                            wire:navigate
                        >
                            {{ __('OMS Gráficas') }}
                        </flux:sidebar.item>
                    </flux:sidebar.group>
                @endrole
            </flux:sidebar.nav>

            <flux:spacer />

            <flux:sidebar.nav>
                <div class="px-3 py-2">
                    <button
                        id="theme-toggle"
                        type="button"
                        x-data
                        @click="$flux.appearance = $flux.appearance === 'dark' ? 'light' : 'dark'"
                        class="w-full flex items-center justify-center gap-2 p-2 rounded-lg bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-300 hover:bg-zinc-200 dark:hover:bg-zinc-700 transition"
                        aria-label="Cambiar tema"
                    >
                        <svg
                            class="w-4 h-4 dark:hidden"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke-width="1.5"
                            stroke="currentColor"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M12 3v2.25m6.364.386-1.591 1.591M21 12h-2.25m-.386 6.364-1.591-1.591M12 18.75V21m-4.773-4.227-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0Z"
                            />
                        </svg>
                        <svg
                            class="w-4 h-4 hidden dark:inline"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke-width="1.5"
                            stroke="currentColor"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M21.752 15.002A9.72 9.72 0 0 1 18 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 0 0 3 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 0 0 9.002-5.998Z"
                            />
                        </svg>
                        <span class="text-sm font-medium">Tema</span>
                    </button>
                </div>
            </flux:sidebar.nav>

            <x-desktop-user-menu class="hidden lg:block" :name="auth()->user()->name" />
        </flux:sidebar>

        <!-- Mobile User Menu -->
        <flux:header class="lg:hidden">
            <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />

            <flux:spacer />

            <flux:dropdown position="top" align="end">
                <flux:profile :initials="auth()->user()->initials()" icon-trailing="chevron-down" />

                <flux:menu>
                    <flux:menu.radio.group>
                        <div class="p-0 text-sm font-normal">
                            <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                                <flux:avatar :name="auth()->user()->name" :initials="auth()->user()->initials()" />

                                <div class="grid flex-1 text-start text-sm leading-tight">
                                    <flux:heading class="truncate">{{ auth()->user()->name }}</flux:heading>
                                    <flux:text class="truncate">{{ auth()->user()->email }}</flux:text>
                                </div>
                            </div>
                        </div>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <flux:menu.radio.group>
                        <flux:menu.item :href="route('profile.edit')" icon="cog" wire:navigate>
                            {{ __('Settings') }}
                        </flux:menu.item>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <form method="POST" action="{{ route('logout') }}" class="w-full">
                        @csrf
                        <flux:menu.item
                            as="button"
                            type="submit"
                            icon="arrow-right-start-on-rectangle"
                            class="w-full cursor-pointer"
                            data-test="logout-button"
                        >
                            {{ __('Log Out') }}
                        </flux:menu.item>
                    </form>
                </flux:menu>
            </flux:dropdown>
        </flux:header>

        <flux:header
            container
            class="max-lg:hidden border-b border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900"
        >
            <flux:navbar class="-mb-px">
                <flux:navbar.item
                    icon="home"
                    :href="route('dashboard')"
                    :current="request()->routeIs('dashboard')"
                    wire:navigate
                >
                    {{ __('Dashboard') }}
                </flux:navbar.item>
                <flux:navbar.item
                    icon="users"
                    :href="route('pacientes.index')"
                    :current="request()->routeIs('pacientes.*')"
                    wire:navigate
                >
                    {{ __('Pacientes') }}
                </flux:navbar.item>
                <flux:navbar.item
                    icon="clipboard"
                    :href="route('consultas.index')"
                    :current="request()->routeIs('consultas.*')"
                    wire:navigate
                >
                    {{ __('Consultas') }}
                </flux:navbar.item>
                <flux:navbar.item
                    icon="clipboard-document-list"
                    :href="route('settings.prescriptions')"
                    :current="request()->routeIs('settings.prescriptions')"
                    wire:navigate
                >
                    {{ __('Recetas predefinidas') }}
                </flux:navbar.item>
            </flux:navbar>

            <flux:spacer />

            <x-desktop-user-menu class="hidden lg:block" :name="auth()->user()->name" />
        </flux:header>

        {{ $slot }}

        @if (session('success') || session('error'))
            <script>
                (function () {
                    var type = {{ session('error') ? "'error'" : "'success'" }};
                    var message = @js(session('error') ?? session('success'));
                    window.dispatchEvent(new CustomEvent('notify', { detail: { type: type, message: message } }));
                })();
            </script>
        @endif

        @fluxScripts
    </body>
</html>
