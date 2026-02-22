<x-layouts::auth>
    <div class="flex flex-col gap-6">
        <x-auth-header 
            :title="__('Recuperar Contraseña')" 
            :description="__('Ingresa tu correo para recibir un enlace de restablecimiento')" 
        />

        <!-- Session Status -->
        <x-auth-session-status class="text-center" :status="session('status')" />

        <form method="POST" action="{{ route('password.email') }}" class="flex flex-col gap-5">
            @csrf

            <!-- Email Address -->
            <flux:input
                name="email"
                :label="__('Correo Electrónico')"
                type="email"
                required
                autofocus
                placeholder="tu@email.com"
            />

            <button 
                type="submit" 
                data-auth-button
                class="mt-2"
                data-test="email-password-reset-link-button"
            >
                <i class="fas fa-envelope mr-2"></i>{{ __('Enviar Enlace de Restablecimiento') }}
            </button>
        </form>

        <div data-auth-divider><span>{{ __('o') }}</span></div>

        <p data-auth-footer>
            {{ __('¿Recordaste tu contraseña?') }}
            <a href="{{ route('login') }}" wire:navigate>{{ __('Inicia sesión') }}</a>
        </p>
    </div>
</x-layouts::auth>
