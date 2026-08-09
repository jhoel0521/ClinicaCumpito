{{--
    Vista previa standalone de un documento clínico (sin layout de la app).
    Recibe: $doc (ClinicalDocumentDTO), $documentView (vista blade),
    $editUrl, $downloadUrl
--}}
<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <title>Vista previa · {{ $doc->patientName }}</title>
        <style>
            * {
                box-sizing: border-box;
            }

            body {
                margin: 0;
                background: #e9edf1;
                font-family: Arial, Helvetica, sans-serif;
            }

            .toolbar {
                position: sticky;
                top: 0;
                z-index: 40;
                display: flex;
                flex-wrap: wrap;
                align-items: center;
                justify-content: space-between;
                gap: 10px;
                padding: 10px 18px;
                background: #ffffff;
                border-bottom: 1px solid #dcdcdc;
                box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            }

            .toolbar .info {
                min-width: 0;
            }

            .toolbar .info h1 {
                margin: 0;
                font-size: 15px;
                color: #1a1a1a;
            }

            .toolbar .info p {
                margin: 2px 0 0;
                font-size: 12px;
                color: #666;
                overflow: hidden;
                text-overflow: ellipsis;
                white-space: nowrap;
            }

            .toolbar .actions {
                display: flex;
                flex-wrap: wrap;
                gap: 8px;
            }

            .btn {
                display: inline-flex;
                align-items: center;
                gap: 6px;
                padding: 8px 14px;
                border-radius: 8px;
                border: 1px solid #d0d0d0;
                background: #fff;
                color: #333;
                font-size: 13px;
                font-weight: 600;
                text-decoration: none;
                cursor: pointer;
                transition: background 0.15s;
            }

            .btn:hover {
                background: #f4f4f4;
            }

            .btn.primary {
                background: #0d9488;
                border-color: #0d9488;
                color: #fff;
            }
            .btn.primary:hover {
                background: #0f766e;
            }

            .btn.danger {
                background: #dc2626;
                border-color: #dc2626;
                color: #fff;
            }
            .btn.danger:hover {
                background: #b91c1c;
            }

            .btn:disabled {
                opacity: 0.55;
                cursor: not-allowed;
            }

            .alert {
                margin: 12px 18px 0;
                padding: 12px 16px;
                border-radius: 10px;
                border: 1px solid #f59e0b;
                background: #fef3c7;
                color: #92400e;
                font-size: 13px;
            }

            .alert a {
                color: #92400e;
                font-weight: 700;
            }

            .stage {
                padding: 24px 18px 40px;
                display: flex;
                justify-content: center;
            }

            .stage-inner {
                overflow-x: auto;
                max-width: 100%;
            }

            .sheet-wrapper {
                width: {{ $doc->paper->widthMm * 3.78 }}px;
                box-shadow: 0 8px 30px rgba(0, 0, 0, 0.18);
                border-radius: 2px;
                overflow: hidden;
            }

            .size-note {
                margin-top: 14px;
                text-align: center;
                font-size: 11px;
                color: #888;
            }
        </style>
    </head>
    <body>
        <div class="toolbar">
            <div class="info">
                <h1>{{ $doc->title === 'receta' ? 'Receta médica' : 'Orden de laboratorio' }}</h1>
                <p>{{ $doc->patientName }} · {{ $doc->dateText }}</p>
            </div>
            <div class="actions">
                <a class="btn" href="{{ $editUrl }}">← Editar</a>

                @if ($doc->overflow)
                    <span class="btn danger" disabled>Contenido excede el espacio</span>
                @elseif ($downloadUrl)
                    <a class="btn primary" href="{{ $downloadUrl }}">Descargar PDF</a>
                    <a class="btn" href="{{ $downloadUrl }}" target="_blank">Imprimir</a>
                @endif

                <a class="btn" href="{{ url()->previous() }}">Cerrar</a>
            </div>
        </div>

        @if ($doc->overflow)
            <div class="alert">
                <strong>El contenido no cabe en una sola página.</strong>
                La descarga e impresión están bloqueadas para evitar información cortada.
                <a href="{{ $editUrl }}">Editar la receta</a>
                y reducí medicamentos o indicaciones.
            </div>
        @endif

        <main class="stage">
            <div class="stage-inner">
                <div class="sheet-wrapper">
                    @include($documentView, ['doc' => $doc])
                </div>
                <p class="size-note">
                    {{ number_format($doc->paper->widthMm, 1) }} × {{ number_format($doc->paper->heightMm, 1) }} mm ·
                    {{ $doc->title === 'receta' ? 'formato del recetario · una sola página' : 'hoja oficio' }} · escala
                    100 %
                </p>
            </div>
        </main>
    </body>
</html>
