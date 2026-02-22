<x-layouts::auth>
    <div class="flex flex-col gap-6">
        <div data-auth-header>
            <h1 data-auth-title>{{ __('Verificar Email') }}</h1>
            <p data-auth-description>{{ __('Haz clic en el enlace que enviamos a tu correo para verificar tu cuenta.') }}</p>
        </div>

        @if (session('status') == 'verification-link-sent')
            <div class="bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-700 rounded-lg p-4 text-center">
                <p class="text-green-700 dark:text-green-300 font-medium text-sm">
                    <i class="fas fa-check-circle mr-2"></i>
                    {{ __('Se envió un nuevo enlace de verificación a tu correo.') }}
                </p>
            </div>
        @endif

        <div class="flex flex-col gap-3">
            <form method="POST" action="{{ route('verification.send') }}" class="w-full">
                @csrf
                <button type="submit" data-auth-button>
                    <i class="fas fa-envelope mr-2"></i>{{ __('Reenviar Email de Verificación') }}
                </button>
            </form>

            <form method="POST" action="{{ route('logout') }}" class="w-full">
                @csrf
                <button type="submit" class="w-full py-2.5 px-4 text-zinc-700 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-800 rounded-lg transition-colors font-medium text-sm" data-test="logout-button">
                    <i class="fas fa-sign-out-alt mr-2"></i>{{ __('Cerrar Sesión') }}
                </button>
            </form>
        </div>
    </div>
</x-layouts::auth>
