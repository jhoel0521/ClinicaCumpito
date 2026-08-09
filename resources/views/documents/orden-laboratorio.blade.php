{{--
    Orden de laboratorio en hoja oficio (215,9 × 330,2 mm).
    Recibe: $doc (App\DTOs\ClinicalDocumentDTO)
--}}
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
                size: 215.9mm 330.2mm;
                margin: 0;
            }

            body {
                font-family: Arial, Helvetica, sans-serif;
                color: #101010;
                background: #fff;
                font-size: 3.4mm;
                line-height: 1.45;
            }

            /* Encabezado institucional repetido en cada página */
            .page-header {
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                background: #f7d2d7;
                padding: 5mm 12mm 4mm;
                border-bottom: 1.2mm solid #f4df00;
            }

            .page-header .doctor {
                font-size: 4.6mm;
                font-weight: 800;
            }

            .page-header .meta {
                font-size: 3mm;
                color: #333;
            }

            .page-header .title {
                margin-top: 1.5mm;
                font-size: 4.4mm;
                font-weight: 800;
                letter-spacing: 0.3mm;
                color: #a94f50;
            }

            .content {
                padding: 26mm 14mm 22mm;
            }

            .patient-box {
                border: 0.3mm solid #d9d9d9;
                border-radius: 2mm;
                padding: 3.5mm 4mm;
                margin-bottom: 4mm;
            }

            .patient-box .row {
                display: flex;
                gap: 6mm;
                margin-bottom: 1.5mm;
            }

            .patient-box .row:last-child {
                margin-bottom: 0;
            }

            .patient-box .label {
                font-weight: 800;
                text-transform: uppercase;
                font-size: 2.9mm;
                letter-spacing: 0.1mm;
            }

            .patient-box .value {
                font-weight: 700;
            }

            .section-title {
                font-size: 3.4mm;
                font-weight: 800;
                text-transform: uppercase;
                letter-spacing: 0.2mm;
                color: #a94f50;
                margin: 4mm 0 2mm;
                border-bottom: 0.4mm solid #f7d2d7;
                padding-bottom: 0.8mm;
            }

            .study-group {
                margin-bottom: 3mm;
            }

            .study-group .group-name {
                font-weight: 800;
                font-size: 3.2mm;
                color: #555;
            }

            .study-group ul {
                list-style: none;
                margin: 1mm 0 0 3mm;
            }

            .study-group li {
                padding: 0.8mm 0 0.8mm 4mm;
                position: relative;
                break-inside: avoid;
            }

            .study-group li::before {
                content: '☐';
                position: absolute;
                left: 0;
                top: 0.8mm;
                font-size: 3.2mm;
            }

            .study-group li .param {
                color: #555;
                font-size: 3mm;
            }

            .observations {
                border: 0.3mm dashed #d9d9d9;
                border-radius: 2mm;
                padding: 3mm 4mm;
                min-height: 12mm;
                margin-top: 3mm;
            }

            .signature {
                margin-top: 14mm;
                width: 70mm;
                text-align: center;
                font-size: 3mm;
                color: #333;
            }

            .signature .line {
                border-bottom: 0.4mm solid #333;
                margin-bottom: 1mm;
            }

            .page-footer {
                position: fixed;
                bottom: 0;
                left: 0;
                right: 0;
                background: #cceaa3;
                padding: 2.5mm 12mm;
                font-size: 2.8mm;
                color: #333;
                display: flex;
                justify-content: space-between;
            }
        </style>
    </head>
    <body>
        <header class="page-header">
            <div class="doctor">{{ $doc->doctorName }}</div>
            <div class="meta">{{ $doc->specialty }}@if ($doc->phone)· {{ $doc->phone }}
            @endif</div>
            <div class="title">ORDEN DE LABORATORIO</div>
        </header>

        <main class="content">
            <div class="patient-box">
                <div class="row">
                    <span class="label">Paciente:</span>
                    <span class="value">{{ $doc->patientName }}</span>
                </div>
                <div class="row">
                    <span class="label">Fecha:</span>
                    <span class="value">{{ $doc->dateText }}</span>
                    <span class="label">Edad:</span>
                    <span class="value">{{ $doc->ageText }}</span>
                </div>
                <div class="row">
                    <span class="label">Consulta:</span>
                    <span class="value">{{ $doc->dateText }}</span>
                </div>
            </div>

            @if ($doc->diagnosis)
                <div class="section-title">Diagnóstico / Motivo</div>
                <p>{{ $doc->diagnosis }}</p>
            @endif

            <div class="section-title">Estudios solicitados</div>

            @php
                $groupOrder = ["Hematología", "Química sanguínea", "Orina", "Heces", "Serología", "Microbiología", "Hormonas", "Otros"];
                $studies = collect($doc->items)->groupBy(fn ($s) => $s->category);
            @endphp

            @foreach ($groupOrder as $group)
                @php($groupStudies = $studies->get($group) ?? collect())
                @if ($groupStudies->isEmpty())
                    @continue
                @endif

                <div class="study-group">
                    <div class="group-name">{{ $group }}</div>
                    <ul>
                        @foreach ($groupStudies as $study)
                            <li>
                                {{ $study->exam_name }}
                                @if ($study->parameter_name)
                                    <span class="param">({{ $study->parameter_name }})</span>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endforeach

            @if ($doc->observations)
                <div class="section-title">Observaciones</div>
                <div class="observations">{{ $doc->observations }}</div>
            @endif

            <div class="signature">
                <div class="line"></div>
                {{ $doc->doctorName }}
                @if ($doc->phone)
                    · {{ $doc->phone }}
                @endif
            </div>
        </main>

        <footer class="page-footer">
            <span>{{ $doc->doctorName }} · {{ $doc->specialty }}</span>
            <span>{{ $doc->phone ?? "" }}</span>
        </footer>
    </body>
</html>
