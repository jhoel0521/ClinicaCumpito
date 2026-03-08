<?php

namespace App\Contracts;

interface GrowthChartServiceContract
{
    /**
     * Retorna las curvas de referencia OMS (líneas SD -3 a +3) para una gráfica.
     * Estructura lista para ser consumida por Chart.js.
     *
     * @return array{labels: array<int, float>, datasets: array<int, array{label: string, color: string, data: array<int, float|null>}>}
     */
    public function getReferenceDatasets(string $graficaId): array;

    /**
     * Retorna los puntos de medición históricos del paciente mapeados a la
     * gráfica indicada, incluyendo z-score y categoría clínica por punto.
     *
     * @return array<int, array{x: float, y: float, z_score: float, category: string, date: string}>
     */
    public function getPatientDatapoints(string $patientId, string $graficaId): array;

    /**
     * Combina curvas de referencia OMS y puntos del paciente en una
     * estructura completa lista para Chart.js.
     * Incluye `reference_datasets` (7 SD, modo Médico) y
     * `percentile_datasets` (P3/P50/P97, modo Padres).
     *
     * @return array{grafica: array{id: string, nombre: string, tipo_grafica: string}, labels: array<int, float>, reference_datasets: array<int, array{label: string, color: string, data: array<int, float|null>}>, percentile_datasets: array<int, array{label: string, color: string, dash: bool, data: array<int, float|null>}>, patient_datapoints: array<int, array{x: float, y: float, z_score: float, category: string, date: string}>}
     */
    public function prepareChartData(string $patientId, string $graficaId): array;
}
