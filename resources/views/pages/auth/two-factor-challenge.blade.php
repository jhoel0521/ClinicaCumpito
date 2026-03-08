<x-layouts::auth>
    <div class="flex flex-col gap-6">
        <div
            class="relative w-full h-auto"
            x-cloak
            x-data="{
                showRecoveryInput: @js($errors->has('recovery_code')),
                code: '',
                recovery_code: '',
                toggleInput() {
                    this.showRecoveryInput = ! this.showRecoveryInput

                    this.code = ''
                    this.recovery_code = ''

                    $dispatch('clear-2fa-auth-code')

                    $nextTick(() => {
                        this.showRecoveryInput
                            ? this.$refs.recovery_code?.focus()
                            : $dispatch('focus-2fa-auth-code')
                    })
                },
            }"
        >
            <div x-show="!showRecoveryInput">
                <x-auth-header
                    :title="__('Código de Autenticación')"
                    :description="__('Ingresa el código de 6 dígitos de tu aplicación autenticadora.')"
                />
            </div>

            <div x-show="showRecoveryInput">
                <x-auth-header
                    :title="__('Código de Recuperación')"
                    :description="__('Ingresa uno de tus códigos de recuperación de emergencia.')"
                />
            </div>

            <form method="POST" action="{{ route('two-factor.login.store') }}" class="flex flex-col gap-5">
                @csrf

                <div class="space-y-5">
                    <div x-show="!showRecoveryInput">
                        <div class="flex items-center justify-center py-4">
                            <flux:otp
                                x-model="code"
                                length="6"
                                name="code"
                                label="OTP Code"
                                label:sr-only
                                class="mx-auto"
                            />
                        </div>
                    </div>

                    <div x-show="showRecoveryInput">
                        <flux:input
                            type="text"
                            name="recovery_code"
                            :label="__('Código de Recuperación')"
                            x-ref="recovery_code"
                            x-bind:required="showRecoveryInput"
                            autocomplete="one-time-code"
                            x-model="recovery_code"
                        />

                        @error('recovery_code')
                            <p class="text-red-600 dark:text-red-400 text-sm mt-2 flex items-center gap-2">
                                <i class="fas fa-exclamation-circle"></i>
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <button type="submit" data-auth-button class="mt-2">
                        <i class="fas fa-arrow-right mr-2"></i>
                        {{ __('Continuar') }}
                    </button>
                </div>

                <div data-auth-divider><span>{{ __('o') }}</span></div>

                <p class="text-center text-sm text-zinc-600 dark:text-zinc-400">
                    <button
                        type="button"
                        @click="toggleInput()"
                        class="text-teal-600 dark:text-teal-400 hover:text-teal-700 dark:hover:text-teal-300 font-medium underline cursor-pointer"
                    >
                        <span x-show="!showRecoveryInput">
                            <i class="fas fa-key mr-1"></i>
                            {{ __('Usar código de recuperación') }}
                        </span>
                        <span x-show="showRecoveryInput">
                            <i class="fas fa-shield-alt mr-1"></i>
                            {{ __('Usar código de autenticación') }}
                        </span>
                    </button>
                </p>
            </form>
        </div>
    </div>
</x-layouts::auth>
