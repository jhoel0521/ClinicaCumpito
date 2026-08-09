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

            .exam-col-header {
                background: #1e3a5f;
                color: white;
                font-weight: bold;
                font-size: 10px;
                text-transform: uppercase;
                letter-spacing: 0.5px;
                padding: 5px 8px;
            }

            .exam-col-cell {
                border: 1px solid #d1d5db;
                vertical-align: top;
                padding: 0;
            }

            .param-item {
                padding: 4px 8px;
                font-size: 10px;
                border-bottom: 1px solid #f0f0f0;
            }

            .col-dx {
                padding: 4px 8px;
                font-size: 9px;
                color: #1d4ed8;
                border-top: 1px solid #dbeafe;
                background: #eff6ff;
            }

            .col-obs {
                padding: 4px 8px;
                font-size: 9px;
                color: #555;
                border-top: 1px dashed #e5e7eb;
            }

            .col-w-1 {
                width: 100%;
            }

            .col-w-2 {
                width: 50%;
            }

            .col-w-3 {
                width: 33%;
            }

            .result-normal {
                padding-left: 18px;
                font-size: 9px;
                color: #374151;
                border-bottom: 1px solid #f0f0f0;
            }

            .result-abnormal {
                padding-left: 18px;
                font-size: 9px;
                color: #dc2626;
                border-bottom: 1px solid #f0f0f0;
            }
        </style>
    </head>

    <body>
        <div class="page">
            @include('pdf._header')

            {{-- Título en caja --}}
            <div class="lab-title-box">ORDEN DE LABORATORIO</div>

            {{-- Datos del paciente --}}
            @php
                $pat = $consultation->patient;
                $ageStr = $pat?->date_of_birth ? \App\ValueObjects\Age::fromDates($pat->date_of_birth, $consultation->consultation_date)->forDisplayPediatric() : '';
                $labs = $consultation->laboratoryRequests;
                $total = $labs->count();
                $cols = $total === 0 ? 1 : ($total === 1 ? 1 : ($total === 2 ? 2 : 3));
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

            @if ($labs->isEmpty())
                <p style="color: #888; font-style: italic">Sin solicitudes de laboratorio en esta consulta.</p>
            @endif

            @if ($labs->isNotEmpty())
                {{-- Grid de solicitudes: máximo 3 columnas por fila --}}
                @foreach ($labs->chunk($cols) as $row)
                    <table style="width: 100%; border-collapse: collapse; margin-bottom: 6px; table-layout: fixed">
                        <tr>
                            @foreach ($row as $lab)
                                @php
                                    $examName = $lab->items->first()?->exam_name ?? 'Sin examen';
                                @endphp

                                <td class="exam-col-cell col-w-{{ $cols }}">
                                    @include('pdf._lab-all-cell', ['lab' => $lab, 'examName' => $examName])
                                </td>
                            @endforeach

                            @for ($e = $row->count(); $e < $cols; $e++)
                                <td class="exam-col-cell col-w-{{ $cols }}" style="background: #fafafa"></td>
                            @endfor
                        </tr>
                    </table>
                @endforeach
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
