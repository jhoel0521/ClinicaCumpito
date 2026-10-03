/**
 * Links de "Ver / Imprimir" (`a[data-print-link]`).
 *
 * Los campos de receta y laboratorio se guardan con wire:change, es decir al
 * salir del campo. Si la doctora escribe una dosis y hace clic directo en
 * imprimir, ese guardado todavía está en camino y el PDF saldría con el dato
 * anterior. Por eso:
 *
 * 1. Si el link es target="_blank", la pestaña nueva se abre en el mismo clic
 *    (si se abre después, el navegador la bloquea como popup).
 * 2. Se espera a que terminen los requests de Livewire pendientes.
 * 3. Recién entonces la pestaña carga el PDF.
 */

let inFlight = 0;

function trackLivewireRequests() {
    window.Livewire.interceptRequest(({ onResponse, onFailure }) => {
        inFlight++;
        let done = false;
        const finish = () => {
            if (!done) {
                done = true;
                inFlight = Math.max(0, inFlight - 1);
            }
        };
        onResponse(finish);
        onFailure(finish);
    });
}

function waitForSaves(timeoutMs = 8000) {
    const started = Date.now();

    return new Promise((resolve) => {
        // Un tick para que el `change` del campo que perdió el foco dispare su request.
        setTimeout(function check() {
            if (inFlight === 0 || Date.now() - started > timeoutMs) {
                resolve();
                return;
            }
            setTimeout(check, 50);
        }, 60);
    });
}

function onClick(event) {
    const link = event.target.closest('a[data-print-link]');
    if (!link || event.button !== 0 || event.ctrlKey || event.metaKey || event.shiftKey) {
        return;
    }

    event.preventDefault();
    document.activeElement?.blur?.();

    // Pestaña nueva solo si el link lo pide; si no, se navega en la misma (como siempre).
    const tab = link.target === '_blank' ? window.open('', '_blank') : null;
    if (tab) {
        tab.document.title = 'Preparando documento…';
        tab.document.body.textContent = 'Guardando los últimos cambios…';
    }

    waitForSaves().then(() => {
        if (tab && !tab.closed) {
            tab.location.href = link.href;
        } else {
            window.location.href = link.href;
        }
    });
}

export function initPrintLinks() {
    document.addEventListener('click', onClick);

    if (window.Livewire?.interceptRequest) {
        trackLivewireRequests();
    } else {
        document.addEventListener('livewire:init', trackLivewireRequests, { once: true });
    }
}
