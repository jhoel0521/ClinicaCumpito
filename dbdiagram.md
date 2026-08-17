```
// ==========================================
// Esquema real — generado a partir de database/migrations/ (36 migraciones)
// Motor: PostgreSQL. PKs en UUID salvo excepción marcada.
// No incluye tablas de infraestructura de Laravel (sessions, cache, jobs,
// password_reset_tokens) ni las tablas de spatie/laravel-permission
// (permissions, roles, model_has_roles, model_has_permissions,
// role_has_permissions) — su esquema es el estándar del paquete.
// ==========================================

// ==========================================
// AUTENTICACIÓN
// ==========================================

Table users {
  id uuid [pk]
  doctor_id uuid [unique, nullable, note: 'FK -> doctors (1:1 opcional, doctor con cuenta de acceso)']
  name varchar
  email varchar [unique]
  email_verified_at timestamp [nullable]
  password varchar
  phone_number varchar(20) [nullable]
  remember_token varchar [nullable]
  two_factor_secret text [nullable, note: 'Laravel Fortify']
  two_factor_recovery_codes text [nullable]
  two_factor_confirmed_at timestamp [nullable]
  created_at timestamp
  updated_at timestamp
  deleted_at timestamp [nullable, note: 'Soft delete']
}

// ==========================================
// MÓDULO MÉDICO
// ==========================================

Table doctors {
  id uuid [pk]
  full_name varchar
  specialty varchar [nullable]
  license_number varchar [unique]
  active boolean [default: true]
  created_at timestamp
  updated_at timestamp
  deleted_at timestamp [nullable, note: 'Soft delete']
}

// ==========================================
// MÓDULO PACIENTES Y ANTECEDENTES
// ==========================================

Table patients {
  id uuid [pk]
  responsible_doctor_id uuid [nullable, note: 'FK -> doctors']
  user_id uuid [nullable, note: 'FK -> users, si el paciente también es usuario']
  full_name varchar
  date_of_birth date [nullable]
  gender enum('M', 'F') [nullable]

  // Datos al nacer (línea base para curvas OMS)
  birth_weight decimal(5,2) [nullable, note: 'kg']
  birth_height decimal(5,2) [nullable, note: 'cm']
  birth_head_circumference decimal(5,2) [nullable, note: 'cm']
  birth_type enum('Normal', 'Cesarean') [nullable]
  birth_place varchar [nullable]
  heel_prick_done boolean [nullable, note: 'Tamizaje neonatal / "prueba del talón": true=realizó, false=no realizó, null=sin dato']

  blood_group varchar(5) [nullable]
  allergies text [nullable]
  pathologies text [nullable]
  surgeries text [nullable]

  created_at timestamp
  updated_at timestamp
  deleted_at timestamp [nullable, note: 'Soft delete']
}

Table medical_conditions {
  id uuid [pk]
  name varchar [unique, note: 'Ej: Chagas, Sífilis, HIV']
  description text [nullable]
  created_at timestamp
  updated_at timestamp
}

Table patient_medical_conditions {
  patient_id uuid
  medical_condition_id uuid
  status enum('Positive', 'Negative', 'Not tested')
  notes text [nullable]
  created_at timestamp
  updated_at timestamp

  indexes {
    (patient_id, medical_condition_id) [pk]
  }
}

// ==========================================
// HISTORIA CLÍNICA HÍBRIDA Y SOAP
// ==========================================

Table consultations {
  id uuid [pk]
  patient_id uuid
  doctor_id uuid [nullable, note: 'Nullable: consultas manuales cargadas por rol Técnico sin médico asignado']

  type enum('digital', 'manual') [note: 'digital = SOAP en el sistema, manual = escaneo histórico']
  status enum('draft', 'saved', 'finalized') [default: 'saved']
  consultation_date datetime

  // Sistema híbrido (archivos físicos escaneados)
  scanned_file_path varchar [nullable]
  scanned_file_name varchar [nullable]
  pending_transcription boolean [default: false]

  created_at timestamp
  updated_at timestamp
  deleted_at timestamp [nullable, note: 'Soft delete']
}

Table vital_signs {
  id uuid [pk]
  consultation_id uuid [unique]

  weight decimal(5,2) [nullable, note: 'kg']
  height decimal(5,2) [nullable, note: 'cm']
  head_circumference decimal(5,2) [nullable, note: 'cm']
  temperature decimal(4,2) [nullable, note: '°C']
  created_at timestamp
  updated_at timestamp
}

Table soap_notes {
  id uuid [pk]
  consultation_id uuid [unique]

  subjective text [nullable]
  objective text [nullable]
  assessment text [nullable]
  plan text [nullable]
  created_at timestamp
  updated_at timestamp
}

// ==========================================
// CATÁLOGOS MÉDICOS (Laboratorio y Vacunas)
// ==========================================

Table laboratory_categories {
  id uuid [pk]
  name varchar [unique, note: 'Ej: Hematología, Orina, Rayos X, Ecografía']
  description text [nullable]
  created_at timestamp
  updated_at timestamp
  deleted_at timestamp [nullable, note: 'Soft delete']
}

Table laboratory_exams {
  id uuid [pk]
  category_id uuid
  name varchar [note: 'Ej: Hemograma, Radiografía de Tórax, Ecografía Abdominal']
  description text [nullable]
  unit varchar [nullable]
  reference_range varchar [nullable]
  created_at timestamp
  updated_at timestamp
  deleted_at timestamp [nullable, note: 'Soft delete']
}

Table laboratory_exam_parameters {
  id uuid [pk]
  exam_id uuid
  name varchar [note: 'Parámetro individual dentro de un examen, ej: Hemoglobina dentro de Hemograma']
  unit varchar [nullable]
  reference_range varchar [nullable]
  sort_order integer [default: 0]
  created_at timestamp
  updated_at timestamp
}

Table vaccines {
  id uuid [pk]
  name varchar [note: 'Catálogo PAI Bolivia']
  disease_prevented varchar [nullable]
  recommended_age varchar [nullable]
  dose_sequence integer [nullable]
  min_age_months smallint [nullable, note: '0 = recién nacido']
  created_at timestamp
  updated_at timestamp
  deleted_at timestamp [nullable, note: 'Soft delete']
}

// ==========================================
// PLANTILLAS (Ahorro de tiempo)
// ==========================================

Table prescription_templates {
  id uuid [pk]
  doctor_id uuid [note: 'Dueño de la plantilla']
  name varchar
  description text [nullable]
  is_active boolean [default: true]
  created_at timestamp
  updated_at timestamp
  deleted_at timestamp [nullable, note: 'Soft delete']
}

Table prescription_template_items {
  id uuid [pk]
  template_id uuid
  custom_medication_name varchar [nullable]
  dose varchar [nullable, note: 'Dosis + cantidad unificadas en un solo campo libre (migración 2026-08-09)']
  frequency varchar [nullable]
  duration varchar [nullable]
  instructions text [nullable]
  created_at timestamp
  updated_at timestamp
}

// ==========================================
// EJECUCIÓN REAL: VACUNAS, RECETAS Y LABORATORIOS
// ==========================================

Table patient_vaccines {
  id uuid [pk]
  patient_id uuid
  consultation_id uuid [nullable, note: 'Si se registró durante una consulta interna']
  vaccine_id uuid
  applied_by_doctor_id uuid [nullable, note: 'Null cuando applied_elsewhere = true']
  application_site varchar [nullable]
  applied_at timestamp
  dose_number tinyint [nullable]
  notes text [nullable]
  applied_elsewhere boolean [default: false, note: 'La mamá informa que fue aplicada en otro centro']
  created_at timestamp
  updated_at timestamp

  indexes {
    (consultation_id, applied_at)
    (patient_id, applied_at)
  }
}

Table prescriptions {
  id uuid [pk]
  consultation_id uuid
  reason varchar [nullable]
  observations text [nullable]
  created_at timestamp
  updated_at timestamp
  deleted_at timestamp [nullable, note: 'Soft delete']
}

Table prescription_items {
  id uuid [pk]
  prescription_id uuid
  medication_name varchar
  dose varchar [note: 'Dosis + cantidad unificadas en un solo campo libre (migración 2026-08-09)']
  administration_route varchar [nullable, note: 'Agregado 2026-08-09']
  frequency varchar
  duration varchar
  instructions text [nullable]
  created_at timestamp
  updated_at timestamp
}

Table laboratory_requests {
  id uuid [pk]
  consultation_id uuid
  observations text [nullable]
  status varchar [default: 'pending', note: 'pending | ... ver LaboratoryRequestService']
  presumptive_diagnosis text [nullable, note: 'Agregado 2026-03-20, va impreso en la orden']
  created_at timestamp
  updated_at timestamp
  deleted_at timestamp [nullable, note: 'Soft delete']
}

Table laboratory_request_items {
  id uuid [pk]
  laboratory_request_id uuid
  exam_name varchar [note: 'Copia del nombre al momento de solicitar']
  parameter_name varchar [nullable]
  created_at timestamp
  updated_at timestamp
  note: 'Columnas de resultado (result_value/is_abnormal/result_notes/result_received_at) y "indications" existieron brevemente y fueron eliminadas: los resultados viven ahora en laboratory_item_results.'
}

Table laboratory_item_results {
  id uuid [pk]
  laboratory_request_item_id uuid
  consultation_id uuid [nullable, note: 'Consulta en la que se registró el resultado (puede ser posterior a la solicitud)']
  value varchar [nullable]
  report_text text [nullable, note: 'Informe libre: radiólogo, cultivo, etc.']
  is_abnormal boolean [default: false]
  sort_order smallint [default: 0]
  created_at timestamp
  updated_at timestamp
}

Table laboratory_attachments {
  id uuid [pk]
  laboratory_request_id uuid [nullable, note: 'Exactamente uno de los dos FKs está seteado']
  laboratory_request_item_id uuid [nullable]
  file_path varchar
  original_name varchar [nullable]
  mime_type varchar [nullable, note: 'image/jpeg, application/pdf, etc.']
  sort_order smallint [default: 0]
  created_at timestamp
  updated_at timestamp
}

// ==========================================
// BOLETAS OMS (crecimiento) — replica de los estándares oficiales
// ==========================================

Table oms_catalogo_graficas {
  id uuid [pk]
  nombre varchar
  codigo varchar [unique]
  descripcion text [nullable]
  tipo_grafica varchar [note: 'peso_talla | talla_edad | peso_edad | perimetro_cefalico']
  rango_edad varchar [note: 'Texto libre, ej. "0-24 meses"']
  sexo varchar [note: 'M | F']
  minimo_z_score integer [default: -3]
  maximo_z_score integer [default: 3]
  minimo_percentil integer [default: 3]
  maximo_percentil integer [default: 97]
  created_at timestamp
  updated_at timestamp
  deleted_at timestamp [nullable, note: 'Soft delete']
}

Table oms_datos_graficas {
  id uuid [pk]
  oms_catalogo_grafica_id uuid

  x_value decimal(8,4) [note: 'Eje X: edad en meses o longitud en cm']

  // Parámetros LMS (fórmula Box-Cox)
  l_value decimal(10,6)
  m_value decimal(10,6)
  s_value decimal(10,6)

  // Z-Scores (Modo Médico: -3 a +3)
  sd3neg decimal(8,4) [nullable]
  sd2neg decimal(8,4) [nullable]
  sd1neg decimal(8,4) [nullable]
  sd0 decimal(8,4) [nullable]
  sd1 decimal(8,4) [nullable]
  sd2 decimal(8,4) [nullable]
  sd3 decimal(8,4) [nullable]

  // Percentiles (Modo Padres: P3 a P97)
  p3 decimal(8,4) [nullable]
  p15 decimal(8,4) [nullable]
  p50 decimal(8,4) [nullable]
  p85 decimal(8,4) [nullable]
  p97 decimal(8,4) [nullable]

  created_at timestamp
  updated_at timestamp

  indexes {
    (oms_catalogo_grafica_id, x_value) [unique]
  }
}

// ==========================================
// CONFIGURACIÓN Y AUDITORÍA
// ==========================================

Table clinic_settings {
  id bigint [pk, increment, note: 'Única tabla del dominio sin UUID (registro único de configuración)']
  name varchar
  address varchar [nullable]
  phone varchar [nullable]
  whatsapp varchar [nullable]
  logo_path varchar [nullable, note: 'Ruta relativa en storage/app/public, usado en membrete de recetas/laboratorios impresos']
  created_at timestamp
  updated_at timestamp
}

Table audit_logs {
  id uuid [pk]
  user_id uuid [nullable, note: 'Null on delete']
  action varchar [note: 'created | updated | deleted']
  auditable_type varchar [note: 'Clase del modelo auditado']
  auditable_id uuid
  old_values jsonb [nullable]
  new_values jsonb [nullable]
  ip_address varchar(45) [nullable]
  user_agent text [nullable]
  created_at timestamp
  updated_at timestamp

  indexes {
    (auditable_type, auditable_id)
    (user_id)
  }
}

// ==========================================
// RELACIONES (Foreign Keys)
// ==========================================

Ref: users.doctor_id - doctors.id [delete: set null]

Ref: patients.responsible_doctor_id > doctors.id [delete: set null]
Ref: patients.user_id > users.id [delete: set null]
Ref: patient_medical_conditions.patient_id > patients.id [delete: cascade]
Ref: patient_medical_conditions.medical_condition_id > medical_conditions.id [delete: restrict]

Ref: consultations.patient_id > patients.id [delete: cascade]
Ref: consultations.doctor_id > doctors.id [delete: set null]
Ref: vital_signs.consultation_id - consultations.id [delete: cascade]
Ref: soap_notes.consultation_id - consultations.id [delete: cascade]

Ref: laboratory_exams.category_id > laboratory_categories.id [delete: cascade]
Ref: laboratory_exam_parameters.exam_id > laboratory_exams.id [delete: cascade]

Ref: prescription_templates.doctor_id > doctors.id [delete: cascade]
Ref: prescription_template_items.template_id > prescription_templates.id [delete: cascade]

Ref: patient_vaccines.patient_id > patients.id [delete: cascade]
Ref: patient_vaccines.consultation_id > consultations.id [delete: set null]
Ref: patient_vaccines.vaccine_id > vaccines.id [delete: restrict]
Ref: patient_vaccines.applied_by_doctor_id > doctors.id [delete: set null]

Ref: prescriptions.consultation_id > consultations.id [delete: cascade]
Ref: prescription_items.prescription_id > prescriptions.id [delete: cascade]

Ref: laboratory_requests.consultation_id > consultations.id [delete: cascade]
Ref: laboratory_request_items.laboratory_request_id > laboratory_requests.id [delete: cascade]
Ref: laboratory_item_results.laboratory_request_item_id > laboratory_request_items.id [delete: cascade]
Ref: laboratory_item_results.consultation_id > consultations.id [delete: set null]
Ref: laboratory_attachments.laboratory_request_id > laboratory_requests.id [delete: cascade]
Ref: laboratory_attachments.laboratory_request_item_id > laboratory_request_items.id [delete: cascade]

Ref: oms_datos_graficas.oms_catalogo_grafica_id > oms_catalogo_graficas.id [delete: cascade]

Ref: audit_logs.user_id > users.id [delete: set null]
```
