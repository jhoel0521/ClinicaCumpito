{{--
    Receta sobre el arte original aprobado en Canva (129,91 × 210,08 mm).
    Recibe: $doc (App\DTOs\ClinicalDocumentDTO)
--}}
@php
    $background = 'data:image/jpeg;base64,' . base64_encode(file_get_contents(public_path('images/pdf/recetario-base.jpg')));
@endphp

<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="utf-8" />
        <style>
            * {
                box-sizing: border-box;
                margin: 0;
                padding: 0;
            }

            @page {
                size: 129.91mm 210.08mm;
                margin: 0;
            }

            body {
                background: #fff;
                color: #101010;
                font-family:
                    DejaVu Sans,
                    sans-serif;
            }

            .sheet {
                position: relative;
                width: 129.91mm;
                height: 210.08mm;
                overflow: hidden;
            }

            .artwork {
                position: absolute;
                z-index: 0;
                top: 0;
                left: 0;
                width: 129.91mm;
                height: 210.08mm;
            }

            .clinical-value {
                position: absolute;
                z-index: 2;
                overflow: hidden;
                background: #fff;
                font-size: 3.35mm;
                font-weight: 700;
                line-height: 1.25;
                white-space: nowrap;
            }

            .patient-name {
                top: 31.2mm;
                left: 61mm;
                width: 59.5mm;
                height: 5.8mm;
                padding: 0.65mm 0.8mm 0;
                border-bottom: 0.45mm dotted #111;
                font-size: 2.8mm;
            }

            .date {
                top: 41mm;
                left: 31.5mm;
                width: 34mm;
                height: 6mm;
                padding: 0.8mm 0 0;
            }

            .age {
                top: 41mm;
                left: 90mm;
                width: 31mm;
                height: 6mm;
                padding: 0.8mm 0 0;
            }

            .weight {
                top: 51.3mm;
                left: 25mm;
                width: 36mm;
                height: 6mm;
                padding: 0.8mm 0 0;
            }

            .height {
                top: 51.3mm;
                left: 90mm;
                width: 31mm;
                height: 6mm;
                padding: 0.8mm 0 0;
            }

            .prescription-area {
                position: absolute;
                z-index: 2;
                top: 61mm;
                right: 10.5mm;
                bottom: 25mm;
                left: 10.5mm;
                overflow: hidden;
                font-size: 3mm;
                line-height: 1.38;
            }

            .diagnosis {
                margin-bottom: 2mm;
                font-size: 3.15mm;
                font-weight: 700;
            }

            .medication {
                margin-bottom: 2.2mm;
                padding-bottom: 1.5mm;
                border-bottom: 0.2mm solid rgba(0, 0, 0, 0.1);
                page-break-inside: avoid;
            }

            .medication-name {
                font-size: 3.15mm;
                font-weight: 700;
            }

            .medication-detail {
                color: #252525;
            }

            .instructions {
                color: #353535;
                font-style: italic;
            }

            .observations {
                margin-top: 1mm;
                font-style: italic;
            }
        </style>
    </head>
    <body>
        <main class="sheet" aria-label="Receta de la Dra. Karen Zaconeta">
            <img class="artwork" src="{{ $background }}" alt="" aria-hidden="true" />

            <div class="clinical-value patient-name">{{ $doc->patientName }}</div>
            <div class="clinical-value date">{{ $doc->dateText }}</div>
            <div class="clinical-value age">{{ $doc->ageText }}</div>
            <div class="clinical-value weight">{{ $doc->weight ?? '—' }}</div>
            <div class="clinical-value height">{{ $doc->height ?? '—' }}</div>

            <section class="prescription-area" aria-label="Medicamentos e indicaciones">
                @if ($doc->diagnosis)
                    <div class="diagnosis">Diagnóstico: {{ $doc->diagnosis }}</div>
                @endif

                @foreach ($doc->items as $med)
                    <div class="medication">
                        <div class="medication-name">{{ $med->medication_name }}</div>
                        <div class="medication-detail">
                            @if ($med->dose)
                                <span>{{ $med->dose }}</span>
                            @endif

                            @if ($med->administration_route)
                                <span>· Vía {{ $med->administration_route }}</span>
                            @endif

                            @if ($med->frequency)
                                <span>· {{ $med->frequency }}</span>
                            @endif

                            @if ($med->duration)
                                <span>· {{ $med->duration }}</span>
                            @endif
                        </div>
                        @if ($med->instructions)
                            <div class="instructions">{{ $med->instructions }}</div>
                        @endif
                    </div>
                @endforeach

                @if ($doc->observations)
                    <div class="observations">{{ $doc->observations }}</div>
                @endif
            </section>
        </main>
    </body>
</html>
