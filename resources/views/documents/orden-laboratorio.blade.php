{{--
    Orden de laboratorio pediátrica en hoja oficio, sin fotografías.
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
                background: #f8fbfc;
                color: #263238;
                font-family:
                    DejaVu Sans,
                    sans-serif;
                font-size: 3mm;
                line-height: 1.4;
            }

            .sheet {
                position: relative;
                width: 215.9mm;
                padding-bottom: 20mm;
                background: #fff;
            }

            .header {
                position: relative;
                height: 39mm;
                overflow: hidden;
                border-bottom: 1.5mm solid #f2ca36;
                background: #dff4f5;
            }

            .header-band {
                position: absolute;
                top: 0;
                left: 0;
                width: 7mm;
                height: 39mm;
                background: #3db9c5;
            }

            .header-circle {
                position: absolute;
                border-radius: 50%;
                opacity: 0.85;
            }

            .circle-one {
                top: -9mm;
                right: 17mm;
                width: 29mm;
                height: 29mm;
                background: #f3c7d0;
            }

            .circle-two {
                right: 4mm;
                bottom: -8mm;
                width: 20mm;
                height: 20mm;
                background: #d2e9a8;
            }

            .logo-mark {
                position: absolute;
                top: 10mm;
                left: 17mm;
                width: 17mm;
                height: 17mm;
                border-radius: 5mm;
                background: #3db9c5;
            }

            .logo-mark::before,
            .logo-mark::after {
                position: absolute;
                display: block;
                background: #fff;
                content: '';
            }

            .logo-mark::before {
                top: 6.6mm;
                left: 3.3mm;
                width: 10.4mm;
                height: 3.8mm;
            }

            .logo-mark::after {
                top: 3.3mm;
                left: 6.6mm;
                width: 3.8mm;
                height: 10.4mm;
            }

            .identity {
                position: absolute;
                top: 8.5mm;
                left: 41mm;
                width: 90mm;
            }

            .doctor-name {
                color: #225f67;
                font-size: 5mm;
                font-weight: 700;
                letter-spacing: 0.15mm;
            }

            .doctor-meta {
                margin-top: 1.2mm;
                color: #51747a;
                font-size: 2.75mm;
            }

            .document-title {
                position: absolute;
                top: 11mm;
                right: 14mm;
                width: 63mm;
                color: #a64e61;
                font-size: 4.3mm;
                font-weight: 700;
                letter-spacing: 0.4mm;
                line-height: 1.35;
                text-align: right;
            }

            .document-subtitle {
                position: absolute;
                top: 27mm;
                right: 14mm;
                width: 63mm;
                color: #6d7780;
                font-size: 2.35mm;
                text-align: right;
            }

            .content {
                padding: 7mm 14mm 26mm;
            }

            .patient-card {
                margin-bottom: 5mm;
                padding: 4mm 5mm;
                border: 0.35mm solid #9ed9de;
                border-radius: 2.5mm;
                background: #f2fbfc;
            }

            .patient-name {
                margin-bottom: 2.8mm;
                padding-bottom: 1.8mm;
                border-bottom: 0.3mm solid #bee4e7;
                color: #264f54;
                font-size: 3.5mm;
                font-weight: 700;
            }

            .patient-label,
            .data-label {
                color: #348f99;
                font-size: 2.3mm;
                font-weight: 700;
                letter-spacing: 0.15mm;
                text-transform: uppercase;
            }

            .patient-table {
                width: 100%;
                border-collapse: collapse;
                table-layout: fixed;
            }

            .patient-table td {
                padding: 0.5mm 4mm 0.5mm 0;
                vertical-align: top;
            }

            .data-value {
                margin-top: 0.4mm;
                font-size: 2.85mm;
                font-weight: 700;
            }

            .diagnosis-card {
                margin-bottom: 5mm;
                padding: 3mm 4mm;
                border-left: 1.3mm solid #f0c62d;
                background: #fff9df;
                font-size: 2.8mm;
            }

            .diagnosis-card strong {
                color: #786100;
            }

            .section-heading {
                margin: 5mm 0 3mm;
                padding-bottom: 1.3mm;
                border-bottom: 0.45mm solid #efbcc7;
                color: #a64e61;
                font-size: 3.6mm;
                font-weight: 700;
                letter-spacing: 0.3mm;
            }

            .study-group {
                margin-bottom: 3mm;
                padding: 2.8mm 4mm;
                border-left: 1.2mm solid #3db9c5;
                background: #f5fbfc;
                page-break-inside: avoid;
            }

            .study-group:nth-of-type(even) {
                border-left-color: #e89bad;
                background: #fff7f9;
            }

            .group-name {
                margin-bottom: 1.5mm;
                color: #267984;
                font-size: 3mm;
                font-weight: 700;
                letter-spacing: 0.12mm;
            }

            .study-group:nth-of-type(even) .group-name {
                color: #a65366;
            }

            .study-table {
                width: 100%;
                border-collapse: collapse;
                table-layout: fixed;
            }

            .study-table td {
                width: 50%;
                padding: 0.9mm 4mm 0.9mm 0;
                vertical-align: top;
                font-size: 2.65mm;
            }

            .check {
                display: inline-block;
                margin-right: 1.4mm;
                color: #178895;
                font-family:
                    DejaVu Sans Mono,
                    monospace;
                font-size: 2.8mm;
                font-weight: 700;
            }

            .parameter {
                color: #65757b;
                font-size: 2.35mm;
            }

            .observations {
                min-height: 14mm;
                padding: 3.2mm 4mm;
                border: 0.35mm dashed #c5afd5;
                border-radius: 2mm;
                background: #fbf7fd;
                color: #574865;
                font-size: 2.7mm;
            }

            .signature {
                width: 65mm;
                margin: 15mm 0 0 auto;
                color: #526269;
                font-size: 2.55mm;
                text-align: center;
            }

            .signature-line {
                margin-bottom: 1.2mm;
                border-top: 0.35mm solid #7c8c92;
            }

            .footer {
                position: fixed;
                right: 0;
                bottom: 0;
                left: 0;
                height: 17mm;
                border-top: 1.3mm solid #f0c62d;
                background: #dcefbf;
                color: #3e5338;
            }

            .footer-table {
                width: 100%;
                border-collapse: collapse;
                table-layout: fixed;
            }

            .footer-table td {
                width: 50%;
                padding: 4.2mm 14mm 0;
                font-size: 2.5mm;
                vertical-align: middle;
            }

            .footer-contact {
                font-weight: 700;
                text-align: right;
            }
        </style>
    </head>
    <body>
        <main class="sheet" aria-label="Orden de laboratorio pediátrica">
            <header class="header">
                <span class="header-band"></span>
                <span class="header-circle circle-one"></span>
                <span class="header-circle circle-two"></span>
                <span class="logo-mark" aria-hidden="true"></span>

                <div class="identity">
                    <div class="doctor-name">{{ $doc->doctorName }}</div>
                    <div class="doctor-meta">{{ $doc->specialty }}</div>
                </div>

                <div class="document-title">ORDEN DE LABORATORIO</div>
                <div class="document-subtitle">Solicitud de estudios clínicos</div>
            </header>

            <section class="content">
                <div class="patient-card">
                    <div class="patient-name">
                        <span class="patient-label">Paciente</span>
                        · {{ $doc->patientName }}
                    </div>
                    <table class="patient-table" aria-label="Datos del paciente">
                        <tr>
                            <td>
                                <div class="data-label">Fecha</div>
                                <div class="data-value">{{ $doc->dateText }}</div>
                            </td>
                            <td>
                                <div class="data-label">Edad</div>
                                <div class="data-value">{{ $doc->ageText }}</div>
                            </td>
                            <td>
                                <div class="data-label">Documento</div>
                                <div class="data-value">Orden médica</div>
                            </td>
                        </tr>
                    </table>
                </div>

                @if ($doc->diagnosis)
                    <div class="diagnosis-card">
                        <strong>Diagnóstico presuntivo:</strong>
                        {{ $doc->diagnosis }}
                    </div>
                @endif

                <h1 class="section-heading">ESTUDIOS SOLICITADOS</h1>

                @php
                    $studies = collect($doc->items)->groupBy(fn ($study) => $study->category);
                    $preferredOrder = [
                        'Hematología',
                        'Química Sanguínea',
                        'Uroanálisis',
                        'Microbiología',
                        'Parasitología',
                        'Inmunología / Serología',
                        'Imagenología',
                        'Otros',
                    ];
                    $groupOrder = collect($preferredOrder)
                        ->filter(fn ($category) => $studies->has($category))
                        ->concat($studies->keys()->reject(fn ($category) => in_array($category, $preferredOrder, true)));
                @endphp

                @foreach ($groupOrder as $group)
                    @php($groupStudies = $studies->get($group) ?? collect())
                    @if ($groupStudies->isEmpty())
                        @continue
                    @endif

                    <div class="study-group">
                        <div class="group-name">{{ $group }}</div>
                        <table class="study-table">
                            @foreach ($groupStudies->chunk(2) as $row)
                                <tr>
                                    @foreach ($row as $study)
                                        <td>
                                            <span class="check">[x]</span>
                                            {{ $study->exam_name }}
                                            @if ($study->parameter_name)
                                                <span class="parameter">({{ $study->parameter_name }})</span>
                                            @endif
                                        </td>
                                    @endforeach

                                    @if ($row->count() === 1)
                                        <td></td>
                                    @endif
                                </tr>
                            @endforeach
                        </table>
                    </div>
                @endforeach

                @if ($doc->observations)
                    <h2 class="section-heading">OBSERVACIONES</h2>
                    <div class="observations">{{ $doc->observations }}</div>
                @endif

                <div class="signature">
                    <div class="signature-line"></div>
                    {{ $doc->doctorName }}
                </div>
            </section>

            <footer class="footer">
                <table class="footer-table">
                    <tr>
                        <td>{{ $doc->specialty }}</td>
                        <td class="footer-contact">
                            @if ($doc->phone)
                                Contacto · {{ $doc->phone }}
                            @endif
                        </td>
                    </tr>
                </table>
            </footer>
        </main>
    </body>
</html>
