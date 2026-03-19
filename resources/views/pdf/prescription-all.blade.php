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
                RECETA MÉDICA
                <span style="font-size: 11px; font-weight: normal; color: #555">
                    ({{ $consultation->prescriptions->count() }}
                    {{ $consultation->prescriptions->count() === 1 ? 'receta' : 'recetas' }})
                </span>
            </div>

            @if ($consultation->prescriptions->isEmpty())
                <p style="color: #888; font-style: italic">Sin recetas en esta consulta.</p>
            @else
                @foreach ($consultation->prescriptions as $i => $prescription)
                    @if ($i > 0)
                        <hr class="separator" />
                    @endif

                    <h3 class="section-title">
                        {{ $i + 1 }}. {{ $prescription->reason ?: 'Sin diagnóstico especificado' }}
                    </h3>

                    @if ($prescription->items->isNotEmpty())
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th style="width: 26%">Medicamento</th>
                                    <th style="width: 18%">Dosis</th>
                                    <th style="width: 16%">Cantidad</th>
                                    <th style="width: 20%">Frecuencia</th>
                                    <th style="width: 20%">Duración</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($prescription->items as $item)
                                    <tr>
                                        <td><strong>{{ $item->medication_name }}</strong></td>
                                        <td>{{ $item->dose ?: '—' }}</td>
                                        <td>{{ $item->quantity ?: '—' }}</td>
                                        <td>{{ $item->frequency ?: '—' }}</td>
                                        <td>{{ $item->duration ?: '—' }}</td>
                                    </tr>
                                    @if ($item->instructions)
                                        <tr class="instructions-row">
                                            <td colspan="5">{{ $item->instructions }}</td>
                                        </tr>
                                    @endif
                                @endforeach
                            </tbody>
                        </table>
                    @endif

                    @if ($prescription->observations)
                        <div class="observations">Observaciones: {{ $prescription->observations }}</div>
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
