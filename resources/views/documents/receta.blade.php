{{--
    Receta en media hoja oficio (215,9 × 165,1 mm).
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
            }

            .brand {
                position: absolute;
                inset: 9.2mm 20mm auto 20mm;
                text-align: center;
            }

            .brand h1 {
                font-size: 5.6mm;
                line-height: 1;
                font-weight: 800;
                letter-spacing: 0.2mm;
            }

            .brand p {
                margin-top: 1.1mm;
                font-size: 3.4mm;
                line-height: 1;
            }

            .medical-icon {
                position: absolute;
                left: 10%;
                top: 6.5mm;
                width: 10mm;
                height: 10mm;
            }
            .abc-blocks {
                position: absolute;
                left: 43%;
                top: 2.2mm;
                width: 13mm;
                height: 9mm;
            }
            .header-bear {
                position: absolute;
                left: 58%;
                top: 2.2mm;
                width: 9mm;
                height: 9mm;
            }
            .monkey {
                position: absolute;
                right: 0;
                top: -1mm;
                width: 22mm;
                height: 20mm;
            }

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
                border-bottom: 0.6mm dotted #111;
                padding: 0 1mm 0.4mm;
                overflow: hidden;
                text-overflow: ellipsis;
            }

            .data-row {
                display: grid;
                grid-template-columns: 1.2fr 1fr;
                column-gap: 14mm;
                margin-bottom: 2.6mm;
            }

            .field {
                display: flex;
                align-items: baseline;
                gap: 2mm;
                white-space: nowrap;
            }
            .field-value {
                flex: 1;
                border-bottom: 0.3mm solid #999;
                padding: 0 1mm 0.3mm;
            }

            /* ── Marca de agua ── */
            .watermark {
                position: absolute;
                z-index: 1;
                left: 50%;
                top: 62mm;
                transform: translateX(-50%);
                width: 120mm;
                height: 66mm;
                opacity: 0.1;
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
                border-bottom: 0.2mm solid #e5e5e5;
                break-inside: avoid;
            }

            .medication:last-child {
                border-bottom: 0;
            }

            .medication .name {
                font-weight: 800;
                font-size: 3.4mm;
            }

            .medication .detail {
                color: #333;
            }

            .medication .instructions {
                font-style: italic;
                color: #444;
            }

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
                width: 54mm;
                height: 32mm;
            }

            .bee {
                position: absolute;
                right: 1.5mm;
                top: -9mm;
                width: 20mm;
                height: 18mm;
            }

            .footer-copy {
                position: absolute;
                left: 52mm;
                right: 24mm;
                top: 1.2mm;
                text-align: center;
            }

            .slogan {
                font-family: 'Brush Script MT', 'Segoe Script', cursive;
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
                margin-top: 0.3mm;
                font-size: 4.6mm;
                font-weight: 800;
                line-height: 1;
            }

            .heart {
                position: absolute;
                right: 3mm;
                bottom: 2mm;
                width: 7mm;
                height: 7mm;
                background: #f4df00;
                clip-path: polygon(
                    50% 18%,
                    62% 5%,
                    78% 3%,
                    94% 15%,
                    98% 33%,
                    92% 50%,
                    50% 94%,
                    8% 50%,
                    2% 33%,
                    6% 15%,
                    22% 3%,
                    38% 5%
                );
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
                border-bottom: 0.4mm solid #333;
                margin-bottom: 0.8mm;
            }
        </style>
    </head>
    <body>
        <main class="sheet" aria-label="Receta de la Dra. Karen Zaconeta">
            <header class="header">
                <svg class="confetti" viewBox="0 0 1000 220" preserveAspectRatio="none" aria-hidden="true">
                    <g opacity=".92">
                        <circle cx="24" cy="18" r="11" fill="#d9b59d" />
                        <circle cx="82" cy="38" r="15" fill="#b9d48d" />
                        <circle cx="146" cy="17" r="21" fill="#dfc1aa" />
                        <circle cx="220" cy="48" r="24" fill="#d4b49c" />
                        <circle cx="288" cy="22" r="13" fill="#f9df30" />
                        <circle cx="330" cy="41" r="18" fill="#b9d48d" />
                        <circle cx="390" cy="15" r="9" fill="#e3c0a9" />
                        <circle cx="515" cy="26" r="18" fill="#d3b59d" />
                        <circle cx="582" cy="55" r="25" fill="#cfad96" />
                        <circle cx="657" cy="23" r="22" fill="#b8cf8d" />
                        <circle cx="724" cy="59" r="9" fill="#f7dd2d" />
                        <circle cx="802" cy="25" r="15" fill="#d6b59d" />
                        <circle cx="880" cy="48" r="16" fill="#b6cf8c" />
                        <circle cx="948" cy="20" r="10" fill="#d5b29b" />
                        <circle cx="49" cy="120" r="23" fill="#dcc0aa" />
                        <circle cx="111" cy="155" r="14" fill="#f7dd2d" />
                        <circle cx="232" cy="116" r="8" fill="#f5db2b" />
                        <circle cx="350" cy="135" r="19" fill="#d8b49a" />
                        <circle cx="705" cy="125" r="12" fill="#f6dc2c" />
                        <circle cx="840" cy="109" r="21" fill="#d8b49a" />
                        <circle cx="928" cy="155" r="15" fill="#f7dd2d" />
                    </g>
                </svg>

                <svg class="medical-icon" viewBox="0 0 100 100" aria-label="Corazón azul con cruz blanca">
                    <path d="M50 89 14 56C-12 31 22 1 50 27 78 1 112 31 86 56Z" fill="#38c9df" />
                    <path d="M43 39h14v13h13v14H57v13H43V66H30V52h13z" fill="#fff" />
                </svg>

                <svg class="abc-blocks" viewBox="0 0 150 100" aria-label="Bloques infantiles con letras ABC">
                    <g stroke="#9f6549" stroke-width="3">
                        <rect x="9" y="40" width="43" height="43" rx="4" fill="#e8c7a1" transform="rotate(-5 30 61)" />
                        <rect x="53" y="16" width="43" height="43" rx="4" fill="#e6b58d" transform="rotate(3 74 37)" />
                        <rect x="96" y="42" width="43" height="43" rx="4" fill="#d7a781" transform="rotate(6 117 63)" />
                    </g>
                    <text
                        x="30"
                        y="70"
                        text-anchor="middle"
                        font-size="29"
                        font-family="Arial"
                        font-weight="800"
                        fill="#a94f50"
                    >
                        A
                    </text>
                    <text
                        x="75"
                        y="48"
                        text-anchor="middle"
                        font-size="29"
                        font-family="Arial"
                        font-weight="800"
                        fill="#7b6c43"
                    >
                        B
                    </text>
                    <text
                        x="117"
                        y="73"
                        text-anchor="middle"
                        font-size="29"
                        font-family="Arial"
                        font-weight="800"
                        fill="#9e4d50"
                    >
                        C
                    </text>
                </svg>

                <svg class="header-bear" viewBox="0 0 100 100" aria-label="Osito pequeño">
                    <circle cx="28" cy="29" r="17" fill="#bf7548" />
                    <circle cx="72" cy="29" r="17" fill="#bf7548" />
                    <circle cx="50" cy="47" r="35" fill="#d99660" />
                    <circle cx="39" cy="43" r="4" fill="#24170f" />
                    <circle cx="61" cy="43" r="4" fill="#24170f" />
                    <ellipse cx="50" cy="58" rx="15" ry="12" fill="#f1c599" />
                    <ellipse cx="50" cy="54" rx="5" ry="4" fill="#3d2417" />
                    <path d="M44 62q6 7 12 0" fill="none" stroke="#3d2417" stroke-width="2.5" stroke-linecap="round" />
                    <path d="M31 78q19-13 38 0v20H31z" fill="#d99660" />
                    <path d="M24 39q-10-10-18 0 8 14 19 7" fill="#ef6578" />
                </svg>

                <div class="brand">
                    <h1>DRA. KAREN ZACONETA</h1>
                    <p>{{ $doc->specialty }}</p>
                </div>

                <svg class="monkey" viewBox="0 0 150 140" aria-hidden="true">
                    <path d="M123-2c-8 17-16 29-27 40" fill="none" stroke="#55a640" stroke-width="5" />
                    <path d="M116 1c13 4 24 12 31 23" fill="none" stroke="#55a640" stroke-width="4" />
                    <circle cx="80" cy="50" r="28" fill="#a8643d" />
                    <circle cx="58" cy="43" r="11" fill="#d49368" />
                    <circle cx="102" cy="43" r="11" fill="#d49368" />
                    <ellipse cx="80" cy="55" rx="20" ry="18" fill="#f3bd88" />
                    <circle cx="72" cy="50" r="3" />
                    <circle cx="88" cy="50" r="3" />
                    <path d="M74 62q6 6 12 0" fill="none" stroke="#4b2a20" stroke-width="2" />
                    <path
                        d="M82 77q-3 21-24 24M90 76q16 8 18 26"
                        fill="none"
                        stroke="#a8643d"
                        stroke-width="12"
                        stroke-linecap="round"
                    />
                    <path
                        d="M58 99q-9 21 6 34M108 100q12 18 2 34"
                        fill="none"
                        stroke="#a8643d"
                        stroke-width="10"
                        stroke-linecap="round"
                    />
                </svg>
            </header>

            <div class="dash-line" aria-hidden="true"></div>

            <section class="patient-data" aria-label="Datos del paciente">
                <div class="name-row">
                    <span>NOMBRE DE PACIENTE</span>
                    <span class="editable-line">{{ $doc->patientName }}</span>
                </div>
                <div class="data-row">
                    <div class="field">
                        <span>Fecha:</span>
                        <span class="field-value">{{ $doc->dateText }}</span>
                    </div>
                    <div class="field">
                        <span>Edad:</span>
                        <span class="field-value">{{ $doc->ageText }}</span>
                    </div>
                </div>
                <div class="data-row">
                    <div class="field">
                        <span>Peso:</span>
                        <span class="field-value">{{ $doc->weight ?? '—' }}</span>
                    </div>
                    <div class="field">
                        <span>Talla:</span>
                        <span class="field-value">{{ $doc->height ?? '—' }}</span>
                    </div>
                </div>
            </section>

            <svg class="watermark" viewBox="0 0 560 500" aria-hidden="true">
                <g fill="none" stroke="#99a0a6" stroke-width="17" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="334" cy="74" r="54" />
                    <circle cx="459" cy="79" r="54" />
                    <circle cx="397" cy="158" r="112" />
                    <circle cx="360" cy="149" r="7" fill="#99a0a6" />
                    <circle cx="434" cy="149" r="7" fill="#99a0a6" />
                    <ellipse cx="397" cy="190" rx="30" ry="21" />
                    <path d="M397 210v14m-35-2q35 30 70 0" />
                    <path d="M322 252q-38 2-58 37m201-37q37 6 56 39" />
                    <path d="M314 289q-44 47-34 125m176-124q45 45 37 122" />
                    <path d="M303 421q54 44 104 3m4 0q48 38 92-2" />
                    <path d="M389 340c-38-47-108-10-83 43 17 36 83 78 83 78s68-43 84-81c23-54-48-86-84-40z" />
                    <path d="M104 158c-36 2-45 46-34 95 15 65 66 87 97 43 20-29 21-95 4-122-13-21-42-23-67-16z" />
                    <path d="M87 162q-8-23 6-34m47 32q8-23-5-34" />
                    <path d="M126 320v90c0 42-48 45-48 7 0-25 27-39 52-23" />
                    <path d="M168 277q40 75 107 69" />
                </g>
            </svg>

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
            </section>

            <div class="signature">
                <div class="line"></div>
                {{ $doc->doctorName }}
                @if ($doc->phone)
                    · {{ $doc->phone }}
                @endif
            </div>

            <footer class="footer">
                <svg class="animals" viewBox="0 0 460 310" aria-hidden="true">
                    <g stroke="#53331d" stroke-width="4">
                        <path d="M35 250V89M27 92q8-37 18 0" fill="none" stroke="#dba72d" stroke-width="13" />
                        <ellipse cx="58" cy="112" rx="43" ry="54" fill="#f7c328" />
                        <circle cx="43" cy="103" r="5" />
                        <circle cx="71" cy="103" r="5" />
                        <ellipse cx="57" cy="128" rx="27" ry="20" fill="#eba149" />
                        <path d="M50 135q8 8 16 0" fill="none" />
                        <path d="M44 165v103m28-103v103" fill="none" stroke="#e5a72b" stroke-width="24" />
                        <path d="M16 278h96" stroke="#2d241c" stroke-width="12" stroke-linecap="round" />
                        <circle cx="193" cy="191" r="70" fill="#a65a28" />
                        <path d="M128 151l-26-29 45-2m109 31 25-31-45 1" fill="#a65a28" />
                        <circle cx="193" cy="195" r="50" fill="#e7a84b" />
                        <circle cx="174" cy="187" r="5" />
                        <circle cx="213" cy="187" r="5" />
                        <ellipse cx="193" cy="208" rx="25" ry="19" fill="#efd0a0" />
                        <path d="M186 211q7 8 14 0" fill="none" />
                        <path d="M171 239v52m44-52v52" stroke="#c67832" stroke-width="25" />
                        <path d="M292 133l-21-31 12 52m44-21 20-31-10 53" fill="#222" />
                        <ellipse cx="310" cy="202" rx="46" ry="69" fill="#f4f4f4" />
                        <path d="M292 138l13 128m25-126-9 130" stroke="#222" stroke-width="18" />
                        <circle cx="293" cy="190" r="5" />
                        <circle cx="327" cy="190" r="5" />
                        <ellipse cx="310" cy="215" rx="21" ry="15" fill="#b9b9b9" />
                        <path d="M290 264v29m40-29v29" stroke="#222" stroke-width="13" />
                    </g>
                    <g aria-label="Pajarito rosado">
                        <ellipse cx="19" cy="178" rx="27" ry="20" fill="#f4a4b5" stroke="#8b4c59" stroke-width="3" />
                        <circle cx="28" cy="169" r="3.5" fill="#26171a" />
                        <path d="M46 174l22 8-22 8z" fill="#f1b52c" stroke="#8b6820" stroke-width="2" />
                        <path d="M-3 176l-35-27 8 34-28 13 54 1z" fill="#f4a4b5" stroke="#8b4c59" stroke-width="3" />
                        <path
                            d="M10 157q-8-18 1-30m12 30q3-20 14-27"
                            fill="none"
                            stroke="#f4a4b5"
                            stroke-width="7"
                            stroke-linecap="round"
                        />
                        <path d="M13 198l-3 13m21-12 5 13" stroke="#8b6820" stroke-width="3" />
                    </g>
                </svg>

                <div class="footer-copy">
                    <p class="slogan">Tu bebé en las mejores manos…</p>
                    <div class="appointment">AGENDA TU CITA</div>
                    <div class="phone">
                        <svg width="6mm" height="6mm" viewBox="0 0 64 64" aria-hidden="true">
                            <circle cx="32" cy="32" r="27" fill="none" stroke="#fff" stroke-width="4" />
                            <path
                                d="M18 49l3-10c-8-15 3-31 19-29 17 2 23 22 11 34-7 7-18 8-27 3z"
                                fill="none"
                                stroke="#fff"
                                stroke-width="3"
                            />
                            <path d="M25 21c2 13 8 19 19 20l4-6-8-4-3 4c-4-2-7-5-9-9l4-3-4-7z" fill="#fff" />
                        </svg>
                        <span>{{ $doc->phone }}</span>
                    </div>
                </div>

                <svg class="bee" viewBox="0 0 150 130" aria-hidden="true">
                    <ellipse
                        cx="45"
                        cy="55"
                        rx="29"
                        ry="18"
                        fill="#9dd9ee"
                        stroke="#386c86"
                        stroke-width="3"
                        transform="rotate(-30 45 55)"
                    />
                    <ellipse
                        cx="103"
                        cy="55"
                        rx="29"
                        ry="18"
                        fill="#9dd9ee"
                        stroke="#386c86"
                        stroke-width="3"
                        transform="rotate(30 103 55)"
                    />
                    <ellipse cx="74" cy="78" rx="38" ry="31" fill="#f4db22" stroke="#705c14" stroke-width="3" />
                    <path d="M48 68h52m-47 20h43" stroke="#44350e" stroke-width="9" />
                    <circle cx="63" cy="69" r="3" />
                    <circle cx="83" cy="69" r="3" />
                    <path d="M66 79q8 8 16 0" fill="none" stroke="#543b11" stroke-width="2" />
                    <path d="M59 47q-3-19-14-22m42 22q3-19 14-22" fill="none" stroke="#594a15" stroke-width="3" />
                    <circle cx="44" cy="24" r="5" fill="#f4db22" />
                    <circle cx="102" cy="24" r="5" fill="#f4db22" />
                </svg>
                <div class="heart" aria-hidden="true"></div>
            </footer>
        </main>
    </body>
</html>
