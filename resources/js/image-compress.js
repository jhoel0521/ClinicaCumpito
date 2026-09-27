/**
 * Compresión de fotos en el navegador antes de subirlas.
 *
 * Con internet móvil lento (EDGE/3G) una foto de celular de 3–5 MB tarda
 * minutos en subir y puede cortarse. Los <input type="file"> marcados con
 * `data-compress-image` reducen las imágenes a como máximo MAX_SIDE px y
 * JPEG de calidad QUALITY (~200–400 KB) antes de que Livewire las suba.
 * Los PDF y archivos que no son imagen pasan sin cambios.
 */

const MAX_SIDE = 1800;
const QUALITY = 0.75;
const MIN_BYTES_TO_COMPRESS = 400 * 1024;
const COMPRESSIBLE = ['image/jpeg', 'image/png', 'image/webp'];

function loadImage(file) {
    return new Promise((resolve, reject) => {
        const url = URL.createObjectURL(file);
        const img = new Image();
        img.onload = () => {
            URL.revokeObjectURL(url);
            resolve(img);
        };
        img.onerror = (error) => {
            URL.revokeObjectURL(url);
            reject(error);
        };
        img.src = url;
    });
}

async function compress(file) {
    if (!COMPRESSIBLE.includes(file.type) || file.size < MIN_BYTES_TO_COMPRESS) {
        return file;
    }

    const img = await loadImage(file);
    const scale = Math.min(1, MAX_SIDE / Math.max(img.width, img.height));
    const canvas = document.createElement('canvas');
    canvas.width = Math.round(img.width * scale);
    canvas.height = Math.round(img.height * scale);

    const ctx = canvas.getContext('2d');
    // Fondo blanco: los PNG con transparencia quedarían negros en JPEG.
    ctx.fillStyle = '#ffffff';
    ctx.fillRect(0, 0, canvas.width, canvas.height);
    ctx.drawImage(img, 0, 0, canvas.width, canvas.height);

    const blob = await new Promise((resolve) => canvas.toBlob(resolve, 'image/jpeg', QUALITY));

    if (!blob || blob.size >= file.size) {
        return file;
    }

    const name = file.name.replace(/\.[^.]+$/, '') + '.jpg';

    return new File([blob], name, { type: 'image/jpeg', lastModified: Date.now() });
}

export function initImageCompression() {
    document.addEventListener(
        'change',
        async (event) => {
            const input = event.target;

            if (!(input instanceof HTMLInputElement) || input.type !== 'file' || !input.hasAttribute('data-compress-image')) {
                return;
            }

            // Segundo disparo (ya comprimido): dejar que Livewire lo procese.
            if (input.dataset.compressed === '1') {
                delete input.dataset.compressed;
                return;
            }

            if (!input.files?.length || typeof DataTransfer === 'undefined') {
                return;
            }

            event.stopImmediatePropagation();

            const transfer = new DataTransfer();
            for (const file of input.files) {
                try {
                    transfer.items.add(await compress(file));
                } catch {
                    // Si el navegador no puede procesar la imagen, se sube tal cual.
                    transfer.items.add(file);
                }
            }

            input.files = transfer.files;
            input.dataset.compressed = '1';
            input.dispatchEvent(new Event('change', { bubbles: true }));
        },
        true,
    );
}
