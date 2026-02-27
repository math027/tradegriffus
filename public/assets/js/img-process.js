/**
 * img-process.js — Processamento de imagem no navegador
 *
 * - Redimensiona pelo lado maior (máx 1400px)
 * - Converte para WebP
 * - Qualidade adaptativa: alvo 150 KB – 500 KB
 * - Remove todos os metadados (EXIF, GPS etc) via Canvas API
 * - Corrige orientação EXIF automaticamente via createImageBitmap
 */

/**
 * Processa um File de imagem e retorna um Blob WebP otimizado.
 * @param {File} file
 * @returns {Promise<Blob>}
 */
async function processarImagem(file) {
    const MAX_LADO  = 1400;
    const MAX_BYTES = 500 * 1024; // 500 KB
    const MIN_BYTES = 150 * 1024; // 150 KB

    // createImageBitmap já respeita o EXIF de orientação (Chrome, Firefox, Safari 15+)
    // e não copia nenhum metadado para o bitmap
    const bitmap = await createImageBitmap(file);

    let w = bitmap.width;
    let h = bitmap.height;

    // Redimensiona pelo lado maior mantendo proporção
    if (w >= h && w > MAX_LADO) {
        h = Math.round(h * MAX_LADO / w);
        w = MAX_LADO;
    } else if (h > w && h > MAX_LADO) {
        w = Math.round(w * MAX_LADO / h);
        h = MAX_LADO;
    }

    const canvas = document.createElement('canvas');
    canvas.width  = w;
    canvas.height = h;

    const ctx = canvas.getContext('2d');
    ctx.fillStyle = '#ffffff';
    ctx.fillRect(0, 0, w, h);
    ctx.drawImage(bitmap, 0, 0, w, h);
    bitmap.close();

    // Qualidade adaptativa — tenta encaixar no alvo de tamanho
    const tentativas = [0.82, 0.72, 0.60, 0.48, 0.36, 0.25];

    for (let i = 0; i < tentativas.length; i++) {
        const qualidade = tentativas[i];
        const blob = await canvasToBlob(canvas, 'image/webp', qualidade);

        // Dentro do alvo, ou última tentativa — aceita
        if (blob.size <= MAX_BYTES || i === tentativas.length - 1) {
            return blob;
        }
        // Muito grande — próxima qualidade menor
    }
}

/**
 * Promisifica canvas.toBlob
 */
function canvasToBlob(canvas, type, quality) {
    return new Promise((resolve, reject) => {
        canvas.toBlob(blob => {
            if (blob) resolve(blob);
            else reject(new Error('canvas.toBlob falhou'));
        }, type, quality);
    });
}

/**
 * Substitui o arquivo em um input[type=file] por um Blob processado.
 * @param {HTMLInputElement} input
 * @param {Blob} blob
 * @param {string} nome  nome do arquivo resultante
 */
function substituirArquivoNoInput(input, blob, nome) {
    const file = new File([blob], nome, { type: 'image/webp' });
    const dt = new DataTransfer();
    dt.items.add(file);
    input.files = dt.files;
}
