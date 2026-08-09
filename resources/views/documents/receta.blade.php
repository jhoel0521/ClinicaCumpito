{{--
    Receta en media hoja oficio (215,9 × 165,1 mm) con los componentes
    gráficos reales del recetario (public/images/pdf/).
    Recibe: $doc (App\DTOs\ClinicalDocumentDTO)
--}}
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }

        @page {
            size: 215.9mm 165.1mm;
            margin: 0;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            color: #101010;
            background: #fff;
        }

        .sheet {
            position: relative;
            width: 215.9mm;
            height: 165.1mm;
            overflow: hidden;
        }

        /* ── Encabezado (14 % de la altura) ── */
        .header {
            position: relative;
            height: 23mm;
            overflow: hidden;
            background: #f7d2d7;
        }

        .confetti {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            object-fit: fill;
        }

        .brand {
            position: absolute;
            inset: 9.2mm 24mm auto 24mm;
            text-align: center;
            z-index: 2;
        }

        .brand h1 {
            font-size: 5.6mm;
            line-height: 1;
            font-weight: 800;
            letter-spacing: .2mm;
        }

        .brand p {
            margin-top: 1.1mm;
            font-size: 3.4mm;
            line-height: 1;
        }

        .medical-icon { position: absolute; left: 9%; top: 6.5mm; width: 10mm; height: 10mm; z-index: 2; }
        .abc-blocks { position: absolute; left: 42%; top: 2.2mm; width: 13mm; height: 10.6mm; z-index: 2; }
        .header-bear { position: absolute; left: 57%; top: 2.4mm; width: 9mm; height: 7.7mm; z-index: 2; }
        .monkey { position: absolute; right: 0; top: -1mm; width: 22mm; height: 15.7mm; z-index: 2; }

        /* ── Separador ── */
        .dash-line {
            height: 2.8mm;
            background: repeating-linear-gradient(
                90deg,
                transparent 0 4.5mm,
                #f4df00 4.5mm 12mm,
                transparent 12mm 17mm
            );
            background-size: auto 1.1mm;
            background-position: left center;
            background-repeat: repeat-x;
        }

        /* ── Datos del paciente ── */
        .patient-data {
            position: relative;
            z-index: 3;
            padding: 5mm 17mm 3mm;
            font-size: 3.9mm;
            font-weight: 700;
        }

        .name-row {
            display: flex;
            align-items: flex-end;
            gap: 2mm;
            margin-bottom: 3.2mm;
            white-space: nowrap;
        }

        .editable-line {
            flex: 1;
            border-bottom: .6mm dotted #111;
            padding: 0 1mm .4mm;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .data-row {
            display: grid;
            grid-template-columns: 1.2fr 1fr;
            column-gap: 14mm;
            margin-bottom: 2.6mm;
        }

        .field { display: flex; align-items: baseline; gap: 2mm; white-space: nowrap; }
        .field-value { flex: 1; border-bottom: .3mm solid #999; padding: 0 1mm .3mm; }

        /* ── Marca de agua ── */
        .watermark {
            position: absolute;
            z-index: 1;
            left: 50%;
            top: 62mm;
            transform: translateX(-50%);
            width: 118mm;
            height: 106mm;
            opacity: .1;
        }

        /* ── Área de escritura (medicamentos) ── */
        .prescription-area {
            position: relative;
            z-index: 2;
            padding: 1.5mm 17mm 0;
            font-size: 3.2mm;
            line-height: 1.4;
        }

        .diagnosis {
            font-size: 3.4mm;
            font-weight: 800;
            margin-bottom: 1.5mm;
        }

        .medication {
            margin-bottom: 1.8mm;
            padding-bottom: 1.2mm;
            border-bottom: .2mm solid #e5e5e5;
            break-inside: avoid;
        }

        .medication:last-child { border-bottom: 0; }

        .medication .name { font-weight: 800; font-size: 3.4mm; }
        .medication .detail { color: #333; }
        .medication .instructions { font-style: italic; color: #444; }

        /* ── Pie (15 % de la altura) ── */
        .footer {
            position: absolute;
            z-index: 4;
            left: 0;
            right: 0;
            bottom: 0;
            height: 24mm;
            background: #cceaa3;
        }

        .animals {
            position: absolute;
            left: 0;
            bottom: 0;
            display: flex;
            align-items: flex-end;
            gap: 1mm;
        }

        .animals img { display: block; }

        .animal-bird { width: 15mm; height: 15mm; }
        .animal-giraffe { width: 10mm; height: 16mm; }
        .animal-lion { width: 13mm; height: 13mm; }
        .animal-zebra { width: 9.5mm; height: 15mm; }

        .bee { position: absolute; right: 1.5mm; top: -8mm; width: 18mm; height: 18.6mm; }

        .footer-copy {
            position: absolute;
            left: 52mm;
            right: 26mm;
            top: 1.2mm;
            text-align: center;
        }

        .slogan {
            font-family: "Brush Script MT", "Segoe Script", cursive;
            font-size: 5mm;
            line-height: 1;
            white-space: nowrap;
        }

        .appointment {
            margin-top: 1mm;
            font-size: 3mm;
            font-weight: 800;
        }

        .phone {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 1.2mm;
            margin-top: .3mm;
            font-size: 4.6mm;
            font-weight: 800;
            line-height: 1;
        }

        .heart-img {
            position: absolute;
            right: 2.5mm;
            bottom: 1.8mm;
            width: 7mm;
            height: 7mm;
        }

        .signature {
            position: absolute;
            right: 17mm;
            bottom: 27mm;
            z-index: 5;
            text-align: center;
            font-size: 2.8mm;
            color: #333;
        }

        .signature .line {
            width: 45mm;
            border-bottom: .4mm solid #333;
            margin-bottom: .8mm;
        }
    </style>
</head>
<body>
    <main class="sheet" aria-label="Receta de la Dra. Karen Zaconeta">
        <header class="header">
            <img class="confetti" src="{{ asset('images/pdf/bolitas.png') }}" alt="" aria-hidden="true" />

            <img class="medical-icon" src="{{ asset('images/pdf/corazon-azul.png') }}" alt="" aria-hidden="true" />
            <img class="abc-blocks" src="{{ asset('images/pdf/abc.png') }}" alt="" aria-hidden="true" />
            <img class="header-bear" src="{{ asset('images/pdf/oso-header.png') }}" alt="" aria-hidden="true" />
            <img class="monkey" src="{{ asset('images/pdf/mono.png') }}" alt="" aria-hidden="true" />

            <div class="brand">
                <h1>DRA. KAREN ZACONETA</h1>
                <p>{{ $doc->specialty }}</p>
            </div>
        </header>

        <div class="dash-line" aria-hidden="true"></div>

        <section class="patient-data" aria-label="Datos del paciente">
            <div class="name-row">
                <span>NOMBRE DE PACIENTE</span>
                <span class="editable-line">{{ $doc->patientName }}</span>
            </div>
            <div class="data-row">
                <div class="field"><span>Fecha:</span><span class="field-value">{{ $doc->dateText }}</span></div>
                <div class="field"><span>Edad:</span><span class="field-value">{{ $doc->ageText }}</span></div>
            </div>
            <div class="data-row">
                <div class="field"><span>Peso:</span><span class="field-value">{{ $doc->weight ?? '—' }}</span></div>
                <div class="field"><span>Talla:</span><span class="field-value">{{ $doc->height ?? '—' }}</span></div>
            </div>
        </section>

        <img class="watermark" src="{{ asset('images/pdf/oso-agua.png') }}" alt="" aria-hidden="true" />

        <section class="prescription-area" aria-label="Medicamentos e indicaciones">
            @if ($doc->diagnosis)
                <div class="diagnosis">Diagnóstico: {{ $doc->diagnosis }}</div>
            @endif

            @foreach ($doc->items as $med)
                <div class="medication">
                    <div class="name">{{ $med->medication_name }}</div>
                    <div class="detail">
                        @if ($med->dose)
                            <span>{{ $med->dose }}</span>
                        @endif
                        @if ($med->administration_route)
                            <span> · Vía {{ $med->administration_route }}</span>
                        @endif
                        @if ($med->frequency)
                            <span> · {{ $med->frequency }}</span>
                        @endif
                        @if ($med->duration)
                            <span> · {{ $med->duration }}</span>
                        @endif
                    </div>
                    @if ($med->instructions)
                        <div class="instructions">{{ $med->instructions }}</div>
                    @endif
                </div>
            @endforeach
        </section>

        <div class="signature">
            <div class="line"></div>
            {{ $doc->doctorName }}
            @if ($doc->phone)
                · {{ $doc->phone }}
            @endif
        </div>

        <footer class="footer">
            <div class="animals">
                <img class="animal-bird" src="{{ asset('images/pdf/ave-rosada.png') }}" alt="" aria-hidden="true" />
                <img class="animal-giraffe" src="{{ asset('images/pdf/girafa.png') }}" alt="" aria-hidden="true" />
                <img class="animal-lion" src="{{ asset('images/pdf/leon.png') }}" alt="" aria-hidden="true" />
                <img class="animal-zebra" src="{{ asset('images/pdf/zebra.png') }}" alt="" aria-hidden="true" />
            </div>

            <div class="footer-copy">
                <p class="slogan">Tu bebé en las mejores manos…</p>
                <div class="appointment">AGENDA TU CITA</div>
                <div class="phone">
                    <svg width="6mm" height="6mm" viewBox="0 0 64 64" aria-hidden="true">
                        <circle cx="32" cy="32" r="27" fill="none" stroke="#fff" stroke-width="4"/>
                        <path d="M18 49l3-10c-8-15 3-31 19-29 17 2 23 22 11 34-7 7-18 8-27 3z" fill="none" stroke="#fff" stroke-width="3"/>
                        <path d="M25 21c2 13 8 19 19 20l4-6-8-4-3 4c-4-2-7-5-9-9l4-3-4-7z" fill="#fff"/>
                    </svg>
                    <span>{{ $doc->phone }}</span>
                </div>
            </div>

            <img class="bee" src="{{ asset('images/pdf/abeja.png') }}" alt="" aria-hidden="true" />
            <img class="heart-img" src="{{ asset('images/pdf/corazon-amarillo.png') }}" alt="" aria-hidden="true" />
        </footer>
    </main>
</body>
</html>
