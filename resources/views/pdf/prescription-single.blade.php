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

            <div style="font-size: 13px; font-weight: bold; color: #1a1a1a; margin-bottom: 12px">RECETA MÉDICA</div>

            <h3 class="section-title">
                {{ $prescription->reason ?: 'Sin diagnóstico especificado' }}
            </h3>

            @if ($prescription->items->isNotEmpty())
                <table class="data-table">
                    <thead>
                        <tr>
                            <th style="width: 24%">Medicamento</th>
                            <th style="width: 14%">Dosis</th>
                            <th style="width: 12%">Vía</th>
                            <th style="width: 14%">Cantidad</th>
                            <th style="width: 18%">Frecuencia</th>
                            <th style="width: 18%">Duración</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($prescription->items as $item)
                            <tr>
                                <td><strong>{{ $item->medication_name }}</strong></td>
                                <td>{{ $item->dose ?: '—' }}</td>
                                <td>{{ $item->administration_route ?: '—' }}</td>
                                <td>{{ $item->quantity ?: '—' }}</td>
                                <td>{{ $item->frequency ?: '—' }}</td>
                                <td>{{ $item->duration ?: '—' }}</td>
                            </tr>
                            @if ($item->instructions)
                                <tr class="instructions-row">
                                    <td colspan="6">{{ $item->instructions }}</td>
                                </tr>
                            @endif
                        @endforeach
                    </tbody>
                </table>
            @else
                <p style="color: #888; font-style: italic">Sin medicamentos registrados.</p>
            @endif

            @if ($prescription->observations)
                <div class="observations">Observaciones: {{ $prescription->observations }}</div>
            @endif

            <div class="footer">
                {{-- Aquí irá la firma digitalizada --}}
                <div style="height: 60px; margin-bottom: 5px">
                    @if ($consultation->doctor?->digital_signature_path)
                        <img
                            src="{{ public_path('storage/' . $consultation->doctor->digital_signature_path) }}"
                            alt="Firma del doctor"
                            style="max-height: 60px; max-width: 200px; object-fit: contain"
                        />
                    @else
                        <div style="height: 60px; display: inline-block"></div>
                    @endif
                </div>
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
