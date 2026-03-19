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
                SOLICITUD / RESULTADOS DE LABORATORIO
                <span style="font-size: 11px; font-weight: normal; color: #555">
                    ({{ $consultation->laboratoryRequests->count() }}
                    {{ $consultation->laboratoryRequests->count() === 1 ? 'solicitud' : 'solicitudes' }})
                </span>
            </div>

            @if ($consultation->laboratoryRequests->isEmpty())
                <p style="color: #888; font-style: italic">Sin solicitudes de laboratorio en esta consulta.</p>
            @else
                @foreach ($consultation->laboratoryRequests as $i => $lab)
                    @if ($i > 0)
                        <hr class="separator" />
                    @endif

                    <h3 class="section-title">
                        Laboratorio #{{ $i + 1 }} &nbsp;
                        @if ($lab->status === 'received')
                            <span class="badge-received">Resultados recibidos</span>
                        @else
                            <span class="badge-pending">Solicitud pendiente</span>
                        @endif
                    </h3>

                    @if ($lab->items->isNotEmpty())
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th style="width: 22%">Examen</th>
                                    <th style="width: 20%">Parámetro</th>
                                    <th style="width: 22%">Indicaciones</th>
                                    @if ($lab->status === 'received')
                                        <th style="width: 18%">Resultado</th>
                                        <th style="width: 18%">Notas</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($lab->items as $item)
                                    <tr>
                                        <td><strong>{{ $item->exam_name }}</strong></td>
                                        <td>{{ $item->parameter_name ?: '—' }}</td>
                                        <td>{{ $item->indications ?: '—' }}</td>
                                        @if ($lab->status === 'received')
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
                    @endif

                    @if ($lab->observations)
                        <div class="observations">Observaciones: {{ $lab->observations }}</div>
                    @endif
                @endforeach
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
