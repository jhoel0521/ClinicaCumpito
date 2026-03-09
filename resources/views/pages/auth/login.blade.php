<x-layouts::auth>
    <div class="flex flex-col gap-6">
        <x-auth-header
            :title="__('Bienvenido de Vuelta')"
            :description="__('Iniciar sesión en VitalTrack Pediátrico')"
        />

        <!-- Session Status -->
        <x-auth-session-status class="text-center" :status="session('status')" />

        <form method="POST" action="{{ route('login.store') }}" class="flex flex-col gap-5">
            @csrf

            <!-- Email Address -->
            <flux:input
                name="email"
                :label="__('Correo Electrónico')"
                :value="old('email')"
                type="email"
                required
                autofocus
                autocomplete="email"
                :placeholder="__('tu@email.com')"
            />

            <!-- Password -->
            <flux:input
                name="password"
                :label="__('Contraseña')"
                type="password"
                required
                autocomplete="current-password"
                :placeholder="__('••••••••')"
                viewable
            />

            <!-- Remember Me -->
            <div class="flex items-center justify-between">
                <label class="flex items-center gap-3 cursor-pointer group">
                    <input
                        type="checkbox"
                        name="remember"
                        :checked="old('remember')"
                        class="w-5 h-5 rounded-md border-2 border-zinc-300 dark:border-zinc-600 cursor-pointer accent-teal-600 dark:accent-teal-400 transition-all group-hover:border-teal-400"
                    />
                    <span class="font-medium text-zinc-700 dark:text-zinc-300">{{ __('Recuérdame') }}</span>
                </label>
                @if (Route::has('password.request'))
                    <a data-auth-link class="text-xs" href="{{ route('password.request') }}" wire:navigate>
                        {{ __('¿Olvidó su contraseña?') }}
                    </a>
                @endif
            </div>

            <button type="submit" data-auth-button class="mt-2" data-test="login-button">
                <i class="fas fa-sign-in-alt mr-2"></i>
                {{ __('Iniciar Sesión') }}
            </button>
        </form>
    </div>
</x-layouts::auth>
