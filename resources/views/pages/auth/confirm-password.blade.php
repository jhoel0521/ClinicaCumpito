<x-layouts::auth>
    <div class="flex flex-col gap-6">
        <x-auth-header
            :title="__('Confirmar Contraseña')"
            :description="__('Esta es un área segura. Por favor confirma tu contraseña para continuar.')"
        />

        <x-auth-session-status class="text-center" :status="session('status')" />

        <form method="POST" action="{{ route('password.confirm.store') }}" class="flex flex-col gap-5">
            @csrf

            <flux:input
                name="password"
                :label="__('Contraseña')"
                type="password"
                required
                autocomplete="current-password"
                :placeholder="__('••••••••')"
                viewable
            />

            <button 
                type="submit" 
                data-auth-button
                class="mt-2"
                data-test="confirm-password-button"
            >
                <i class="fas fa-shield-alt mr-2"></i>{{ __('Confirmar') }}
            </button>
        </form>
    </div>
</x-layouts::auth>
