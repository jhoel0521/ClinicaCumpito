```
// ==========================================
// CONFIGURACIÓN LARAVEL (AUTENTICACIÓN)
// ==========================================
// NOTA: Los campos en código (migraciones) están en INGLÉS

Table usuarios {
  id bigint [pk, increment]
  nombre varchar
  correo varchar [unique]
  contraseña varchar
  token_recuerdo varchar
  numero_telefono varchar(20) [nullable, note: 'Nuevo: agregar para contacto']
  email_verificado_en timestamp [nullable, note: 'Laravel Breeze']
  created_at timestamp
  updated_at timestamp
  eliminado_en timestamp [nullable, note: 'Soft delete - eliminación lógica']
}

// ==========================================
// MÓDULO MÉDICO
// ==========================================

Table doctores {
  id bigint [pk, increment]
  usuario_id bigint [unique, nullable, note: 'FK → usuarios (1:1 opcional)']
  nombre_completo varchar
  especialidad varchar [nullable, note: 'Ej: Pediatra']
  numero_colegiado varchar [unique, note: 'En lugar de numero_licencia']
  activo boolean [default: true]
  created_at timestamp
  updated_at timestamp
  eliminado_en timestamp [nullable, note: 'Soft delete']
}

// ==========================================
// MÓDULO PACIENTES Y ANTECEDENTES
// ==========================================

Table pacientes {
  id bigint [pk, increment]
  doctor_responsable_id bigint [nullable, note: 'FK → doctores (médico principal)']
  user_id bigint [nullable, note: 'FK → usuarios (si el paciente es usuario del sistema)']
  nombre_completo varchar
  fecha_nacimiento date [note: 'Para calcular edad exacta']
  genero enum('M', 'F')
  
  // Datos al nacer (Línea base para curvas)
  peso_nacimiento decimal(5,2) [nullable, note: 'en kg']
  talla_nacimiento decimal(5,2) [nullable, note: 'en cm']
  perimetro_cefalico_nacimiento decimal(5,2) [nullable, note: 'en cm']
  tipo_parto enum('Parto Normal', 'Cesárea') [nullable]
  lugar_nacimiento varchar [nullable]
  
  // Checklist inicial
  grupo_sanguineo varchar(5) [nullable]
  estado_chagas enum('Positivo', 'Negativo', 'No realizado') [nullable]
  estado_sifilis enum('Positivo', 'Negativo', 'No realizado') [nullable]
  
  // Antecedentes (Textos largos)
  alergias text [nullable]
  patologias text [nullable]
  cirugias text [nullable]
  
  created_at timestamp
  updated_at timestamp
  eliminado_en timestamp [nullable, note: 'Soft delete']
}

// ==========================================
// MÓDULO HISTORIA CLÍNICA (HÍBRIDA) Y SOAP
// ==========================================

Table consultas {
  id bigint [pk, increment]
  paciente_id bigint
  doctor_id bigint [note: 'Doctor que atendió']
  
  tipo enum('digital', 'manual') [note: 'Digital = SOAP, Manual = escaneo']
  estado enum('borrador', 'guardada', 'finalizada') [default: 'guardada', note: 'Nuevo: estado de la consulta']
  fecha_consulta datetime
  
  // Para el sistema Híbrido (Archivos físicos)
  ruta_archivo_escaneado varchar [nullable, note: 'Ruta al PDF/JPG si el tipo es manual']
  pendiente_transcripcion boolean [default: false, note: 'Avisa si falta llenar peso/talla']
  
  created_at timestamp
  updated_at timestamp
  eliminado_en timestamp [nullable, note: 'Soft delete']
}

Table signos_vitales {
  id bigint [pk, increment]
  consulta_id bigint [unique]
  
  peso decimal(5,2) [nullable, note: 'kg - Alimenta la gráfica']
  talla decimal(5,2) [nullable, note: 'cm - Alimenta la gráfica']
  perimetro_cefalico decimal(5,2) [nullable, note: 'cm - Alimenta la gráfica']
  temperatura decimal(4,2) [nullable, note: '°C']
  created_at timestamp
  updated_at timestamp
}

Table notas_soap {
  id bigint [pk, increment]
  consulta_id bigint [unique]
  
  subjetivo text [nullable, note: 'Anamnesis / Motivo consulta']
  objetivo text [nullable, note: 'Examen físico']
  analisis text [nullable, note: 'Diagnóstico CIE-10 / Análisis']
  plan text [nullable, note: 'Indicaciones generales (texto libre)']
  created_at timestamp
  updated_at timestamp
}

// ==========================================
// CATÁLOGOS MÉDICOS (Exámenes e Imagenología)
// ==========================================

Table categorias_laboratorio {
  id bigint [pk, increment]
  nombre varchar [note: 'Ej: Hematología, Orina, Rayos X, Ecografía']
  created_at timestamp
  updated_at timestamp
}

Table catalogo_examenes_laboratorio {
  id bigint [pk, increment]
  categoria_laboratorio_id bigint
  nombre varchar [note: 'Ej: Glucosa, Radiografía de Tórax, Ecografía Abdominal']
  created_at timestamp
  updated_at timestamp
}

// ==========================================
// MÓDULO PLANTILLAS (Para ganar tiempo)
// ==========================================

Table plantillas_receta {
  id bigint [pk, increment]
  doctor_id bigint [note: 'FK → doctores (Dueño de la plantilla)']
  nombre_plantilla varchar [note: 'Ej: Diarrea, Multi-vitaminas niña']
  observaciones_generales text [nullable]
  created_at timestamp
  updated_at timestamp
  eliminado_en timestamp [nullable, note: 'Soft delete']
}

Table items_plantilla_receta {
  id bigint [pk, increment]
  plantilla_receta_id bigint
  nombre_medicamento varchar
  dosis varchar [note: 'Ej: 15 gotas']
  frecuencia varchar [note: 'Ej: cada 8 horas']
  duracion varchar [note: 'Ej: por 5 días']
  created_at timestamp
  updated_at timestamp
}

Table plantillas_laboratorio {
  id bigint [pk, increment]
  doctor_id bigint [note: 'FK → doctores (Dueño de la plantilla)']
  nombre_plantilla varchar [note: 'Ej: Rutina Anual, Perfil Pre-Quirúrgico']
  observaciones_defecto text [nullable, note: 'Detalles que siempre pide para este grupo']
  created_at timestamp
  updated_at timestamp
  eliminado_en timestamp [nullable, note: 'Soft delete']
}

Table items_plantilla_laboratorio {
  id bigint [pk, increment]
  plantilla_laboratorio_id bigint
  catalogo_examen_id bigint [note: 'FK → catalogo_examenes_laboratorio']
  created_at timestamp
  updated_at timestamp
}

// ==========================================
// MÓDULO LABORATORIOS, IMÁGENES Y RECETAS (EJECUCIÓN REAL)
// ==========================================

Table solicitudes_laboratorio {
  id bigint [pk, increment]
  consulta_id bigint
  plantilla_origen_id bigint [nullable, note: 'De dónde se copió (Snapshot)']
  observaciones text [nullable]
  estado enum('Pendiente', 'Recibido') [note: 'Estado de la solicitud']
  resumen_resultado text [nullable]
  ruta_archivo_resultado varchar [nullable]
  created_at timestamp
  updated_at timestamp
  eliminado_en timestamp [nullable, note: 'Soft delete']
}

Table detalles_solicitud_laboratorio {
  id bigint [pk, increment]
  solicitud_laboratorio_id bigint
  categoria_examen varchar [note: 'COPIA inmutable del nombre']
  nombre_examen varchar [note: 'COPIA inmutable del nombre']
  resultado_valor varchar [nullable]
  es_anormal boolean [default: false]
  created_at timestamp
  updated_at timestamp
}

Table recetas {
  id bigint [pk, increment]
  consulta_id bigint
  plantilla_origen_id bigint [nullable, note: 'De dónde se copió (Snapshot)']
  observaciones text [nullable]
  created_at timestamp
  updated_at timestamp
  eliminado_en timestamp [nullable, note: 'Soft delete']
}

Table detalles_receta {
  id bigint [pk, increment]
  receta_id bigint
  nombre_medicamento varchar [note: 'COPIA inmutable del nombre']
  dosis varchar [note: 'COPIA inmutable']
  frecuencia varchar [note: 'COPIA inmutable']
  duracion varchar [note: 'COPIA inmutable']
  created_at timestamp
  updated_at timestamp
}

// ==========================================
// MÓDULO DE VACUNAS (PAI BOLIVIA)
// ==========================================

Table catalogo_vacunas {
  id bigint [pk, increment]
  nombre varchar [note: 'Ej: BCG, Pentavalente 1, SRP']
  mes_recomendado int [note: 'Mes de vida recomendado']
  es_obligatoria boolean [default: true]
  created_at timestamp
  updated_at timestamp
}

Table vacunas_paciente {
  id bigint [pk, increment]
  paciente_id bigint
  catalogo_vacuna_id bigint
  fecha_aplicacion date
  aplicada_por_doctor_id bigint [note: 'FK → doctores']
  observaciones text [nullable]
  created_at timestamp
  updated_at timestamp
}

// ==========================================
// TABLAS OMS: REPLICA EXACTA DE LAS "BOLETAS"
// ==========================================

Table oms_catalogo_graficas {
  id bigint [pk, increment]
  nombre_boleta varchar [note: 'Ej: Girls chart - Talla por edad: 0-6 meses']
  
  // Filtros para saber a quién aplica esta boleta
  genero enum('M', 'F')
  indicador enum('peso_edad', 'talla_edad', 'perimetro_cefalico', 'peso_talla')
  
  // Rangos de la boleta
  unidad_tiempo enum('semanas', 'meses')
  rango_min int [note: 'Ej: 0']
  rango_max int [note: 'Ej: 26 semanas']
  
  created_at timestamp
}

Table oms_datos_graficas {
  id bigint [pk, increment]
  oms_catalogo_grafica_id bigint [note: 'FK a la boleta específica']
  
  // El tiempo (Semana 1, Mes 2, etc.)
  tiempo_valor decimal(5,2) [note: 'Ej: 0, 1, 2, 3...']
  
  // Parámetros LMS universales (fórmula Box-Cox)
  l decimal(8,5)
  m decimal(8,5)
  s decimal(8,5)
  
  // --- Z-SCORES (Modo Médico: -3 a +3) ---
  sd3_neg decimal(6,2)
  sd2_neg decimal(6,2)
  sd1_neg decimal(6,2)
  sd0 decimal(6,2)
  sd1_pos decimal(6,2)
  sd2_pos decimal(6,2)
  sd3_pos decimal(6,2)
  
  // --- PERCENTILES (Modo Padres: P3 a P97) ---
  p1 decimal(6,2)
  p3 decimal(6,2)
  p5 decimal(6,2)
  p15 decimal(6,2)
  p50 decimal(6,2)
  p85 decimal(6,2)
  p97 decimal(6,2)
  p99 decimal(6,2)
  
  created_at timestamp
}

// ==========================================
// RELACIONES (Foreign Keys)
// ==========================================

Ref: doctores.usuario_id - usuarios.id

Ref: pacientes.doctor_responsable_id > doctores.id
Ref: pacientes.user_id > usuarios.id

Ref: consultas.paciente_id > pacientes.id
Ref: consultas.doctor_id > doctores.id
Ref: signos_vitales.consulta_id - consultas.id
Ref: notas_soap.consulta_id - consultas.id

Ref: catalogo_examenes_laboratorio.categoria_laboratorio_id > categorias_laboratorio.id

Ref: plantillas_receta.doctor_id > doctores.id
Ref: items_plantilla_receta.plantilla_receta_id > plantillas_receta.id
Ref: plantillas_laboratorio.doctor_id > doctores.id
Ref: items_plantilla_laboratorio.plantilla_laboratorio_id > plantillas_laboratorio.id
Ref: items_plantilla_laboratorio.catalogo_examen_id > catalogo_examenes_laboratorio.id

Ref: recetas.consulta_id > consultas.id
Ref: recetas.plantilla_origen_id > plantillas_receta.id
Ref: detalles_receta.receta_id > recetas.id

Ref: solicitudes_laboratorio.consulta_id > consultas.id
Ref: detalles_solicitud_laboratorio.solicitud_laboratorio_id > solicitudes_laboratorio.id
Ref: solicitudes_laboratorio.plantilla_origen_id > plantillas_laboratorio.id

Ref: vacunas_paciente.paciente_id > pacientes.id
Ref: vacunas_paciente.catalogo_vacuna_id > catalogo_vacunas.id
Ref: vacunas_paciente.aplicada_por_doctor_id > doctores.id

Ref: oms_datos_graficas.oms_catalogo_grafica_id > oms_catalogo_graficas.id
```