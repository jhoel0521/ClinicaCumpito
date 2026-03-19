<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="UTF-8" />
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        @include('pdf._style')
    </head>
    <body>
        <div class="page">
            @include('pdf._header')

            <div style="font-size: 13px; font-weight: bold; color: #1a1a1a; margin-bottom: 12px">
                @if ($laboratoryRequest->status === 'received')
                    RESULTADOS DE LABORATORIO
                @else
                    SOLICITUD DE LABORATORIO
                @endif
                &nbsp;
                @if ($laboratoryRequest->status === 'received')
                    <span class="badge-received">Resultados recibidos</span>
                @else
                    <span class="badge-pending">Solicitud pendiente</span>
                @endif
            </div>

            @if ($laboratoryRequest->items->isNotEmpty())
                <table class="data-table">
                    <thead>
                        <tr>
                            <th style="width: 22%">Examen</th>
                            <th style="width: 20%">Parámetro</th>
                            <th style="width: 22%">Indicaciones</th>
                            @if ($laboratoryRequest->status === 'received')
                                <th style="width: 18%">Resultado</th>
                                <th style="width: 18%">Notas</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($laboratoryRequest->items as $item)
                            <tr>
                                <td><strong>{{ $item->exam_name }}</strong></td>
                                <td>{{ $item->parameter_name ?: '—' }}</td>
                                <td>{{ $item->indications ?: '—' }}</td>
                                @if ($laboratoryRequest->status === 'received')
                                    <td class="{{ $item->is_abnormal ? 'abnormal' : '' }}">
                                        {{ $item->result_value ?: '—' }}
                                        @if ($item->is_abnormal)
                                            ⚠
                                        @endif
                                    </td>
                                    <td>{{ $item->result_notes ?: '—' }}</td>
                                @endif
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <p style="color: #888; font-style: italic">Sin exámenes registrados.</p>
            @endif

            @if ($laboratoryRequest->observations)
                <div class="observations">Observaciones: {{ $laboratoryRequest->observations }}</div>
            @endif

            <div class="footer">
                <div style="margin-bottom: 32px">&nbsp;</div>
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
