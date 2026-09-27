<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />

<title>{{ $title ?? config('app.name') }}</title>

<link rel="icon" href="/favicon.ico" sizes="any" />
<link rel="icon" href="/favicon.svg" type="image/svg+xml" />
<link rel="apple-touch-icon" href="/apple-touch-icon.png" />

{{--
    Sin fuentes externas: con internet móvil lento una hoja de estilos de otro
    dominio bloquea el primer pintado. El tema cae a la fuente del sistema.
--}}

@vite(['resources/css/app.css', 'resources/js/app.js'])
<script>
    (function () {
        var old = localStorage.getItem('theme');
        if (old) {
            if (!localStorage.getItem('flux.appearance')) {
                localStorage.setItem('flux.appearance', old);
            }
            localStorage.removeItem('theme');
        }
        // Sin modo automático: solo claro u oscuro, por defecto claro.
        if (!localStorage.getItem('flux.appearance')) {
            localStorage.setItem('flux.appearance', 'light');
        }
    })();
</script>
@fluxAppearance
