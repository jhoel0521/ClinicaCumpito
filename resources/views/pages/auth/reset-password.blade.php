<x-layouts::auth>
    <div class="flex flex-col gap-6">
        <x-auth-header 
            :title="__('Nueva Contraseña')" 
            :description="__('Ingresa tu nueva contraseña a continuación')" 
        />

        <!-- Session Status -->
        <x-auth-session-status class="text-center" :status="session('status')" />

        <form method="POST" action="{{ route('password.update') }}" class="flex flex-col gap-5">
            @csrf
            <!-- Token -->
            <input type="hidden" name="token" value="{{ request()->route('token') }}">

            <!-- Email Address -->
            <flux:input
                name="email"
                value="{{ request('email') }}"
                :label="__('Correo Electrónico')"
                type="email"
                required
                autocomplete="email"
            />

            <!-- Password -->
            <flux:input
                name="password"
                :label="__('Nueva Contraseña')"
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

            <button 
                type="submit" 
                data-auth-button
                class="mt-2"
                data-test="reset-password-button"
            >
                <i class="fas fa-key mr-2"></i>{{ __('Establecer Nueva Contraseña') }}
            </button>
        </form>
    </div>
</x-layouts::auth>
