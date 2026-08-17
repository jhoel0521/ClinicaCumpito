# 📖 Biblia del Proyecto: VitalTrack Pediátrico

## 1. Visión General del Proyecto

VitalTrack Pediátrico es un sistema web de gestión clínica diseñado específicamente para resolver los cuellos de botella de un consultorio pediátrico real. No es un simple CRUD de pacientes; es una herramienta pensada para ahorrar tiempo en la consulta, mantener la inmutabilidad de la historia clínica y proporcionar herramientas de diagnóstico visual precisas (OMS) que también sirvan para educar a los padres.

## 2. Stack Tecnológico (Laravel 12)

- Backend: Laravel 12 (PHP 8.4+)
- Base de Datos: PostgreSQL 17
- Autenticación: Laravel Fortify (incluye 2FA). APIs/Sanctum quedan disponibles como base si luego se separa el Front en React/Vue o App Móvil.
- Autorización: spatie/laravel-permission — implementado con 5 roles: Admin, Doctor, Enfermera, Secretaria, Técnico.
- Frontend: Blade + Livewire 4 (componentes Volt de archivo único) + Flux UI + Tailwind CSS + Chart.js (para las gráficas de la OMS).
- Manejo de Archivos: Laravel Storage (disco local) para escaneos históricos, adjuntos de laboratorio y logo de la clínica; los documentos de recetas/laboratorio se generan como PDF con `barryvdh/laravel-dompdf`.

> Nota (actualizada 10-ago-2026): esta sección describía el stack propuesto al inicio del proyecto. El stack efectivamente implementado es el listado arriba; para el detalle verificado ver `AGENTS.md` y `ARCHITECTURE.md`.

## 3. Objetivos Clave (Lo que la Doctora Espera)

### 🎯 Objetivo 1: "Ganar Tiempo" (Automatización)

La doctora odia escribir lo mismo una y otra vez.

Solución: Implementación de Plantillas (Combos) para Recetas y Laboratorios. Con un solo clic debe poder cargar un "Perfil Pre-Quirúrgico" o un tratamiento para "Diarrea Aguda", pudiendo editar solo los detalles finales antes de imprimir.

### 🎯 Objetivo 2: Gráficas de la OMS "Como en la Vida Real"

La doctora no quiere un sistema genérico, quiere sus "boletas" clásicas.

Solución: Un motor de gráficas impulsado por Chart.js que replique las 50 boletas de la OMS (0 a 13 semanas, 0 a 6 meses, 0 a 5 años, 0 a 13 años (opcional en algunas tablas)).

Regla de Oro (histórica, ya no vigente): en el diseño original no se iba a graficar el peso por
petición expresa de la doctora, solo Talla/Longitud y Perímetro Cefálico. El requerimiento
cambió durante el desarrollo: el motor implementado (`GrowthChartService`) sí incluye peso —
las 4 boletas sembradas son `peso_edad`, `talla_edad`, `peso_talla` y `perimetro_cefalico` (cada
una por sexo). Se deja la nota como registro histórico de la decisión original.

Doble Vista: Un "Modo Médico" (Líneas Z-Score: -3 a +3) para diagnóstico clínico, y un "Modo Padres" (Percentiles: P3 a P97) para explicar el crecimiento a la familia.

Puntos Irregulares: La gráfica debe soportar visitas en días/semanas irregulares (dispersión X, Y), no forzar al paciente a coincidir con los meses exactos de la OMS.

### 🎯 Objetivo 3: Inmutabilidad Histórica (Protección Legal)

Si la doctora edita una plantilla de recetas hoy, no debe alterarse la receta que le dio a un paciente hace 3 años.

Solución: Las transacciones (Solicitudes de Lab y Recetas de Consultas) actúan como Snapshots. Se copia el texto exacto (nombre del medicamento, nombre del examen) y se guarda. No se usan llaves foráneas estrictas hacia los catálogos en los detalles finales.

### 🎯 Objetivo 4: Transición Suave (El Sistema Híbrido)

La doctora tiene un archivo físico lleno de papeles y no quiere perder esa historia.

Solución: El sistema permite consultas de tipo manual. Esto significa crear el perfil digital del paciente, pero en lugar de llenar texto, simplemente se adjunta el PDF escaneado del historial viejo y se registran los signos vitales históricos para que la gráfica de la OMS no empiece vacía.

## 4. Fases de Desarrollo Recomendadas (Roadmap Laravel)

### FASE 1: Cimientos y Autenticación (Semana 1)

- Instalar Laravel 12.
- Configurar la Base de Datos según el DBML aprobado.
- Instalar Laravel Breeze o Fortify para el login.
- Instalar spatie/laravel-permission.
- Crear los Modelos (Doctor, Paciente, Usuario) con sus migraciones y relaciones hasOne / hasMany.

### FASE 2: Gestión de Pacientes y Consultas Básicas (Semana 2)

- CRUD de Pacientes (Cálculo de edad exacta con Carbon).
- Módulo de Consultas (Creación de consulta digital vs. carga de archivo PDF para consulta manual).
- Formularios de Signos Vitales y Notas SOAP.

### FASE 3: El Motor de Ahorro de Tiempo (Plantillas) (Semana 3)

- CRUD de Catálogos Maestros (Exámenes de laboratorio).
- CRUD de Plantillas (Para que la doctora cree sus "Combos" de laboratorios y recetas).
- Lógica del Snapshot: Al crear una consulta, un botón debe copiar los items de la plantilla a la tabla de la consulta de forma estática (inmutable).

### FASE 4: Módulo de Vacunas PAI (Semana 4)

- Seeder con el Esquema Nacional de Vacunación (PAI Bolivia).
- Interfaz visual para marcar vacunas aplicadas y ver cuáles faltan según la edad actual del niño.

### FASE 5: El Motor de la OMS (Semana 5 - El Reto Principal)

- Crear el Seeder que importe los archivos CSV reales de la OMS a la tabla oms_datos_graficas.
- Crear un Controlador / API Resource en Laravel que devuelva los datos agrupados según la "boleta" solicitada por el Frontend.
- Construir la vista con Chart.js implementando el gráfico de tipo scatter o line con eje X lineal para soportar los puntos irregulares de las visitas del paciente.
- Programar los "Radio Buttons" (0-13 semanas, 0-2 años, etc.) y el Toggle (Médico/Padres).

### FASE 6: Pruebas y Entrega

- Carga de los escaneos de prueba que el Contratista se comprometió a digitalizar (Bono).
- Revisión conjunta con la Doctora usando el entorno local/staging.
- Despliegue a producción.

Nota para el desarrollador: Siempre mantén los modelos delgados y traslada la lógica compleja (como los cálculos de edad para las vacunas o el armado de la estructura JSON para la OMS) a clases de Servicio (Services/Actions).