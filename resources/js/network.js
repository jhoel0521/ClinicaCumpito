import Swal from 'sweetalert2';
import { showToast } from './swal';

/**
 * Resiliencia ante internet móvil lento/intermitente (EDGE/3G por hotspot).
 *
 * - Si un request de Livewire no llega al servidor (sin señal), avisa que el
 *   último cambio NO se guardó y muestra una franja fija hasta que vuelva la
 *   conexión.
 * - Si la sesión expiró (419), reemplaza el confirm() nativo de Livewire —que
 *   recarga la página y pierde lo escrito— por un aviso que deja copiar el
 *   texto antes de recargar.
 */

let banner = null;

function setOfflineBanner(visible) {
    if (!visible) {
        banner?.remove();
        banner = null;
        return;
    }

    if (banner) {
        return;
    }

    banner = document.createElement('div');
    banner.setAttribute('role', 'alert');
    banner.dataset.test = 'offline-banner';
    banner.textContent = 'Sin conexión: los cambios no se están guardando. Esperá a que vuelva la señal y repetí el último cambio.';
    Object.assign(banner.style, {
        position: 'fixed',
        left: '0',
        right: '0',
        bottom: '0',
        zIndex: '9999',
        padding: '10px 16px',
        background: '#b91c1c',
        color: '#ffffff',
        font: '600 14px/1.4 system-ui, sans-serif',
        textAlign: 'center',
    });
    document.body.appendChild(banner);
}

function registerInterceptor() {
    window.Livewire.interceptRequest(({ onFailure, onError, onSuccess }) => {
        onFailure(() => {
            setOfflineBanner(true);
            showToast('error', 'Sin conexión: el último cambio NO se guardó.');
        });

        onError(({ response, preventDefault }) => {
            if (response?.status !== 419) {
                return;
            }

            preventDefault();
            Swal.fire({
                icon: 'warning',
                title: 'La sesión expiró',
                text: 'Copiá lo que estabas escribiendo antes de recargar; luego volvé a iniciar sesión si te lo pide.',
                confirmButtonText: 'Recargar página',
                cancelButtonText: 'Todavía no',
                showCancelButton: true,
                reverseButtons: true,
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.reload();
                }
            });
        });

        onSuccess(() => setOfflineBanner(false));
    });
}

export function initNetworkResilience() {
    if (window.Livewire?.interceptRequest) {
        registerInterceptor();
    } else {
        document.addEventListener('livewire:init', registerInterceptor, { once: true });
    }

    window.addEventListener('offline', () => setOfflineBanner(true));
    window.addEventListener('online', () => setOfflineBanner(false));
}
