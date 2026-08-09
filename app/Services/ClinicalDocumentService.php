<?php

namespace App\Services;

use App\DTOs\ClinicalDocumentDTO;
use App\DTOs\LabStudyItemDTO;
use App\DTOs\MedicationItemDTO;
use App\Models\ClinicSetting;
use App\Models\Consultation;
use App\Models\LaboratoryExam;
use App\Models\LaboratoryRequest;
use App\Models\Prescription;
use App\ValueObjects\Age;
use App\ValueObjects\PaperSize;

class ClinicalDocumentService
{
    private const SPECIALTY = 'Especialista en Pediatría';

    private const FALLBACK_PHONE = '76387108';

    /** @var array<string, string>|null */
    private ?array $categoryMap = null;

    /**
     * Construye el documento de una receta (media hoja oficio).
     */
    public function receta(Prescription $prescription): ClinicalDocumentDTO
    {
        $prescription->loadMissing(['consultation.patient', 'consultation.doctor', 'consultation.vitalSigns', 'items']);
        $consultation = $prescription->consultation;

        if ($consultation === null || $consultation->patient === null) {
            return $this->documentWithErrors('receta', PaperSize::halfLegal(), ['No se puede generar la receta sin un paciente.']);
        }

        $errors = $this->validateReceta($prescription, $consultation);

        $items = $prescription->items
            ->map(fn ($item) => MedicationItemDTO::fromArray([
                'medication_name' => $item->medication_name,
                'dose' => $item->dose,
                'administration_route' => $item->administration_route,
                'frequency' => $item->frequency,
                'duration' => $item->duration,
                'instructions' => $item->instructions,
            ]))
            ->values()
            ->all();

        $paper = PaperSize::halfLegal();
        $overflow = $this->estimateRecetaOverflow($items);

        return new ClinicalDocumentDTO(
            paper: $paper,
            title: 'receta',
            patientName: $consultation->patient->full_name,
            ageText: $this->ageText($consultation),
            dateText: $this->dateText($consultation),
            dateIso: $consultation->consultation_date->format('Y-m-d'),
            weight: $consultation->vitalSigns?->weight !== null
                ? (string) $consultation->vitalSigns->weight
                : null,
            height: $consultation->vitalSigns?->height !== null
                ? (string) $consultation->vitalSigns->height
                : null,
            diagnosis: $prescription->reason ?: null,
            observations: $prescription->observations ?: null,
            items: $items,
            doctorName: $consultation->doctor ? $consultation->doctor->full_name : 'Dra. Karen Zaconeta S.',
            specialty: $consultation->doctor ? ($consultation->doctor->specialty ?: self::SPECIALTY) : self::SPECIALTY,
            phone: $this->phone(),
            errors: $errors,
            overflow: $overflow,
        );
    }

    /**
     * Construye la orden de laboratorio (hoja oficio).
     */
    public function ordenLaboratorio(LaboratoryRequest $laboratoryRequest): ClinicalDocumentDTO
    {
        $laboratoryRequest->loadMissing(['consultation.patient', 'consultation.doctor', 'items']);
        $consultation = $laboratoryRequest->consultation;

        if ($consultation === null || $consultation->patient === null) {
            return $this->documentWithErrors('orden_laboratorio', PaperSize::legal(), ['No se puede generar la orden sin un paciente.']);
        }

        $errors = $this->validateOrden($laboratoryRequest, $consultation);

        $items = $laboratoryRequest->items
            ->map(function ($item) {
                return new LabStudyItemDTO(
                    exam_name: $item->exam_name,
                    parameter_name: $item->parameter_name,
                    category: $this->categoryFor($item->exam_name),
                );
            })
            ->values()
            ->all();

        return new ClinicalDocumentDTO(
            paper: PaperSize::legal(),
            title: 'orden_laboratorio',
            patientName: $consultation->patient->full_name,
            ageText: $this->ageText($consultation),
            dateText: $this->dateText($consultation),
            dateIso: $consultation->consultation_date->format('Y-m-d'),
            weight: null,
            height: null,
            diagnosis: $laboratoryRequest->presumptive_diagnosis ?: null,
            observations: $laboratoryRequest->observations ?: null,
            items: $items,
            doctorName: $consultation->doctor ? $consultation->doctor->full_name : 'Dra. Karen Zaconeta S.',
            specialty: $consultation->doctor ? ($consultation->doctor->specialty ?: self::SPECIALTY) : self::SPECIALTY,
            phone: $this->phone(),
            errors: $errors,
        );
    }

    /**
     * @param  array<int, string>  $errors
     */
    private function documentWithErrors(string $title, PaperSize $paper, array $errors): ClinicalDocumentDTO
    {
        return new ClinicalDocumentDTO(
            paper: $paper,
            title: $title,
            patientName: '',
            ageText: '',
            dateText: '',
            dateIso: '',
            weight: null,
            height: null,
            diagnosis: null,
            observations: null,
            items: [],
            doctorName: '',
            specialty: self::SPECIALTY,
            phone: $this->phone(),
            errors: $errors,
        );
    }

    /**
     * @return array<int, string>
     */
    private function validateReceta(Prescription $prescription, Consultation $consultation): array
    {
        $errors = [];

        if ($prescription->items->isEmpty()) {
            $errors[] = 'La receta no tiene medicamentos ni indicaciones.';
        }

        return $errors;
    }

    /**
     * @return array<int, string>
     */
    private function validateOrden(LaboratoryRequest $laboratoryRequest, Consultation $consultation): array
    {
        $errors = [];

        if ($laboratoryRequest->items->isEmpty()) {
            $errors[] = 'La orden no tiene estudios solicitados.';
        }

        return $errors;
    }

    private function ageText(Consultation $consultation): string
    {
        $patient = $consultation->patient;

        if ($patient === null || $patient->date_of_birth === null) {
            return '—';
        }

        return Age::fromDates($patient->date_of_birth, $consultation->consultation_date)->forDisplayPediatric();
    }

    private function dateText(Consultation $consultation): string
    {
        return $consultation->consultation_date->format('d/m/Y');
    }

    private function phone(): string
    {
        return ClinicSetting::current()->phone ?: self::FALLBACK_PHONE;
    }

    /** Mapea el nombre del examen a su categoría del catálogo; "Otros" si no coincide. */
    private function categoryFor(string $examName): string
    {
        if ($this->categoryMap === null) {
            $this->categoryMap = LaboratoryExam::with('category')
                ->get()
                ->mapWithKeys(fn ($exam) => [$exam->name => $exam->category ? $exam->category->name : 'Otros'])
                ->all();
        }

        return $this->categoryMap[$examName] ?? 'Otros';
    }

    /**
     * Estima si los medicamentos caben en el área de escritura de la media
     * hoja oficio (sin cortar ni pasar a una segunda página).
     *
     * @param  array<int, MedicationItemDTO>  $items
     */
    private function estimateRecetaOverflow(array $items): bool
    {
        // Área útil de medicamentos en mm (media oficio menos encabezado,
        // datos del paciente, pie y márgenes).
        $availableMm = 88.0;

        $usedMm = 0.0;

        foreach ($items as $item) {
            $baseMm = 10.0;

            if ($item->instructions !== null && $item->instructions !== '') {
                $baseMm += 4.5;
            }

            if (mb_strlen($item->medication_name) > 42) {
                $baseMm += 4.0;
            }

            if (mb_strlen((string) $item->instructions) > 90) {
                $baseMm += 4.0;
            }

            $usedMm += $baseMm;
        }

        return $usedMm > $availableMm;
    }
}
