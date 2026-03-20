<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="UTF-8" />
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        @include('pdf._style')
        <style>
            .lab-title-box {
                text-align: center;
                border: 2px solid #1a1a1a;
                padding: 9px 12px;
                font-size: 16px;
                font-weight: bold;
                letter-spacing: 3px;
                margin-bottom: 12px;
            }

            .patient-row {
                width: 100%;
                border-collapse: collapse;
                font-size: 11px;
                margin-bottom: 12px;
            }

            .patient-row td {
                padding: 3px 4px 3px 0;
            }

            .pfield {
                border-bottom: 1px solid #333;
                display: inline-block;
                min-width: 80px;
            }

            .exam-cat-header {
                background: #1e3a5f;
                color: white;
                font-weight: bold;
                font-size: 11px;
                text-transform: uppercase;
                letter-spacing: 1px;
                padding: 6px 10px;
            }

            .param-cell {
                border: 1px solid #d1d5db;
                padding: 5px 10px;
                font-size: 11px;
                vertical-align: middle;
                width: 50%;
            }

            .obs-box {
                margin-top: 10px;
                padding: 5px 10px;
                border: 1px solid #d1d5db;
                border-left: 3px solid #1e3a5f;
                font-size: 10px;
                color: #333;
            }
        </style>
    </head>

    <body>
        <div class="page">
            {{-- Cabecera de clínica (logo, nombre, dirección) --}}
            @include('pdf._header')

            {{-- Título en caja --}}
            <div class="lab-title-box">
                @if ($laboratoryRequest->status === 'received')
                    RESULTADOS DE LABORATORIO
                @else
                    ORDEN DE LABORATORIO
                @endif
            </div>

            {{-- Datos del paciente con líneas --}}
            @php
                $pat = $consultation->patient;
                $ageStr = '';
                if ($pat?->date_of_birth) {
                    $months = (int) \Carbon\Carbon::parse($pat->date_of_birth)->diffInMonths(now());
                    $yrs = (int) floor($months / 12);
                    $rem = $months % 12;
                    $ageStr = $yrs > 0 ? "{$yrs} año(s)" : '';
                    $ageStr .= $yrs > 0 && $rem > 0 ? " {$rem} mes(es)" : ($yrs === 0 ? "{$rem} mes(es)" : '');
                }
            @endphp

            <table class="patient-row">
                <tr>
                    <td colspan="4">
                        <strong>NOMBRES:</strong>
                        <span class="pfield" style="min-width: 260px">{{ $pat?->full_name ?? '' }}</span>
                    </td>
                    <td style="text-align: right">
                        <strong>FECHA:</strong>
                        <span class="pfield" style="min-width: 80px">
                            {{ $consultation->consultation_date?->format('d/m/Y') ?? now()->format('d/m/Y') }}
                        </span>
                    </td>
                </tr>
                <tr>
                    <td>
                        <strong>EDAD:</strong>
                        <span class="pfield" style="min-width: 90px">{{ $ageStr ?: '—' }}</span>
                    </td>
                    <td colspan="2">
                        <strong>MÉDICO:</strong>
                        <span class="pfield" style="min-width: 180px">
                            {{ $consultation->doctor?->full_name ?? '' }}
                        </span>
                    </td>
                    <td colspan="2" style="text-align: right">
                        @if ($consultation->doctor?->license_number)
                            <strong>MAT.:</strong>
                            <span class="pfield" style="min-width: 80px">
                                {{ $consultation->doctor->license_number }}
                            </span>
                        @endif
                    </td>
                </tr>
            </table>

            @php
                $examName = $laboratoryRequest->items->first()?->exam_name ?? 'Sin examen';
            @endphp

            {{-- Diagnóstico presuntivo --}}
            @if ($laboratoryRequest->presumptive_diagnosis)
                <div
                    style="
                        margin-bottom: 8px;
                        padding: 5px 10px;
                        background: #eff6ff;
                        border-left: 3px solid #2563eb;
                        font-size: 11px;
                    "
                >
                    <strong>Diagnóstico:</strong>
                    {{ $laboratoryRequest->presumptive_diagnosis }}
                </div>
            @endif

            @if ($laboratoryRequest->items->isNotEmpty())
                @if ($laboratoryRequest->status === 'pending')
                    {{-- SOLICITUD --}}

                    {{-- Header del examen --}}
                    <div class="exam-cat-header">{{ $examName }}</div>

                    {{-- Parámetros en grid 2 columnas --}}
                    <table style="width: 100%; border-collapse: collapse; margin-bottom: 2px">
                        <tbody>
                            @foreach ($laboratoryRequest->items->chunk(2) as $row)
                                <tr>
                                    @foreach ($row as $item)
                                        <td class="param-cell">
                                            ○&nbsp;{{ $item->parameter_name ?: '(examen completo)' }}
                                        </td>
                                    @endforeach

                                    @if ($row->count() === 1)
                                        <td class="param-cell">&nbsp;</td>
                                    @endif
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    {{-- RESULTADOS --}}
                    <div class="exam-cat-header">{{ $examName }} &nbsp;— RESULTADOS</div>

                    @foreach ($laboratoryRequest->items as $item)
                        @if ($item->parameter_name)
                            <div
                                style="
                                    font-size: 11px;
                                    font-weight: bold;
                                    color: #374151;
                                    padding: 4px 10px;
                                    background: #f9fafb;
                                    border-bottom: 1px solid #e5e7eb;
                                    margin-top: 4px;
                                "
                            >
                                {{ $item->parameter_name }}
                            </div>
                        @endif

                        @if ($item->results->isNotEmpty())
                            <table class="data-table" style="margin-top: 0">
                                <thead>
                                    <tr>
                                        <th style="width: 28%">Parámetro</th>
                                        <th style="width: 20%">Valor</th>
                                        <th style="width: 22%">Referencia</th>
                                        <th style="width: 25%">Informe</th>
                                        <th style="width: 5%">⚠</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($item->results as $result)
                                        <tr>
                                            <td>{{ $result->parameter_name ?: '—' }}</td>
                                            <td class="{{ $result->is_abnormal ? 'abnormal' : '' }}">
                                                {{ $result->value ?: '—' }}
                                            </td>
                                            <td>{{ $result->reference_range ?: '—' }}</td>
                                            <td style="font-size: 10px; font-style: italic">
                                                {{ $result->report_text ?: '—' }}
                                            </td>
                                            <td style="text-align: center">
                                                @if ($result->is_abnormal)
                                                    ⚠
                                                @else
                                                    ✓
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @else
                            <p style="color: #9ca3af; font-style: italic; font-size: 10px; padding: 4px 10px">
                                Sin resultados registrados.
                            </p>
                        @endif
                    @endforeach
                @endif
            @else
                <div class="exam-cat-header">{{ $examName }}</div>
                <p style="color: #888; font-style: italic; padding: 6px 10px; font-size: 10px">
                    Sin parámetros registrados.
                </p>
            @endif

            {{-- Observaciones --}}
            <div class="obs-box">
                <strong>Observaciones:</strong>
                @if ($laboratoryRequest->observations)
                    {{ $laboratoryRequest->observations }}
                @else
                    <span style="color: #bbb">_____________________________________________</span>
                @endif
            </div>

            @php
                $totalAtt =
                    $laboratoryRequest->attachments->count() +
                    $laboratoryRequest->items->sum(fn ($i) => $i->attachments->count());
            @endphp

            @if ($totalAtt > 0)
                <div style="margin-top: 6px; font-size: 9px; color: #6b7280; font-style: italic">
                    Esta solicitud tiene {{ $totalAtt }} archivo(s) adjunto(s) disponibles en el sistema.
                </div>
            @endif

            <div class="footer">
                <div>_________________________________</div>
                <div><strong>{{ $consultation->doctor?->full_name ?? '' }}</strong></div>
                @if ($consultation->doctor?->specialty)
                    <div>{{ $consultation->doctor->specialty }}</div>
                @endif

                @if ($consultation->doctor?->license_number)
                    <div>Mat. {{ $consultation->doctor->license_number }}</div>
                @endif
            </div>
        </div>
    </body>
</html>
