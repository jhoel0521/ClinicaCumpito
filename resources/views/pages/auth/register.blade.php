<x-layouts::auth>
    <div class="flex flex-col gap-6">
        <x-auth-header :title="__('Crear Cuenta')" :description="__('Registrarse en VitalTrack Pediátrico')" />

        <!-- Session Status -->
        <x-auth-session-status class="text-center" :status="session('status')" />

        <form method="POST" action="{{ route('register.store') }}" class="flex flex-col gap-5">
            @csrf
            <!-- Name -->
            <flux:input
                name="name"
                :label="__('Nombre Completo')"
                :value="old('name')"
                type="text"
                required
                autofocus
                autocomplete="name"
                :placeholder="__('Dr. Juan Pérez')"
            />

            <!-- Email Address -->
            <flux:input
                name="email"
                :label="__('Correo Electrónico')"
                :value="old('email')"
                type="email"
                required
                autocomplete="email"
                placeholder="doctor@email.com"
            />

            <!-- Password -->
            <flux:input
                name="password"
                :label="__('Contraseña')"
                type="password"
                required
                autocomplete="new-password"
                :placeholder="__('••••••••')"
                viewable
            />

            <!-- Confirm Password -->
            <flux:input
                name="password_confirmation"
                :label="__('Confirmar Contraseña')"
                type="password"
                required
                autocomplete="new-password"
                :placeholder="__('••••••••')"
                viewable
            />

            <button type="submit" data-auth-button class="mt-2" data-test="register-user-button">
                <i class="fas fa-user-plus mr-2"></i>
                {{ __('Crear mi Cuenta') }}
            </button>
        </form>

        <!-- Divider -->
        <div data-auth-divider><span>{{ __('o') }}</span></div>

        <!-- Login Link -->
        <p data-auth-footer>
            {{ __('¿Ya tienes cuenta?') }}
            <a href="{{ route('login') }}" wire:navigate>{{ __('Inicia sesión') }}</a>
        </p>
    </div>
</x-layouts::auth>
