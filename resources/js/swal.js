import Swal from 'sweetalert2';

/**
 * Tema de SweetAlert2 adaptado al proyecto (Flux + Tailwind).
 * Se aplica por CSS variables; el dark mode se detecta con la clase
 * .dark del <html> y se re-aplica al cambiar de tema.
 */

function isDark() {
    return document.documentElement.classList.contains('dark');
}

function applyTheme() {
    const dark = isDark();

    Swal.mixin({
        customClass: {
            popup: 'swal-popup',
            confirmButton: 'swal-confirm',
            cancelButton: 'swal-cancel',
            denyButton: 'swal-deny',
            title: 'swal-title',
            htmlContainer: 'swal-html',
            actions: 'swal-actions',
        },
        buttonsStyling: false,
        confirmButtonColor: '#0d9488',
        background: dark ? '#18181b' : '#ffffff',
        color: dark ? '#f4f4f5' : '#18181b',
        borderRadius: '1rem',
        confirmButtonText: 'Sí, continuar',
        cancelButtonText: 'Cancelar',
        showCancelButton: true,
        reverseButtons: true,
    });
}

/** Confirma una acción con SweetAlert2 y ejecuta el callback si se confirma. */
export function swalConfirm(text, options = {}) {
    return Swal.fire({
        icon: options.icon ?? 'warning',
        title: options.title ?? '¿Estás seguro?',
        text,
        confirmButtonText: options.confirmText ?? 'Sí, continuar',
        cancelButtonText: options.cancelText ?? 'Cancelar',
        showCancelButton: true,
        reverseButtons: true,
    });
}

/** Toast pequeño en la esquina superior derecha. */
export function showToast(type, message) {
    const dark = isDark();
    const icons = {
        success: 'success',
        error: 'error',
        info: 'info',
        warning: 'warning',
    };

    return Swal.fire({
        toast: true,
        position: 'top-end',
        icon: icons[type] ?? 'info',
        title: message,
        showConfirmButton: false,
        timer: type === 'error' ? 5000 : 3200,
        timerProgressBar: true,
        background: dark ? '#18181b' : '#ffffff',
        color: dark ? '#f4f4f5' : '#18181b',
        iconColor: type === 'success' ? '#0d9488' : undefined,
    });
}

/**
 * Intercepta clicks en elementos con [data-swal-confirm]:
 * muestra la confirmación y, si se acepta, re-despacha el click
 * original para que Livewire procese el wire:click normalmente.
 */
export function initSwalConfirmInterceptor() {
    document.addEventListener(
        'click',
        (event) => {
            const el = event.target.closest('[data-swal-confirm]');
            if (!el) {
                return;
            }

            // Ya confirmado: dejar pasar y limpiar la bandera
            if (el.dataset.swalConfirmed === '1') {
                delete el.dataset.swalConfirmed;
                return;
            }

            event.preventDefault();
            event.stopImmediatePropagation();

            swalConfirm(el.dataset.swalConfirm).then((result) => {
                if (result.isConfirmed) {
                    el.dataset.swalConfirmed = '1';
                    el.click();
                }
            });
        },
        true,
    );

    // Intercepta submit de formularios con [data-swal-form-confirm]
    document.addEventListener(
        'submit',
        (event) => {
            const form = event.target.closest('[data-swal-form-confirm]');
            if (!form) {
                return;
            }

            if (form.dataset.swalConfirmed === '1') {
                delete form.dataset.swalConfirmed;
                return;
            }

            event.preventDefault();
            event.stopImmediatePropagation();

            swalConfirm(form.dataset.swalFormConfirm).then((result) => {
                if (result.isConfirmed) {
                    form.dataset.swalConfirmed = '1';
                    form.submit();
                }
            });
        },
        true,
    );
}

/** Escucha eventos Livewire "notify" para mostrar toasts desde el backend. */
export function initNotifyListener() {
    window.addEventListener('notify', (event) => {
        const detail = event.detail ?? {};
        showToast(detail.type ?? 'info', detail.message ?? '');
    });
}

/** Escucha eventos nativos "swal-toast" (para flash session). */
export function initFlashToastListener() {
    document.addEventListener('DOMContentLoaded', () => {
        const el = document.getElementById('swal-flash-toast');
        if (el) {
            showToast(el.dataset.type ?? 'info', el.dataset.message ?? '');
            el.remove();
        }
    });
}

applyTheme();
