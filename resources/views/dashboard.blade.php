<x-layouts::app :title="__('Dashboard')">
    <div class="flex h-full w-full flex-1 flex-col gap-6 rounded-xl p-6">
        <!-- Encabezado -->
        <div>
            <h1 class="text-4xl font-bold text-gray-900 dark:text-white">Bienvenido, {{ auth()->user()->name }}</h1>
            <p class="text-gray-600 dark:text-gray-400 mt-2">Panel de control de la Clínica Cumpito</p>
        </div>

        <!-- Grid de acceso rápido -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <!-- Card: Pacientes -->
            <a
                href="{{ route('pacientes.index') }}"
                class="group relative overflow-hidden rounded-xl border border-teal-200 dark:border-teal-800 bg-gradient-to-br from-teal-50 to-teal-100 dark:from-teal-900 dark:to-teal-800 p-6 shadow-sm hover:shadow-md transition-all hover:border-teal-400 dark:hover:border-teal-600"
            >
                <div
                    class="absolute -right-8 -top-8 h-32 w-32 bg-teal-200 dark:bg-teal-700 rounded-full opacity-10 group-hover:opacity-20 transition-opacity"
                ></div>
                <div class="relative z-10">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-2xl font-bold text-teal-700 dark:text-teal-200">Pacientes</h2>
                        <i
                            class="fas fa-users text-3xl text-teal-400 dark:text-teal-500 opacity-50 group-hover:opacity-100 transition-opacity"
                        ></i>
                    </div>
                    <p class="text-teal-600 dark:text-teal-300 text-sm mb-4">
                        Gestión de datos de pacientes, historial médico y screening
                    </p>
                    <div class="flex gap-2">
                        <span
                            class="inline-block bg-teal-200 dark:bg-teal-800 text-teal-800 dark:text-teal-200 text-xs font-semibold px-3 py-1 rounded-full"
                        >
                            Crear
                        </span>
                        <span
                            class="inline-block bg-teal-200 dark:bg-teal-800 text-teal-800 dark:text-teal-200 text-xs font-semibold px-3 py-1 rounded-full"
                        >
                            Listar
                        </span>
                        <span
                            class="inline-block bg-teal-200 dark:bg-teal-800 text-teal-800 dark:text-teal-200 text-xs font-semibold px-3 py-1 rounded-full"
                        >
                            Editar
                        </span>
                    </div>
                </div>
            </a>

            <!-- Card: Consultas (disabled) -->
            <div
                class="group relative overflow-hidden rounded-xl border border-gray-300 dark:border-gray-600 bg-gradient-to-br from-gray-50 to-gray-100 dark:from-gray-800 dark:to-gray-700 p-6 shadow-sm opacity-75"
            >
                <div
                    class="absolute -right-8 -top-8 h-32 w-32 bg-gray-300 dark:bg-gray-600 rounded-full opacity-5"
                ></div>
                <div class="relative z-10">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-2xl font-bold text-gray-500 dark:text-gray-400">Consultas</h2>
                        <i class="fas fa-stethoscope text-3xl text-gray-300 dark:text-gray-600"></i>
                    </div>
                    <p class="text-gray-500 dark:text-gray-400 text-sm mb-4">
                        Gestión de consultas digitales y presenciales
                    </p>
                    <div
                        class="inline-block bg-gray-300 dark:bg-gray-600 text-gray-600 dark:text-gray-300 text-xs font-semibold px-3 py-1 rounded-full"
                    >
                        Próxima Fase
                    </div>
                </div>
            </div>

            <!-- Card: Reportes (disabled) -->
            <div
                class="group relative overflow-hidden rounded-xl border border-gray-300 dark:border-gray-600 bg-gradient-to-br from-gray-50 to-gray-100 dark:from-gray-800 dark:to-gray-700 p-6 shadow-sm opacity-75"
            >
                <div
                    class="absolute -right-8 -top-8 h-32 w-32 bg-gray-300 dark:bg-gray-600 rounded-full opacity-5"
                ></div>
                <div class="relative z-10">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-2xl font-bold text-gray-500 dark:text-gray-400">Reportes</h2>
                        <i class="fas fa-chart-bar text-3xl text-gray-300 dark:text-gray-600"></i>
                    </div>
                    <p class="text-gray-500 dark:text-gray-400 text-sm mb-4">Generación de reportes y estadísticas</p>
                    <div
                        class="inline-block bg-gray-300 dark:bg-gray-600 text-gray-600 dark:text-gray-300 text-xs font-semibold px-3 py-1 rounded-full"
                    >
                        Próxima Fase
                    </div>
                </div>
            </div>
        </div>

        <!-- Estadísticas rápidas -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="rounded-xl bg-white dark:bg-zinc-800 border border-gray-100 dark:border-zinc-700 p-6 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-600 dark:text-gray-400 text-sm font-medium">Total Pacientes</p>
                        <p class="text-4xl font-bold text-teal-700 dark:text-teal-400 mt-2">
                            {{ App\Models\Patient::whereNull('deleted_at')->count() }}
                        </p>
                    </div>
                    <i class="fas fa-user-friends text-5xl text-teal-200 dark:text-teal-900"></i>
                </div>
            </div>

            <div class="rounded-xl bg-white dark:bg-zinc-800 border border-gray-100 dark:border-zinc-700 p-6 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-600 dark:text-gray-400 text-sm font-medium">Consultas Este Mes</p>
                        <p class="text-4xl font-bold text-blue-700 dark:text-blue-400 mt-2">
                            {{ App\Models\Consultation::whereMonth('created_at', date('m'))->whereYear('created_at', date('Y'))->count() }}
                        </p>
                    </div>
                    <i class="fas fa-calendar-check text-5xl text-blue-200 dark:text-blue-900"></i>
                </div>
            </div>

            <div class="rounded-xl bg-white dark:bg-zinc-800 border border-gray-100 dark:border-zinc-700 p-6 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-600 dark:text-gray-400 text-sm font-medium">Usuarios Activos</p>
                        <p class="text-4xl font-bold text-purple-700 dark:text-purple-400 mt-2">
                            {{ App\Models\User::count() }}
                        </p>
                    </div>
                    <i class="fas fa-user-check text-5xl text-purple-200 dark:text-purple-900"></i>
                </div>
            </div>
        </div>

        <!-- Acciones rápidas -->
        <div class="rounded-xl bg-white dark:bg-zinc-800 border border-gray-100 dark:border-zinc-700 p-6 shadow-sm">
            <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">Acciones Rápidas</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <a
                    href="{{ route('pacientes.create') }}"
                    class="flex items-center gap-3 p-4 rounded-lg bg-teal-50 dark:bg-teal-900 border border-teal-200 dark:border-teal-800 hover:bg-teal-100 dark:hover:bg-teal-800 transition group"
                >
                    <i class="fas fa-user-plus text-teal-600 dark:text-teal-400 text-2xl"></i>
                    <div>
                        <p class="font-semibold text-teal-900 dark:text-teal-200">Nuevo Paciente</p>
                        <p class="text-xs text-teal-700 dark:text-teal-400">Crear registro</p>
                    </div>
                </a>

                <a
                    href="{{ route('pacientes.index') }}"
                    class="flex items-center gap-3 p-4 rounded-lg bg-blue-50 dark:bg-blue-900 border border-blue-200 dark:border-blue-800 hover:bg-blue-100 dark:hover:bg-blue-800 transition group"
                >
                    <i class="fas fa-list text-blue-600 dark:text-blue-400 text-2xl"></i>
                    <div>
                        <p class="font-semibold text-blue-900 dark:text-blue-200">Ver Pacientes</p>
                        <p class="text-xs text-blue-700 dark:text-blue-400">Listar todos</p>
                    </div>
                </a>

                <div
                    class="flex items-center gap-3 p-4 rounded-lg bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 opacity-50 cursor-not-allowed"
                >
                    <i class="fas fa-plus-square text-gray-400 dark:text-gray-500 text-2xl"></i>
                    <div>
                        <p class="font-semibold text-gray-700 dark:text-gray-400">Nueva Consulta</p>
                        <p class="text-xs text-gray-600 dark:text-gray-500">Próximamente</p>
                    </div>
                </div>

                <div
                    class="flex items-center gap-3 p-4 rounded-lg bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 opacity-50 cursor-not-allowed"
                >
                    <i class="fas fa-file-pdf text-gray-400 dark:text-gray-500 text-2xl"></i>
                    <div>
                        <p class="font-semibold text-gray-700 dark:text-gray-400">Generar Reporte</p>
                        <p class="text-xs text-gray-600 dark:text-gray-500">Próximamente</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Info cards -->
        <div class="rounded-xl bg-blue-50 dark:bg-blue-900 border border-blue-200 dark:border-blue-800 p-6">
            <div class="flex items-start gap-4">
                <i class="fas fa-info-circle text-2xl text-blue-600 dark:text-blue-400 mt-1"></i>
                <div>
                    <h4 class="font-bold text-blue-900 dark:text-blue-200">Información del Sistema</h4>
                    <p class="text-blue-800 dark:text-blue-300 text-sm mt-2">
                        Eres un usuario de la Clínica Cumpito. Actualmente puedes gestionar pacientes de forma completa
                        (crear, ver, editar, eliminar). Las próximas fases incluirán gestión de consultas, generación de
                        reportes y análisis estadísticos.
                    </p>
                </div>
            </div>
        </div>
    </div>
</x-layouts::app>
