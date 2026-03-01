<x-layouts::app :title="__('Dashboard')">
    @php
        $totalPacientes   = App\Models\Patient::whereNull('deleted_at')->count();
        $consultasMes     = App\Models\Consultation::whereMonth('created_at', date('m'))->whereYear('created_at', date('Y'))->count();
        $consultasFin     = App\Models\Consultation::where('status', 'finalized')->count();
        $totalRecetas     = App\Models\Prescription::count();
        $totalUsuarios    = App\Models\User::count();
        $ultimasConsultas = App\Models\Consultation::with('patient')
                                ->latest()
                                ->limit(5)
                                ->get();
    @endphp

    <div class="flex h-full w-full flex-1 flex-col gap-6 rounded-xl p-6">

        {{-- Encabezado --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
                    Bienvenido, {{ auth()->user()->name }}
                </h1>
                <p class="text-gray-500 dark:text-gray-400 mt-1 text-sm">
                    Panel de control · Clínica Cumpito ·
                    <span class="text-teal-600 dark:text-teal-400 font-medium">{{ now()->isoFormat('D [de] MMMM, YYYY') }}</span>
                </p>
            </div>
            <div class="flex items-center gap-2">
                @foreach(auth()->user()->getRoleNames() as $role)
                    <span class="inline-flex items-center gap-1 rounded-full bg-teal-100 dark:bg-teal-900 text-teal-700 dark:text-teal-300 text-xs font-semibold px-3 py-1 border border-teal-200 dark:border-teal-700">
                        <i class="fas fa-shield-alt text-xs"></i> {{ $role }}
                    </span>
                @endforeach
            </div>
        </div>

        {{-- Estadísticas rápidas --}}
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4">
            <div class="rounded-xl bg-white dark:bg-zinc-800 border border-gray-100 dark:border-zinc-700 p-4 shadow-sm">
                <p class="text-gray-500 dark:text-gray-400 text-xs font-medium uppercase tracking-wide">Pacientes</p>
                <p class="text-3xl font-bold text-teal-600 dark:text-teal-400 mt-1">{{ $totalPacientes }}</p>
                <p class="text-gray-400 dark:text-gray-500 text-xs mt-1">Total registrados</p>
            </div>
            <div class="rounded-xl bg-white dark:bg-zinc-800 border border-gray-100 dark:border-zinc-700 p-4 shadow-sm">
                <p class="text-gray-500 dark:text-gray-400 text-xs font-medium uppercase tracking-wide">Consultas / mes</p>
                <p class="text-3xl font-bold text-blue-600 dark:text-blue-400 mt-1">{{ $consultasMes }}</p>
                <p class="text-gray-400 dark:text-gray-500 text-xs mt-1">{{ now()->isoFormat('MMMM YYYY') }}</p>
            </div>
            <div class="rounded-xl bg-white dark:bg-zinc-800 border border-gray-100 dark:border-zinc-700 p-4 shadow-sm">
                <p class="text-gray-500 dark:text-gray-400 text-xs font-medium uppercase tracking-wide">Finalizadas</p>
                <p class="text-3xl font-bold text-green-600 dark:text-green-400 mt-1">{{ $consultasFin }}</p>
                <p class="text-gray-400 dark:text-gray-500 text-xs mt-1">Consultas cerradas</p>
            </div>
            <div class="rounded-xl bg-white dark:bg-zinc-800 border border-gray-100 dark:border-zinc-700 p-4 shadow-sm">
                <p class="text-gray-500 dark:text-gray-400 text-xs font-medium uppercase tracking-wide">Recetas</p>
                <p class="text-3xl font-bold text-purple-600 dark:text-purple-400 mt-1">{{ $totalRecetas }}</p>
                <p class="text-gray-400 dark:text-gray-500 text-xs mt-1">Emitidas total</p>
            </div>
            <div class="rounded-xl bg-white dark:bg-zinc-800 border border-gray-100 dark:border-zinc-700 p-4 shadow-sm col-span-2 md:col-span-1">
                <p class="text-gray-500 dark:text-gray-400 text-xs font-medium uppercase tracking-wide">Usuarios</p>
                <p class="text-3xl font-bold text-amber-600 dark:text-amber-400 mt-1">{{ $totalUsuarios }}</p>
                <p class="text-gray-400 dark:text-gray-500 text-xs mt-1">Del sistema</p>
            </div>
        </div>

        {{-- Módulos activos --}}
        <div>
            <h2 class="text-lg font-bold text-gray-800 dark:text-gray-200 mb-4">Módulos Activos</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">

                {{-- Pacientes --}}
                <a href="{{ route('pacientes.index') }}"
                   class="group relative overflow-hidden rounded-xl border border-teal-200 dark:border-teal-800 bg-gradient-to-br from-teal-50 to-teal-100 dark:from-teal-900/50 dark:to-teal-800/50 p-5 shadow-sm hover:shadow-md transition-all hover:border-teal-400 dark:hover:border-teal-600">
                    <div class="absolute -right-6 -top-6 h-24 w-24 bg-teal-200 dark:bg-teal-700 rounded-full opacity-10 group-hover:opacity-20 transition-opacity"></div>
                    <div class="relative z-10 flex flex-col h-full">
                        <div class="flex items-start justify-between mb-3">
                            <div class="flex items-center gap-2">
                                <div class="rounded-lg bg-teal-100 dark:bg-teal-800 p-2">
                                    <i class="fas fa-users text-teal-600 dark:text-teal-300 text-lg"></i>
                                </div>
                                <h3 class="text-lg font-bold text-teal-800 dark:text-teal-200">Pacientes</h3>
                            </div>
                            <span class="text-xs font-semibold bg-teal-200 dark:bg-teal-700 text-teal-800 dark:text-teal-200 px-2 py-0.5 rounded-full">100%</span>
                        </div>
                        <p class="text-teal-700 dark:text-teal-300 text-sm mb-3">
                            Registro clínico completo: filiación, antecedentes, historial, gráficas OMS.
                        </p>
                        <div class="flex flex-wrap gap-1.5 mt-auto">
                            <span class="text-xs bg-teal-200 dark:bg-teal-800 text-teal-800 dark:text-teal-200 px-2 py-0.5 rounded-full">CRUD</span>
                            <span class="text-xs bg-teal-200 dark:bg-teal-800 text-teal-800 dark:text-teal-200 px-2 py-0.5 rounded-full">Búsqueda live</span>
                            <span class="text-xs bg-teal-200 dark:bg-teal-800 text-teal-800 dark:text-teal-200 px-2 py-0.5 rounded-full">Gráficas OMS</span>
                        </div>
                    </div>
                </a>

                {{-- Consultas --}}
                <a href="{{ route('consultas.index') }}"
                   class="group relative overflow-hidden rounded-xl border border-blue-200 dark:border-blue-800 bg-gradient-to-br from-blue-50 to-blue-100 dark:from-blue-900/50 dark:to-blue-800/50 p-5 shadow-sm hover:shadow-md transition-all hover:border-blue-400 dark:hover:border-blue-600">
                    <div class="absolute -right-6 -top-6 h-24 w-24 bg-blue-200 dark:bg-blue-700 rounded-full opacity-10 group-hover:opacity-20 transition-opacity"></div>
                    <div class="relative z-10 flex flex-col h-full">
                        <div class="flex items-start justify-between mb-3">
                            <div class="flex items-center gap-2">
                                <div class="rounded-lg bg-blue-100 dark:bg-blue-800 p-2">
                                    <i class="fas fa-stethoscope text-blue-600 dark:text-blue-300 text-lg"></i>
                                </div>
                                <h3 class="text-lg font-bold text-blue-800 dark:text-blue-200">Consultas</h3>
                            </div>
                            <span class="text-xs font-semibold bg-blue-200 dark:bg-blue-700 text-blue-800 dark:text-blue-200 px-2 py-0.5 rounded-full">100%</span>
                        </div>
                        <p class="text-blue-700 dark:text-blue-300 text-sm mb-3">
                            Flujo SOAP completo: signos vitales, notas, recetas, laboratorios y vacunas.
                        </p>
                        <div class="flex flex-wrap gap-1.5 mt-auto">
                            <span class="text-xs bg-blue-200 dark:bg-blue-800 text-blue-800 dark:text-blue-200 px-2 py-0.5 rounded-full">SOAP</span>
                            <span class="text-xs bg-blue-200 dark:bg-blue-800 text-blue-800 dark:text-blue-200 px-2 py-0.5 rounded-full">Signos vitales</span>
                            <span class="text-xs bg-blue-200 dark:bg-blue-800 text-blue-800 dark:text-blue-200 px-2 py-0.5 rounded-full">Inmutable</span>
                        </div>
                    </div>
                </a>

                {{-- Plantillas --}}
                <a href="{{ route('templates.index') }}"
                   class="group relative overflow-hidden rounded-xl border border-indigo-200 dark:border-indigo-800 bg-gradient-to-br from-indigo-50 to-indigo-100 dark:from-indigo-900/50 dark:to-indigo-800/50 p-5 shadow-sm hover:shadow-md transition-all hover:border-indigo-400 dark:hover:border-indigo-600">
                    <div class="absolute -right-6 -top-6 h-24 w-24 bg-indigo-200 dark:bg-indigo-700 rounded-full opacity-10 group-hover:opacity-20 transition-opacity"></div>
                    <div class="relative z-10 flex flex-col h-full">
                        <div class="flex items-start justify-between mb-3">
                            <div class="flex items-center gap-2">
                                <div class="rounded-lg bg-indigo-100 dark:bg-indigo-800 p-2">
                                    <i class="fas fa-file-medical-alt text-indigo-600 dark:text-indigo-300 text-lg"></i>
                                </div>
                                <h3 class="text-lg font-bold text-indigo-800 dark:text-indigo-200">Plantillas</h3>
                            </div>
                            <span class="text-xs font-semibold bg-indigo-200 dark:bg-indigo-700 text-indigo-800 dark:text-indigo-200 px-2 py-0.5 rounded-full">100%</span>
                        </div>
                        <p class="text-indigo-700 dark:text-indigo-300 text-sm mb-3">
                            Plantillas reutilizables de recetas y solicitudes de laboratorio.
                        </p>
                        <div class="flex flex-wrap gap-1.5 mt-auto">
                            <span class="text-xs bg-indigo-200 dark:bg-indigo-800 text-indigo-800 dark:text-indigo-200 px-2 py-0.5 rounded-full">Recetas</span>
                            <span class="text-xs bg-indigo-200 dark:bg-indigo-800 text-indigo-800 dark:text-indigo-200 px-2 py-0.5 rounded-full">Laboratorio</span>
                            <span class="text-xs bg-indigo-200 dark:bg-indigo-800 text-indigo-800 dark:text-indigo-200 px-2 py-0.5 rounded-full">Ítems</span>
                        </div>
                    </div>
                </a>

                {{-- Consultas Escaneadas (Técnico / Admin / Doctor) --}}
                @can('create', App\Models\Consultation::class)
                <a href="{{ route('pacientes.create-old') }}"
                   class="group relative overflow-hidden rounded-xl border border-cyan-200 dark:border-cyan-800 bg-gradient-to-br from-cyan-50 to-cyan-100 dark:from-cyan-900/50 dark:to-cyan-800/50 p-5 shadow-sm hover:shadow-md transition-all hover:border-cyan-400 dark:hover:border-cyan-600">
                    <div class="absolute -right-6 -top-6 h-24 w-24 bg-cyan-200 dark:bg-cyan-700 rounded-full opacity-10 group-hover:opacity-20 transition-opacity"></div>
                    <div class="relative z-10 flex flex-col h-full">
                        <div class="flex items-start justify-between mb-3">
                            <div class="flex items-center gap-2">
                                <div class="rounded-lg bg-cyan-100 dark:bg-cyan-800 p-2">
                                    <i class="fas fa-file-upload text-cyan-600 dark:text-cyan-300 text-lg"></i>
                                </div>
                                <h3 class="text-lg font-bold text-cyan-800 dark:text-cyan-200">Digitalización</h3>
                            </div>
                            <span class="text-xs font-semibold bg-cyan-200 dark:bg-cyan-700 text-cyan-800 dark:text-cyan-200 px-2 py-0.5 rounded-full">100%</span>
                        </div>
                        <p class="text-cyan-700 dark:text-cyan-300 text-sm mb-3">
                            Subida de consultas históricas escaneadas (PDF / JPG) asociadas a pacientes.
                        </p>
                        <div class="flex flex-wrap gap-1.5 mt-auto">
                            <span class="text-xs bg-cyan-200 dark:bg-cyan-800 text-cyan-800 dark:text-cyan-200 px-2 py-0.5 rounded-full">PDF</span>
                            <span class="text-xs bg-cyan-200 dark:bg-cyan-800 text-cyan-800 dark:text-cyan-200 px-2 py-0.5 rounded-full">Imágenes</span>
                            <span class="text-xs bg-cyan-200 dark:bg-cyan-800 text-cyan-800 dark:text-cyan-200 px-2 py-0.5 rounded-full">Histórico</span>
                        </div>
                    </div>
                </a>
                @endcan

                {{-- Catálogos (solo Admin) --}}
                @role('Admin')
                <a href="{{ route('catalogs.index') }}"
                   class="group relative overflow-hidden rounded-xl border border-amber-200 dark:border-amber-800 bg-gradient-to-br from-amber-50 to-amber-100 dark:from-amber-900/50 dark:to-amber-800/50 p-5 shadow-sm hover:shadow-md transition-all hover:border-amber-400 dark:hover:border-amber-600">
                    <div class="absolute -right-6 -top-6 h-24 w-24 bg-amber-200 dark:bg-amber-700 rounded-full opacity-10 group-hover:opacity-20 transition-opacity"></div>
                    <div class="relative z-10 flex flex-col h-full">
                        <div class="flex items-start justify-between mb-3">
                            <div class="flex items-center gap-2">
                                <div class="rounded-lg bg-amber-100 dark:bg-amber-800 p-2">
                                    <i class="fas fa-database text-amber-600 dark:text-amber-300 text-lg"></i>
                                </div>
                                <h3 class="text-lg font-bold text-amber-800 dark:text-amber-200">Catálogos</h3>
                            </div>
                            <span class="text-xs font-semibold bg-amber-200 dark:bg-amber-700 text-amber-800 dark:text-amber-200 px-2 py-0.5 rounded-full">Admin</span>
                        </div>
                        <p class="text-amber-700 dark:text-amber-300 text-sm mb-3">
                            Laboratorio, medicamentos, vacunas PAI Bolivia y boletas OMS de crecimiento.
                        </p>
                        <div class="flex flex-wrap gap-1.5 mt-auto">
                            <span class="text-xs bg-amber-200 dark:bg-amber-800 text-amber-800 dark:text-amber-200 px-2 py-0.5 rounded-full">Laboratorio</span>
                            <span class="text-xs bg-amber-200 dark:bg-amber-800 text-amber-800 dark:text-amber-200 px-2 py-0.5 rounded-full">Vacunas PAI</span>
                            <span class="text-xs bg-amber-200 dark:bg-amber-800 text-amber-800 dark:text-amber-200 px-2 py-0.5 rounded-full">OMS</span>
                        </div>
                    </div>
                </a>
                @endrole

                {{-- Perfil Médico (solo Doctor) --}}
                @role('Doctor')
                <a href="{{ route('doctor-profile.edit') }}"
                   class="group relative overflow-hidden rounded-xl border border-purple-200 dark:border-purple-800 bg-gradient-to-br from-purple-50 to-purple-100 dark:from-purple-900/50 dark:to-purple-800/50 p-5 shadow-sm hover:shadow-md transition-all hover:border-purple-400 dark:hover:border-purple-600">
                    <div class="absolute -right-6 -top-6 h-24 w-24 bg-purple-200 dark:bg-purple-700 rounded-full opacity-10 group-hover:opacity-20 transition-opacity"></div>
                    <div class="relative z-10 flex flex-col h-full">
                        <div class="flex items-start justify-between mb-3">
                            <div class="flex items-center gap-2">
                                <div class="rounded-lg bg-purple-100 dark:bg-purple-800 p-2">
                                    <i class="fas fa-user-md text-purple-600 dark:text-purple-300 text-lg"></i>
                                </div>
                                <h3 class="text-lg font-bold text-purple-800 dark:text-purple-200">Perfil Médico</h3>
                            </div>
                            <span class="text-xs font-semibold bg-purple-200 dark:bg-purple-700 text-purple-800 dark:text-purple-200 px-2 py-0.5 rounded-full">100%</span>
                        </div>
                        <p class="text-purple-700 dark:text-purple-300 text-sm mb-3">
                            Especialidad, matrícula profesional y datos del médico tratante.
                        </p>
                        <div class="flex flex-wrap gap-1.5 mt-auto">
                            <span class="text-xs bg-purple-200 dark:bg-purple-800 text-purple-800 dark:text-purple-200 px-2 py-0.5 rounded-full">Especialidad</span>
                            <span class="text-xs bg-purple-200 dark:bg-purple-800 text-purple-800 dark:text-purple-200 px-2 py-0.5 rounded-full">Matrícula</span>
                        </div>
                    </div>
                </a>
                @endrole

            </div>
        </div>

        {{-- Acciones rápidas + Últimas consultas --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            {{-- Acciones rápidas --}}
            <div class="rounded-xl bg-white dark:bg-zinc-800 border border-gray-100 dark:border-zinc-700 p-5 shadow-sm">
                <h3 class="text-base font-bold text-gray-900 dark:text-white mb-4">Acciones Rápidas</h3>
                <div class="flex flex-col gap-2">
                    <a href="{{ route('pacientes.create') }}"
                       class="flex items-center gap-3 p-3 rounded-lg bg-teal-50 dark:bg-teal-900/40 border border-teal-200 dark:border-teal-800 hover:bg-teal-100 dark:hover:bg-teal-800/60 transition">
                        <i class="fas fa-user-plus text-teal-600 dark:text-teal-400 w-5 text-center"></i>
                        <div>
                            <p class="font-semibold text-teal-900 dark:text-teal-200 text-sm">Nuevo Paciente</p>
                            <p class="text-xs text-teal-600 dark:text-teal-400">Registro completo</p>
                        </div>
                    </a>

                    <a href="{{ route('consultas.create') }}"
                       class="flex items-center gap-3 p-3 rounded-lg bg-blue-50 dark:bg-blue-900/40 border border-blue-200 dark:border-blue-800 hover:bg-blue-100 dark:hover:bg-blue-800/60 transition">
                        <i class="fas fa-plus-square text-blue-600 dark:text-blue-400 w-5 text-center"></i>
                        <div>
                            <p class="font-semibold text-blue-900 dark:text-blue-200 text-sm">Nueva Consulta</p>
                            <p class="text-xs text-blue-600 dark:text-blue-400">Atención digital</p>
                        </div>
                    </a>

                    <a href="{{ route('pacientes.create-old') }}"
                       class="flex items-center gap-3 p-3 rounded-lg bg-cyan-50 dark:bg-cyan-900/40 border border-cyan-200 dark:border-cyan-800 hover:bg-cyan-100 dark:hover:bg-cyan-800/60 transition">
                        <i class="fas fa-file-upload text-cyan-600 dark:text-cyan-400 w-5 text-center"></i>
                        <div>
                            <p class="font-semibold text-cyan-900 dark:text-cyan-200 text-sm">Subir Escaneado</p>
                            <p class="text-xs text-cyan-600 dark:text-cyan-400">Consulta histórica</p>
                        </div>
                    </a>

                    <a href="{{ route('templates.prescriptions') }}"
                       class="flex items-center gap-3 p-3 rounded-lg bg-indigo-50 dark:bg-indigo-900/40 border border-indigo-200 dark:border-indigo-800 hover:bg-indigo-100 dark:hover:bg-indigo-800/60 transition">
                        <i class="fas fa-file-medical-alt text-indigo-600 dark:text-indigo-400 w-5 text-center"></i>
                        <div>
                            <p class="font-semibold text-indigo-900 dark:text-indigo-200 text-sm">Plantillas Recetas</p>
                            <p class="text-xs text-indigo-600 dark:text-indigo-400">Gestionar plantillas</p>
                        </div>
                    </a>

                    <a href="{{ route('pacientes.index') }}"
                       class="flex items-center gap-3 p-3 rounded-lg bg-gray-50 dark:bg-zinc-700/50 border border-gray-200 dark:border-zinc-600 hover:bg-gray-100 dark:hover:bg-zinc-700 transition">
                        <i class="fas fa-list text-gray-500 dark:text-gray-400 w-5 text-center"></i>
                        <div>
                            <p class="font-semibold text-gray-800 dark:text-gray-200 text-sm">Ver Todos los Pacientes</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Listado completo</p>
                        </div>
                    </a>
                </div>
            </div>

            {{-- Últimas 5 consultas --}}
            <div class="lg:col-span-2 rounded-xl bg-white dark:bg-zinc-800 border border-gray-100 dark:border-zinc-700 p-5 shadow-sm">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-base font-bold text-gray-900 dark:text-white">Últimas Consultas</h3>
                    <a href="{{ route('consultas.index') }}"
                       class="text-xs text-teal-600 dark:text-teal-400 hover:underline font-medium">Ver todas</a>
                </div>

                @if($ultimasConsultas->isEmpty())
                    <div class="flex flex-col items-center justify-center py-10 text-gray-400 dark:text-gray-500">
                        <i class="fas fa-stethoscope text-4xl mb-3 opacity-40"></i>
                        <p class="text-sm">No hay consultas registradas aún.</p>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="text-xs text-gray-500 dark:text-gray-400 border-b border-gray-100 dark:border-zinc-700">
                                    <th class="text-left pb-2 font-medium">Paciente</th>
                                    <th class="text-left pb-2 font-medium">Tipo</th>
                                    <th class="text-left pb-2 font-medium">Estado</th>
                                    <th class="text-left pb-2 font-medium">Fecha</th>
                                    <th class="text-right pb-2 font-medium">Acción</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50 dark:divide-zinc-700">
                                @foreach($ultimasConsultas as $consulta)
                                <tr class="hover:bg-gray-50 dark:hover:bg-zinc-700/50 transition">
                                    <td class="py-2.5 pr-3">
                                        <span class="font-medium text-gray-800 dark:text-gray-200">
                                            {{ $consulta->patient?->full_name ?? '—' }}
                                        </span>
                                    </td>
                                    <td class="py-2.5 pr-3">
                                        @if($consulta->type?->isManual())
                                            <span class="inline-flex items-center gap-1 text-xs bg-cyan-100 dark:bg-cyan-900/50 text-cyan-700 dark:text-cyan-300 px-2 py-0.5 rounded-full">
                                                <i class="fas fa-file-upload text-xs"></i> Escaneada
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 text-xs bg-blue-100 dark:bg-blue-900/50 text-blue-700 dark:text-blue-300 px-2 py-0.5 rounded-full">
                                                <i class="fas fa-laptop-medical text-xs"></i> Digital
                                            </span>
                                        @endif
                                    </td>
                                    <td class="py-2.5 pr-3">
                                        @if($consulta->status?->isFinalized())
                                            <span class="inline-flex items-center gap-1 text-xs bg-green-100 dark:bg-green-900/50 text-green-700 dark:text-green-300 px-2 py-0.5 rounded-full">
                                                <i class="fas fa-check-circle text-xs"></i> Finalizada
                                            </span>
                                        @elseif($consulta->status?->isSaved())
                                            <span class="inline-flex items-center gap-1 text-xs bg-blue-100 dark:bg-blue-900/50 text-blue-700 dark:text-blue-300 px-2 py-0.5 rounded-full">
                                                <i class="fas fa-save text-xs"></i> Guardada
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 text-xs bg-yellow-100 dark:bg-yellow-900/50 text-yellow-700 dark:text-yellow-300 px-2 py-0.5 rounded-full">
                                                <i class="fas fa-edit text-xs"></i> Borrador
                                            </span>
                                        @endif
                                    </td>
                                    <td class="py-2.5 pr-3 text-gray-500 dark:text-gray-400 text-xs whitespace-nowrap">
                                        {{ $consulta->created_at->isoFormat('D MMM YYYY') }}
                                    </td>
                                    <td class="py-2.5 text-right">
                                        <a href="{{ route('consultas.show', $consulta) }}"
                                           class="text-xs text-teal-600 dark:text-teal-400 hover:text-teal-800 dark:hover:text-teal-200 font-medium hover:underline">
                                            Ver
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>

        {{-- Banner de estado del sistema --}}
        <div class="rounded-xl bg-gradient-to-r from-teal-600 to-teal-700 dark:from-teal-800 dark:to-teal-900 p-5 shadow-sm">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div class="flex items-start gap-3">
                    <i class="fas fa-check-circle text-teal-200 text-2xl mt-0.5"></i>
                    <div>
                        <h4 class="font-bold text-white">Sistema operativo — MVP al 90%</h4>
                        <p class="text-teal-200 text-sm mt-0.5">
                            Módulos activos: Pacientes · Consultas SOAP · Plantillas · Catálogos · Gráficas OMS · Digitalización de históricos
                        </p>
                    </div>
                </div>
                <div class="flex flex-wrap gap-2 shrink-0">
                    <div class="text-center bg-teal-500/40 rounded-lg px-3 py-1.5">
                        <p class="text-white font-bold text-lg leading-none">378+</p>
                        <p class="text-teal-200 text-xs">Tests PHP</p>
                    </div>
                    <div class="text-center bg-teal-500/40 rounded-lg px-3 py-1.5">
                        <p class="text-white font-bold text-lg leading-none">14</p>
                        <p class="text-teal-200 text-xs">Tests Dusk</p>
                    </div>
                    <div class="text-center bg-teal-500/40 rounded-lg px-3 py-1.5">
                        <p class="text-white font-bold text-lg leading-none">10</p>
                        <p class="text-teal-200 text-xs">Boletas OMS</p>
                    </div>
                </div>
            </div>
        </div>

    </div>
</x-layouts::app>
