<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <title>{{ config('app.name', 'VitalTrack Pediátrico') }}</title>

        <!-- Estilos Tailwind CSS -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <!-- Font Awesome para Iconos -->
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet" />

        <script>
            // Inicializar dark mode desde localStorage o preferencia del sistema
            (function () {
                const theme =
                    localStorage.getItem('theme') ||
                    (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
                if (theme === 'dark') {
                    document.documentElement.classList.add('dark');
                } else {
                    document.documentElement.classList.remove('dark');
                }
                localStorage.setItem('theme', theme);
            })();
        </script>

        <style>
            @keyframes fadeInUp {
                from {
                    opacity: 0;
                    transform: translateY(20px);
                }

                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }

            .animate-fade-in-up {
                animation: fadeInUp 0.6s ease-out;
            }

            .gradient-teal {
                background: linear-gradient(135deg, #14b8a6 0%, #0d9488 100%);
            }
        </style>
    </head>

    <body class="bg-white dark:bg-zinc-950 min-h-screen font-sans text-zinc-900 dark:text-zinc-50 transition-colors">
        <!-- NAVEGACIÓN SUPERIOR -->
        <nav
            class="sticky top-0 z-50 bg-white dark:bg-zinc-900 shadow-sm border-b border-zinc-200 dark:border-zinc-800 transition-colors"
        >
            <div class="max-w-6xl mx-auto px-6 py-4 flex justify-between items-center">
                <div class="flex items-center gap-3">
                    <div
                        class="h-10 w-10 bg-teal-600 rounded-lg flex items-center justify-center text-white font-bold text-lg shadow-md"
                    >
                        <i class="fas fa-heartbeat"></i>
                    </div>
                    <div>
                        <h1 class="text-lg font-bold text-zinc-900 dark:text-white">
                            {{ config('app.name', 'VitalTrack') }}
                        </h1>
                        <p class="text-xs text-zinc-500 dark:text-zinc-400">Gestión Clínica Pediátrica</p>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <!-- Toggle Dark Mode -->
                    <button
                        id="theme-toggle"
                        class="p-2 rounded-lg bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-300 hover:bg-zinc-200 dark:hover:bg-zinc-700 transition"
                        aria-label="Cambiar tema"
                    >
                        <i class="fas fa-sun dark:hidden"></i>
                        <i class="fas fa-moon hidden dark:inline"></i>
                    </button>

                    @auth
                        <a
                            href="{{ route('dashboard') }}"
                            class="bg-teal-600 text-white px-4 py-2 rounded-lg hover:bg-teal-700 transition font-medium text-sm shadow-md flex items-center gap-2"
                        >
                            <i class="fas fa-chart-bar"></i>
                            {{ __('Dashboard') }}
                        </a>
                    @else
                        <a
                            href="{{ route('login') }}"
                            class="text-zinc-700 dark:text-zinc-300 px-4 py-2 rounded-lg hover:bg-zinc-100 dark:hover:bg-zinc-800 transition font-medium text-sm"
                        >
                            {{ __('Iniciar Sesión') }}
                        </a>
                    @endauth
                </div>
            </div>
        </nav>

        <!-- HERO SECTION -->
        <section class="relative overflow-hidden">
            <div class="absolute inset-0 gradient-teal opacity-5"></div>
            <div class="max-w-6xl mx-auto px-6 py-16 sm:py-20 relative">
                <div class="text-center animate-fade-in-up">
                    <div class="inline-block mb-4">
                        <span
                            class="bg-teal-50 dark:bg-teal-950 text-teal-700 dark:text-teal-300 px-4 py-2 rounded-full text-sm font-medium border border-teal-200 dark:border-teal-700"
                        >
                            <i class="fas fa-star mr-2"></i>
                            Plataforma de Salud Infantil
                        </span>
                    </div>
                    <h2 class="text-4xl sm:text-5xl font-bold text-zinc-900 dark:text-white mb-4 leading-tight">
                        Gestión Clínica Que
                        <span
                            class="text-transparent bg-clip-text bg-gradient-to-r from-teal-600 to-teal-500 dark:from-teal-400 dark:to-teal-300"
                        >
                            Ahorra Tiempo
                        </span>
                    </h2>
                    <p class="text-lg text-zinc-600 dark:text-zinc-400 max-w-2xl mx-auto mb-8">
                        Sistema integral diseñado para pediatría real. Curvas OMS, historia clínica inmutable y
                        plantillas inteligentes para máxima eficiencia.
                    </p>

                    @auth
                        <a
                            href="{{ route('dashboard') }}"
                            class="inline-flex items-center gap-2 bg-teal-600 text-white px-8 py-3 rounded-lg hover:bg-teal-700 transition font-medium shadow-lg hover:shadow-xl"
                        >
                            <i class="fas fa-arrow-right"></i>
                            Ir al Dashboard
                        </a>
                    @else
                        <a
                            href="{{ route('login') }}"
                            class="inline-flex items-center gap-2 bg-teal-600 text-white px-8 py-3 rounded-lg hover:bg-teal-700 transition font-medium shadow-lg hover:shadow-xl"
                        >
                            <i class="fas fa-sign-in-alt mr-2"></i>
                            Comenzar
                        </a>
                    @endauth
                </div>
            </div>
        </section>

        <!-- CARACTERÍSTICAS CLAVE -->
        <section class="max-w-6xl mx-auto px-6 py-16">
            <h3 class="text-2xl font-bold text-zinc-900 dark:text-white text-center mb-12">
                <i class="fas fa-check-circle text-teal-600 mr-2"></i>
                ¿Qué Logra VitalTrack?
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Característica 1 -->
                <div
                    class="bg-white dark:bg-zinc-900 rounded-xl shadow-sm p-6 border border-zinc-200 dark:border-zinc-700 hover:shadow-md transition group"
                >
                    <div
                        class="h-12 w-12 bg-teal-100 dark:bg-teal-900 rounded-lg flex items-center justify-center text-teal-600 dark:text-teal-400 text-xl mb-4 group-hover:bg-teal-600 group-hover:text-white transition"
                    >
                        <i class="fas fa-bolt"></i>
                    </div>
                    <h4 class="text-lg font-bold text-zinc-900 dark:text-white mb-2">Ganar Tiempo Real</h4>
                    <p class="text-zinc-600 dark:text-zinc-400 text-sm">
                        Plantillas (Combos) para recetas y laboratorios. Carga en un clic y personaliza los detalles
                        finales antes de imprimir.
                    </p>
                </div>

                <!-- Característica 2 -->
                <div
                    class="bg-white dark:bg-zinc-900 rounded-xl shadow-sm p-6 border border-zinc-200 dark:border-zinc-700 hover:shadow-md transition group"
                >
                    <div
                        class="h-12 w-12 bg-sky-100 dark:bg-sky-900 rounded-lg flex items-center justify-center text-sky-600 dark:text-sky-400 text-xl mb-4 group-hover:bg-sky-600 group-hover:text-white transition"
                    >
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <h4 class="text-lg font-bold text-zinc-900 dark:text-white mb-2">Curvas OMS Inteligentes</h4>
                    <p class="text-zinc-600 dark:text-zinc-400 text-sm">
                        Gráficas de talla y perímetro cefálico con vista médica (Z-Score) y vista padres (Percentiles).
                        Puntos irregulares soportados.
                    </p>
                </div>

                <!-- Característica 3 -->
                <div
                    class="bg-white dark:bg-zinc-900 rounded-xl shadow-sm p-6 border border-zinc-200 dark:border-zinc-700 hover:shadow-md transition group"
                >
                    <div
                        class="h-12 w-12 bg-green-100 dark:bg-green-900 rounded-lg flex items-center justify-center text-green-600 dark:text-green-400 text-xl mb-4 group-hover:bg-green-600 group-hover:text-white transition"
                    >
                        <i class="fas fa-lock"></i>
                    </div>
                    <h4 class="text-lg font-bold text-zinc-900 dark:text-white mb-2">Historia Clínica Inmutable</h4>
                    <p class="text-zinc-600 dark:text-zinc-400 text-sm">
                        Transacciones como Snapshots. Editar una plantilla hoy no afecta recetas de hace 3 años.
                        Protección legal garantizada.
                    </p>
                </div>

                <!-- Característica 4 -->
                <div
                    class="bg-white dark:bg-zinc-900 rounded-xl shadow-sm p-6 border border-zinc-200 dark:border-zinc-700 hover:shadow-md transition group"
                >
                    <div
                        class="h-12 w-12 bg-amber-100 dark:bg-amber-900 rounded-lg flex items-center justify-center text-amber-600 dark:text-amber-400 text-xl mb-4 group-hover:bg-amber-600 group-hover:text-white transition"
                    >
                        <i class="fas fa-file-pdf"></i>
                    </div>
                    <h4 class="text-lg font-bold text-zinc-900 dark:text-white mb-2">Modo Híbrido</h4>
                    <p class="text-zinc-600 dark:text-zinc-400 text-sm">
                        Transición suave desde archivos físicos. Adjunta PDFs históricos y captura signos vitales
                        antiguos para llenar las curvas.
                    </p>
                </div>
            </div>
        </section>

        <!-- FLUJOS PRINCIPALES DE LA APP -->
        <section class="max-w-6xl mx-auto px-6 py-16 border-t border-zinc-200 dark:border-zinc-700 pt-16">
            <h3 class="text-2xl font-bold text-zinc-900 dark:text-white text-center mb-4">
                <i class="fas fa-compass text-teal-600 mr-2"></i>
                Flujos Principales
            </h3>
            <p class="text-center text-zinc-600 dark:text-zinc-400 mb-12">
                Acceso rápido a las funciones clave del sistema
            </p>

            @auth
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Dashboard Paciente -->
                    <a
                        href="#"
                        class="group relative block bg-white dark:bg-zinc-900 rounded-xl shadow-sm p-6 border border-zinc-200 dark:border-zinc-700 hover:border-teal-500 dark:hover:border-teal-400 hover:shadow-lg transition overflow-hidden"
                    >
                        <div
                            class="absolute inset-0 bg-gradient-to-r from-teal-500/5 to-transparent opacity-0 group-hover:opacity-100 transition"
                        ></div>
                        <div class="relative flex items-start gap-4">
                            <div
                                class="h-14 w-14 bg-teal-100 dark:bg-teal-900 rounded-lg flex items-center justify-center text-teal-600 dark:text-teal-400 text-2xl group-hover:bg-teal-600 group-hover:text-white transition flex-shrink-0"
                            >
                                <i class="fas fa-columns"></i>
                            </div>
                            <div class="flex-1">
                                <h4
                                    class="text-lg font-bold text-zinc-900 dark:text-white group-hover:text-teal-700 dark:group-hover:text-teal-300 transition"
                                >
                                    Dashboard Paciente
                                </h4>
                                <p class="text-sm text-zinc-600 dark:text-zinc-400 mt-1">
                                    Vista integral de paciente: curvas OMS, laboratorios, historial y accesos rápidos.
                                </p>
                                <div
                                    class="mt-3 text-teal-600 dark:text-teal-400 font-medium text-sm flex items-center gap-2 group-hover:gap-3 transition"
                                >
                                    Explorar
                                    <i class="fas fa-arrow-right ml-auto"></i>
                                </div>
                            </div>
                        </div>
                    </a>

                    <!-- Nueva Consulta -->
                    <a
                        href="#"
                        class="group relative block bg-white dark:bg-zinc-900 rounded-xl shadow-sm p-6 border border-zinc-200 dark:border-zinc-700 hover:border-sky-500 dark:hover:border-sky-400 hover:shadow-lg transition overflow-hidden"
                    >
                        <div
                            class="absolute inset-0 bg-gradient-to-r from-sky-500/5 to-transparent opacity-0 group-hover:opacity-100 transition"
                        ></div>
                        <div class="relative flex items-start gap-4">
                            <div
                                class="h-14 w-14 bg-sky-100 dark:bg-sky-900 rounded-lg flex items-center justify-center text-sky-600 dark:text-sky-400 text-2xl group-hover:bg-sky-600 group-hover:text-white transition flex-shrink-0"
                            >
                                <i class="fas fa-plus"></i>
                            </div>
                            <div class="flex-1">
                                <h4
                                    class="text-lg font-bold text-zinc-900 dark:text-white group-hover:text-sky-700 dark:group-hover:text-sky-300 transition"
                                >
                                    Nueva Consulta
                                </h4>
                                <p class="text-sm text-zinc-600 dark:text-zinc-400 mt-1">
                                    Selector híbrido: nueva consulta digital o carga de archivo manual. Flexibilidad
                                    real.
                                </p>
                                <div
                                    class="mt-3 text-sky-600 dark:text-sky-400 font-medium text-sm flex items-center gap-2 group-hover:gap-3 transition"
                                >
                                    Crear
                                    <i class="fas fa-arrow-right ml-auto"></i>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>

                <!-- Detalles y Formularios (Mini Grid) -->
                <div
                    class="mt-8 bg-zinc-50 dark:bg-zinc-900 rounded-xl p-6 border border-zinc-200 dark:border-zinc-700"
                >
                    <h4 class="text-sm font-bold text-zinc-700 dark:text-zinc-300 uppercase tracking-wider mb-4">
                        Formularios y Detalles
                    </h4>
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                        <a
                            href="#"
                            class="flex items-center gap-3 p-3 rounded-lg hover:bg-white dark:hover:bg-zinc-800 hover:shadow-sm transition text-zinc-700 dark:text-zinc-300 font-medium text-sm"
                        >
                            <i class="fas fa-user-plus w-5 text-teal-600"></i>
                            <span>Nuevo Paciente</span>
                        </a>
                        <a
                            href="#"
                            class="flex items-center gap-3 p-3 rounded-lg hover:bg-white dark:hover:bg-zinc-800 hover:shadow-sm transition text-zinc-700 dark:text-zinc-300 font-medium text-sm"
                        >
                            <i class="fas fa-file-medical w-5 text-sky-600"></i>
                            <span>Detalle Híbrido</span>
                        </a>
                        <a
                            href="#"
                            class="flex items-center gap-3 p-3 rounded-lg hover:bg-white dark:hover:bg-zinc-800 hover:shadow-sm transition text-zinc-700 dark:text-zinc-300 font-medium text-sm"
                        >
                            <i class="fas fa-laptop-medical w-5 text-green-600"></i>
                            <span>Detalle Digital</span>
                        </a>
                    </div>
                </div>
            @else
                <!-- Mensaje para no autenticados -->
                <div
                    class="bg-white dark:bg-zinc-900 rounded-xl shadow-sm p-12 border border-zinc-200 dark:border-zinc-700 text-center"
                >
                    <i class="fas fa-lock text-teal-600 text-5xl mb-4 block"></i>
                    <h4 class="text-xl font-bold text-zinc-900 dark:text-white mb-2">Acceso Restringido</h4>
                    <p class="text-zinc-600 dark:text-zinc-400 mb-6">
                        Inicia sesión para acceder a todas las funciones clínicas del sistema.
                    </p>
                    <a
                        href="{{ route('login') }}"
                        class="inline-flex items-center gap-2 bg-teal-600 text-white px-8 py-3 rounded-lg hover:bg-teal-700 transition font-medium shadow-md"
                    >
                        <i class="fas fa-sign-in-alt"></i>
                        Iniciar Sesión
                    </a>
                </div>
            @endauth
        </section>

        <!-- HITOS DEL ROADMAP -->
        <section class="max-w-6xl mx-auto px-6 py-16 border-t border-zinc-200 dark:border-zinc-700 pt-16">
            <h3 class="text-2xl font-bold text-zinc-900 dark:text-white text-center mb-12">
                <i class="fas fa-road text-teal-600 mr-2"></i>
                Roadmap de Desarrollo
            </h3>

            <div class="space-y-4">
                <!-- Fase 1 -->
                <div
                    class="bg-white dark:bg-zinc-900 rounded-lg p-4 border-l-4 border-green-600 shadow-sm hover:shadow-md transition"
                >
                    <div class="flex items-start gap-4">
                        <div
                            class="h-8 w-8 bg-green-600 text-white rounded-full flex items-center justify-center text-sm font-bold flex-shrink-0 mt-0.5"
                        >
                            1
                        </div>
                        <div class="flex-1">
                            <h4 class="font-bold text-zinc-900 dark:text-white">Cimientos y Autenticación</h4>
                            <p class="text-sm text-zinc-600 dark:text-zinc-400 mt-1">
                                Modelos base (Doctor, Paciente, Usuario) con migraciones, relaciones y Laravel Breeze.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Fase 2 -->
                <div
                    class="bg-white dark:bg-zinc-900 rounded-lg p-4 border-l-4 border-sky-600 shadow-sm hover:shadow-md transition"
                >
                    <div class="flex items-start gap-4">
                        <div
                            class="h-8 w-8 bg-sky-600 text-white rounded-full flex items-center justify-center text-sm font-bold flex-shrink-0 mt-0.5"
                        >
                            2
                        </div>
                        <div class="flex-1">
                            <h4 class="font-bold text-zinc-900 dark:text-white">Gestión de Pacientes y Consultas</h4>
                            <p class="text-sm text-zinc-600 dark:text-zinc-400 mt-1">
                                CRUD de pacientes, consultas digitales vs. manuales, signos vitales y notas SOAP.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Fase 3 -->
                <div
                    class="bg-white dark:bg-zinc-900 rounded-lg p-4 border-l-4 border-amber-600 shadow-sm hover:shadow-md transition"
                >
                    <div class="flex items-start gap-4">
                        <div
                            class="h-8 w-8 bg-amber-600 text-white rounded-full flex items-center justify-center text-sm font-bold flex-shrink-0 mt-0.5"
                        >
                            3
                        </div>
                        <div class="flex-1">
                            <h4 class="font-bold text-zinc-900 dark:text-white">
                                Motor de Ahorro de Tiempo (Plantillas)
                            </h4>
                            <p class="text-sm text-zinc-600 dark:text-zinc-400 mt-1">
                                Catálogos maestros, plantillas de "Combos" inmutables con transacciones como Snapshots.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Fase 4 -->
                <div
                    class="bg-white dark:bg-zinc-900 rounded-lg p-4 border-l-4 border-purple-600 shadow-sm hover:shadow-md transition"
                >
                    <div class="flex items-start gap-4">
                        <div
                            class="h-8 w-8 bg-purple-600 text-white rounded-full flex items-center justify-center text-sm font-bold flex-shrink-0 mt-0.5"
                        >
                            4
                        </div>
                        <div class="flex-1">
                            <h4 class="font-bold text-zinc-900 dark:text-white">Módulo de Vacunas PAI</h4>
                            <p class="text-sm text-zinc-600 dark:text-zinc-400 mt-1">
                                Esquema Nacional de Vacunación de Bolivia con interfaz visual de seguimiento.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Fase 5 -->
                <div
                    class="bg-white dark:bg-zinc-900 rounded-lg p-4 border-l-4 border-red-600 shadow-sm hover:shadow-md transition"
                >
                    <div class="flex items-start gap-4">
                        <div
                            class="h-8 w-8 bg-red-600 text-white rounded-full flex items-center justify-center text-sm font-bold flex-shrink-0 mt-0.5"
                        >
                            5
                        </div>
                        <div class="flex-1">
                            <h4 class="font-bold text-zinc-900 dark:text-white">Motor de Gráficas OMS</h4>
                            <p class="text-sm text-zinc-600 dark:text-zinc-400 mt-1">
                                50 boletas OMS con Chart.js. Vista Médica (Z-Score -3 a +3) y Vista Padres (P3 a P97).
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- FOOTER -->
        <footer class="bg-zinc-900 dark:bg-zinc-950 text-zinc-300 dark:text-zinc-400 py-12 mt-16">
            <div class="max-w-6xl mx-auto px-6">
                <div
                    class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-8 pb-8 border-b border-zinc-700 dark:border-zinc-800"
                >
                    <div>
                        <div class="flex items-center gap-2 mb-4">
                            <div
                                class="h-8 w-8 bg-teal-600 rounded-lg flex items-center justify-center text-white text-sm font-bold"
                            >
                                <i class="fas fa-heartbeat"></i>
                            </div>
                            <span class="font-bold">{{ config('app.name', 'VitalTrack') }}</span>
                        </div>
                        <p class="text-sm text-zinc-400 dark:text-zinc-500">
                            Gestión clínica pediátrica diseñada para el mundo real. Curvas OMS, historia inmutable,
                            máxima eficiencia.
                        </p>
                    </div>
                    <div>
                        <h4 class="font-bold text-white mb-4">Enlaces Rápidos</h4>
                        <ul class="space-y-2 text-sm">
                            <li>
                                <a
                                    href="#"
                                    class="text-zinc-400 dark:text-zinc-500 hover:text-teal-400 dark:hover:text-teal-300 transition"
                                >
                                    Dashboard
                                </a>
                            </li>
                            <li>
                                <a
                                    href="#"
                                    class="text-zinc-400 dark:text-zinc-500 hover:text-teal-400 dark:hover:text-teal-300 transition"
                                >
                                    Documentación
                                </a>
                            </li>
                            <li>
                                <a
                                    href="#"
                                    class="text-zinc-400 dark:text-zinc-500 hover:text-teal-400 dark:hover:text-teal-300 transition"
                                >
                                    Soporte
                                </a>
                            </li>
                        </ul>
                    </div>
                    <div>
                        <h4 class="font-bold text-white mb-4">Tecnología</h4>
                        <ul class="space-y-2 text-sm">
                            <li class="text-zinc-400 dark:text-zinc-500">
                                <i class="fas fa-check-circle text-teal-500"></i>
                                Laravel 12
                            </li>
                            <li class="text-zinc-400 dark:text-zinc-500">
                                <i class="fas fa-check-circle text-teal-500"></i>
                                PostgreSQL
                            </li>
                            <li class="text-zinc-400 dark:text-zinc-500">
                                <i class="fas fa-check-circle text-teal-500"></i>
                                Tailwind CSS
                            </li>
                        </ul>
                    </div>
                </div>
                <div class="text-center text-sm text-zinc-500 dark:text-zinc-600">
                    <p>
                        &copy; {{ date('Y') }} {{ config('app.name', 'VitalTrack Pediátrico') }}. Todos los derechos
                        reservados.
                    </p>
                </div>
            </div>
        </footer>
    </body>
</html>

<script>
    // Toggle dark mode
    const themeToggle = document.getElementById('theme-toggle');
    if (themeToggle) {
        themeToggle.addEventListener('click', function () {
            const html = document.documentElement;
            const isDark = html.classList.contains('dark');

            if (isDark) {
                html.classList.remove('dark');
                localStorage.setItem('theme', 'light');
            } else {
                html.classList.add('dark');
                localStorage.setItem('theme', 'dark');
            }
        });
    }
</script>
