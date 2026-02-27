<!-- Loading Overlay — Checkout Griffus SA -->
<div id="loadingOverlay" style="display:none; position:fixed; inset:0; z-index:9999;
     background: linear-gradient(135deg, #3d1a6b 0%, #7b2d8b 45%, #c44fa3 80%, #e8789e 100%);
     flex-direction:column; align-items:center; justify-content:center; gap:0;">

    <!-- Bolhas decorativas -->
    <div style="position:absolute; inset:0; overflow:hidden; pointer-events:none;">
        <div style="position:absolute; width:200px; height:200px; border-radius:50%;
                    background:rgba(255,255,255,0.06); top:-60px; left:-60px;"></div>
        <div style="position:absolute; width:140px; height:140px; border-radius:50%;
                    background:rgba(255,255,255,0.05); bottom:40px; right:-40px;"></div>
        <div style="position:absolute; width:90px; height:90px; border-radius:50%;
                    background:rgba(255,255,255,0.07); top:35%; right:12%;"></div>
    </div>

    <!-- Logo / Marca -->
    <div style="text-align:center; margin-bottom:32px; position:relative;">
        <div style="width:72px; height:72px; border-radius:50%; background:rgba(255,255,255,0.15);
                    display:flex; align-items:center; justify-content:center; margin:0 auto 12px;
                    border:2px solid rgba(255,255,255,0.3); backdrop-filter:blur(4px);">
            <i class="fa-solid fa-spa" style="font-size:32px; color:#fff;"></i>
        </div>
        <p style="color:rgba(255,255,255,0.85); font-size:13px; letter-spacing:3px;
                  text-transform:uppercase; margin:0; font-weight:500;">Griffus SA</p>
    </div>

    <!-- Spinner personalizado -->
    <div style="position:relative; width:64px; height:64px; margin-bottom:28px;">
        <div style="position:absolute; inset:0; border-radius:50%;
                    border:3px solid rgba(255,255,255,0.2);"></div>
        <div style="position:absolute; inset:0; border-radius:50%;
                    border:3px solid transparent; border-top-color:#fff; border-right-color:rgba(255,255,255,0.5);
                    animation: griffusSpin 0.9s linear infinite;"></div>
        <div style="position:absolute; inset:8px; border-radius:50%;
                    background:rgba(255,255,255,0.1); display:flex; align-items:center; justify-content:center;">
            <i class="fa-solid fa-right-from-bracket" style="color:rgba(255,255,255,0.8); font-size:16px;"></i>
        </div>
    </div>

    <!-- Texto de status -->
    <div style="text-align:center;">
        <p style="color:#fff; font-size:20px; font-weight:700; margin:0 0 8px;
                  letter-spacing:0.5px;">Realizando Checkout</p>
        <p style="color:rgba(255,255,255,0.7); font-size:14px; margin:0;">
            Aguarde, finalizando sua visita...
        </p>
    </div>

    <!-- Bolinhas animadas -->
    <div style="display:flex; gap:8px; margin-top:28px;">
        <span style="width:8px; height:8px; background:rgba(255,255,255,0.9); border-radius:50%;
                     animation: griffusBounce 1.2s ease-in-out infinite;"></span>
        <span style="width:8px; height:8px; background:rgba(255,255,255,0.9); border-radius:50%;
                     animation: griffusBounce 1.2s ease-in-out 0.2s infinite;"></span>
        <span style="width:8px; height:8px; background:rgba(255,255,255,0.9); border-radius:50%;
                     animation: griffusBounce 1.2s ease-in-out 0.4s infinite;"></span>
    </div>
</div>

<style>
@keyframes griffusSpin {
    to { transform: rotate(360deg); }
}
@keyframes griffusBounce {
    0%, 80%, 100% { transform: scale(0.6); opacity: 0.5; }
    40%           { transform: scale(1);   opacity: 1;   }
}
</style>

<!-- Workflow da Visita — entre check-in e check-out -->
<?php
$tempoDecorrido = $visita['checkin_at'] ? time() - strtotime($visita['checkin_at']) : 0;
$minutosDecorridos = floor($tempoDecorrido / 60);
$horasDecorridas = floor($minutosDecorridos / 60);
$minRestantes = $minutosDecorridos % 60;
$tempoStr = $horasDecorridas > 0 ? "{$horasDecorridas}h {$minRestantes}min" : "{$minRestantes}min";
?>

<!-- Header com info do PDV -->
<div class="workflow-header">
    <div class="workflow-header-info">
        <div>
            <h3 style="margin:0;"><?= e($visita['pdv_nome']) ?></h3>
            <p class="text-sm text-muted" style="margin:2px 0 0;">
                <i class="fa-solid fa-location-dot"></i>
                <?= e($visita['pdv_endereco'] ?? $visita['pdv_cidade'] ?? '') ?>
            </p>
        </div>
        <div class="workflow-timer">
            <i class="fa-regular fa-clock"></i>
            <span id="timerDisplay"><?= $tempoStr ?></span>
        </div>
    </div>

    <?php if ($visita['foto_checkin']): ?>
    <div class="workflow-photo-checkin">
        <img src="/<?= e($visita['foto_checkin']) ?>" alt="Fachada" style="width:100%; max-height:150px; object-fit:cover; border-radius:var(--radius);">
    </div>
    <?php endif; ?>
</div>

<!-- Seções do Workflow -->
<div class="workflow-sections">

    <!-- 1. Fotos de Trabalho -->
    <div class="section-card">
        <div class="section-header">
            <h4 class="section-title">
                <i class="fa-solid fa-camera" style="color:var(--primary);"></i>
                Fotos de Trabalho
            </h4>
            <span class="text-sm text-muted" id="countFotos"><?= count($fotos) ?> foto(s)</span>
        </div>

        <div class="fotos-grid" id="fotosGrid">
            <?php foreach ($fotos as $idx => $f): ?>
            <div class="foto-thumb" data-saved="true" data-path="<?= e($f) ?>">
                <img src="/<?= e($f) ?>" alt="Foto">
                <button type="button" class="foto-remove-btn" onclick="removerFotoSalva(this)" title="Remover foto">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            <?php endforeach; ?>
        </div>

        <label class="foto-add-btn">
            <input type="file" accept="image/*" capture="environment" 
                   style="display:none;" onchange="adicionarFotoLocal(this)" multiple>
            <i class="fa-solid fa-plus"></i> Adicionar Foto
        </label>
    </div>

    <!-- 2. Pesquisas do Dia -->
    <?php if (!empty($pesquisas)): ?>
    <div class="section-card">
        <div class="section-header">
            <h4 class="section-title">
                <i class="fa-solid fa-clipboard-list" style="color:var(--info, #3B82F6);"></i>
                Pesquisas
            </h4>
        </div>

        <div class="pesquisas-list">
            <?php foreach ($pesquisas as $pesq): ?>
            <a href="/promotor/pesquisas/<?= $pesq['id'] ?>?visita_id=<?= $visita['id'] ?>&pdv_id=<?= $visita['pdv_id'] ?>" 
               class="pesquisa-link">
                <div class="pesquisa-link-info">
                    <strong><?= e($pesq['titulo']) ?></strong>
                    <?php if (!empty($pesq['descricao'])): ?>
                        <span class="text-sm text-muted"><?= e(mb_substr($pesq['descricao'], 0, 80)) ?></span>
                    <?php endif; ?>
                </div>
                <i class="fa-solid fa-chevron-right" style="color:var(--text-muted);"></i>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- 3. Observações -->
    <div class="section-card">
        <div class="section-header">
            <h4 class="section-title">
                <i class="fa-solid fa-sticky-note" style="color:var(--warning);"></i>
                Observações
            </h4>
            <span class="text-sm text-muted" id="obsStatus"></span>
        </div>

        <textarea id="observacao" class="form-control" rows="3" 
                  placeholder="Anote observações sobre esta visita..."
                  oninput="debounceSalvarObs()"><?= e($visita['observacao'] ?? '') ?></textarea>
    </div>

</div>

<!-- Botão Checkout fixo no rodapé — com foto obrigatória -->
<div class="workflow-footer">
    <form method="POST" action="/promotor/checkout/<?= $visita['id'] ?>" id="checkoutForm" enctype="multipart/form-data">
        <?= csrf_field() ?>
        <input type="hidden" name="latitude" id="latOut">
        <input type="hidden" name="longitude" id="lngOut">
        <input type="hidden" name="observacao" id="obsOut">
        <!-- Fotos de trabalho pendentes (adicionadas na sessão) — injetadas via JS -->
        <div id="pendingFotosInputs"></div>
        <!-- Fotos salvas removidas pelo usuário -->
        <div id="removedFotosInputs"></div>

        <!-- Foto de checkout -->
        <div id="checkoutFotoArea" style="margin-bottom:var(--space-sm);">
            <div id="checkoutPreviewContainer" style="display:none; position:relative; margin-bottom:var(--space-sm);">
                <img id="checkoutPreview" style="width:100%; max-height:180px; object-fit:cover; border-radius:var(--radius);">
                <button type="button" onclick="removerFotoCheckout()" 
                        style="position:absolute; top:6px; right:6px; background:rgba(0,0,0,0.6); color:white; 
                               border:none; border-radius:50%; width:28px; height:28px; cursor:pointer; font-size:12px;">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            <label class="foto-checkout-btn" id="fotoCheckoutBtn">
                <input type="file" name="foto_checkout" accept="image/*" capture="environment"
                       style="display:none;" onchange="previewCheckout(this)" required>
                <i class="fa-solid fa-camera"></i>
                <span>Foto da Fachada (obrigatório)</span>
            </label>
        </div>

        <button type="submit" class="btn-checkout" id="btnCheckout" disabled >
            <i class="fa-solid fa-right-from-bracket"></i>
            Finalizar Visita (Check-out)
        </button>
    </form>
</div>

<style>
/* --- Workflow Header --- */
.workflow-header {
    background: var(--white);
    border: 1px solid var(--border);
    border-radius: var(--radius-lg);
    padding: var(--space-md);
    margin-bottom: var(--space-md);
}
.workflow-header-info {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: var(--space-md);
}
.workflow-timer {
    display: flex; align-items: center; gap: 6px;
    font-weight: 600; font-size: var(--font-sm);
    color: var(--primary);
    background: var(--primary-light);
    padding: 6px 12px;
    border-radius: var(--radius);
    white-space: nowrap;
}
.workflow-photo-checkin {
    margin-top: var(--space-sm);
}

/* --- Fotos --- */
.fotos-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(80px, 1fr));
    gap: var(--space-xs);
    margin-bottom: var(--space-sm);
}
.foto-thumb {
    aspect-ratio: 1;
    border-radius: var(--radius-sm);
    overflow: hidden;
    position: relative;
}
.foto-thumb img {
    width: 100%; height: 100%; object-fit: cover;
}
.foto-remove-btn {
    position: absolute;
    top: 4px; right: 4px;
    width: 22px; height: 22px;
    border-radius: 50%;
    background: rgba(0,0,0,0.65);
    color: #fff;
    border: none;
    cursor: pointer;
    font-size: 11px;
    display: flex; align-items: center; justify-content: center;
    line-height: 1;
    z-index: 2;
    transition: background 0.15s;
}
.foto-remove-btn:hover {
    background: rgba(239,68,68,0.85);
}
.foto-add-btn {
    display: flex; align-items: center; justify-content: center;
    gap: var(--space-xs);
    padding: var(--space-sm);
    border: 2px dashed var(--border);
    border-radius: var(--radius);
    cursor: pointer;
    color: var(--text-muted);
    font-size: var(--font-sm);
    transition: all 0.2s;
}
.foto-add-btn:hover {
    border-color: var(--primary);
    color: var(--primary);
}

/* --- Pesquisas --- */
.pesquisas-list {
    display: flex; flex-direction: column; gap: var(--space-xs);
}
.pesquisa-link {
    display: flex; align-items: center; justify-content: space-between;
    padding: var(--space-sm) var(--space-md);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    text-decoration: none; color: inherit;
    transition: all 0.2s;
}
.pesquisa-link:hover {
    border-color: var(--primary);
    box-shadow: var(--shadow-sm);
}
.pesquisa-link-info {
    display: flex; flex-direction: column; gap: 2px;
}
.pesquisa-link-info strong { font-size: var(--font-sm); }

/* --- Footer Checkout --- */
.workflow-footer {
    position: sticky;
    bottom: 0;
    padding: var(--space-md);
    background: var(--white);
    border-top: 1px solid var(--border);
    margin: var(--space-md) calc(-1 * var(--space-md)) calc(-1 * var(--space-md));
}
.btn-checkout {
    width: 100%;
    padding: 16px;
    background: var(--success);
    color: white;
    border: none;
    border-radius: var(--radius);
    font-size: var(--font-base);
    font-weight: 600;
    cursor: pointer;
    display: flex; align-items: center; justify-content: center; gap: var(--space-sm);
    transition: all 0.2s;
}
.btn-checkout:hover {
    background: #059669;
    box-shadow: var(--shadow-md);
}

/* --- Workflow Sections --- */
.workflow-sections {
    padding-bottom: 220px; /* espaço para o footer fixo com foto */
}

.foto-checkout-btn {
    display: flex; align-items: center; justify-content: center;
    gap: var(--space-xs);
    padding: var(--space-sm);
    border: 2px dashed var(--border);
    border-radius: var(--radius);
    cursor: pointer;
    color: var(--text-muted);
    font-size: var(--font-sm);
    transition: all 0.2s;
    background: var(--gray-50);
}
.foto-checkout-btn:hover {
    border-color: var(--success);
    color: var(--success);
}
</style>

<script>
const VISITA_ID = <?= $visita['id'] ?>;
const CHECKIN_TIME = new Date('<?= $visita['checkin_at'] ?>').getTime();

// ---- Fotos de trabalho pendentes (armazenadas localmente até o checkout) ----
// Cada item: { file: File, dataUrl: string }
let pendingFotos = [];
// Paths de fotos salvas que o usuário removeu
let removedSavedPaths = [];

function atualizarContador() {
    const salvos  = document.querySelectorAll('#fotosGrid .foto-thumb[data-saved="true"]').length;
    const pending = pendingFotos.length;
    document.getElementById('countFotos').textContent = `${salvos + pending} foto(s)`;
}

function adicionarFotoLocal(input) {
    if (!input.files || !input.files.length) return;

    Array.from(input.files).forEach(file => {
        const reader = new FileReader();
        reader.onload = (e) => {
            const dataUrl = e.target.result;
            const idx = pendingFotos.length;
            pendingFotos.push({ file, dataUrl });

            const grid = document.getElementById('fotosGrid');
            const thumb = document.createElement('div');
            thumb.className = 'foto-thumb';
            thumb.dataset.pending = idx;
            thumb.innerHTML = `
                <img src="${dataUrl}" alt="Foto">
                <button type="button" class="foto-remove-btn" onclick="removerFotoPendente(this, ${idx})" title="Remover foto">
                    <i class="fa-solid fa-xmark"></i>
                </button>`;
            grid.appendChild(thumb);
            atualizarContador();
        };
        reader.readAsDataURL(file);
    });

    input.value = '';
}

function removerFotoPendente(btn, idx) {
    pendingFotos[idx] = null; // marca como removida
    btn.closest('.foto-thumb').remove();
    atualizarContador();
}

function removerFotoSalva(btn) {
    const thumb = btn.closest('.foto-thumb');
    const path = thumb.dataset.path;
    removedSavedPaths.push(path);
    thumb.remove();
    atualizarContador();
}

// ---- Timer ao vivo ----
setInterval(() => {
    const elapsed = Math.floor((Date.now() - CHECKIN_TIME) / 1000);
    const h = Math.floor(elapsed / 3600);
    const m = Math.floor((elapsed % 3600) / 60);
    document.getElementById('timerDisplay').textContent =
        h > 0 ? `${h}h ${m}min` : `${m}min`;
}, 30000);

// ---- Salvar observação (debounced) ----
let obsTimer = null;
function debounceSalvarObs() {
    clearTimeout(obsTimer);
    document.getElementById('obsStatus').textContent = '';
    obsTimer = setTimeout(async () => {
        const obs = document.getElementById('observacao').value;

        // Se offline, salva localmente
        if (!navigator.onLine) {
            document.getElementById('obsStatus').textContent = '📱 Salvo localmente';
            setTimeout(() => {
                document.getElementById('obsStatus').textContent = '';
            }, 2000);
            return;
        }

        const form = new FormData();
        form.append('observacao', obs);

        await fetch(`/promotor/visita/${VISITA_ID}/observacao`, {
            method: 'POST',
            body: form,
        });
        document.getElementById('obsStatus').textContent = '✓ Salvo';
        setTimeout(() => {
            document.getElementById('obsStatus').textContent = '';
        }, 2000);
    }, 800);
}

// ---- Checkout Photo ----
function previewCheckout(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = (e) => {
            document.getElementById('checkoutPreview').src = e.target.result;
            document.getElementById('checkoutPreviewContainer').style.display = 'block';
            document.getElementById('fotoCheckoutBtn').style.display = 'none';
            document.getElementById('btnCheckout').disabled = false;
        };
        reader.readAsDataURL(input.files[0]);
    }
}

function removerFotoCheckout() {
    document.querySelector('[name="foto_checkout"]').value = '';
    document.getElementById('checkoutPreviewContainer').style.display = 'none';
    document.getElementById('fotoCheckoutBtn').style.display = 'flex';
    document.getElementById('btnCheckout').disabled = true;
}

// ---- Checkout: processa imagens no browser e submete ----
let _checkoutProcessando = false;

document.getElementById('checkoutForm').addEventListener('submit', async function(e) {
    if (_checkoutProcessando) return; // segunda chamada após processamento — deixa passar
    e.preventDefault();

    // ---- OFFLINE: enfileira no IndexedDB ----
    if (!navigator.onLine) {
        try {
            // Sincroniza observação no form
            document.getElementById('obsOut').value = document.getElementById('observacao').value;

            // Tenta pegar GPS
            if (navigator.geolocation) {
                try {
                    const pos = await new Promise((resolve, reject) => {
                        navigator.geolocation.getCurrentPosition(resolve, reject, {
                            enableHighAccuracy: true, timeout: 3000
                        });
                    });
                    document.getElementById('latOut').value = pos.coords.latitude;
                    document.getElementById('lngOut').value = pos.coords.longitude;
                } catch (err) { /* sem GPS offline */ }
            }

            // Processa fotos de trabalho pendentes para o form
            const pendingContainer = document.getElementById('pendingFotosInputs');
            pendingContainer.innerHTML = '';
            const fotasValidas = pendingFotos.filter(f => f !== null);
            for (let i = 0; i < fotasValidas.length; i++) {
                try {
                    const blob = await processarImagem(fotasValidas[i].file);
                    const inp = document.createElement('input');
                    inp.type = 'file'; inp.name = 'fotos_trabalho[]'; inp.style.display = 'none';
                    const dt = new DataTransfer();
                    dt.items.add(new File([blob], `foto_trabalho_${i}.webp`, { type: 'image/webp' }));
                    inp.files = dt.files;
                    pendingContainer.appendChild(inp);
                } catch (err) { }
            }

            // Injeta fotos removidas
            const removedContainer = document.getElementById('removedFotosInputs');
            removedContainer.innerHTML = '';
            removedSavedPaths.forEach(path => {
                const inp = document.createElement('input');
                inp.type = 'hidden'; inp.name = 'fotos_remover[]'; inp.value = path;
                removedContainer.appendChild(inp);
            });

            // Processa foto de checkout
            const inputCheckout = document.querySelector('[name="foto_checkout"]');
            if (inputCheckout && inputCheckout.files && inputCheckout.files[0]) {
                try {
                    const blob = await processarImagem(inputCheckout.files[0]);
                    substituirArquivoNoInput(inputCheckout, blob, 'foto_checkout.webp');
                } catch (err) { }
            }

            const enqueued = await OfflineSync.interceptIfOffline(
                'checkout',
                this.action,
                this,
                { pdv_nome: '<?= e($visita['pdv_nome'] ?? '') ?>' }
            );
            if (enqueued) {
                setTimeout(() => { window.location.href = '/promotor/dashboard'; }, 1500);
                return;
            }
        } catch (err) {
            console.error('Erro ao enfileirar checkout offline:', err);
        }
    }

    // ---- ONLINE: fluxo normal ----
    // Mostra loading imediatamente
    document.getElementById('loadingOverlay').style.display = 'flex';

    // Sincroniza observação e GPS
    document.getElementById('obsOut').value = document.getElementById('observacao').value;
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(pos => {
            document.getElementById('latOut').value = pos.coords.latitude;
            document.getElementById('lngOut').value = pos.coords.longitude;
        }, () => {}, { enableHighAccuracy: true, timeout: 3000 });
    }

    // 1. Processa foto do checkout
    const inputCheckout = document.querySelector('[name="foto_checkout"]');
    if (inputCheckout && inputCheckout.files && inputCheckout.files[0]) {
        try {
            const blob = await processarImagem(inputCheckout.files[0]);
            substituirArquivoNoInput(inputCheckout, blob, 'foto_checkout.webp');
        } catch (err) {
            console.error('Erro ao processar foto checkout:', err);
        }
    }

    // 2. Injeta fotos de trabalho pendentes já processadas
    const pendingContainer = document.getElementById('pendingFotosInputs');
    pendingContainer.innerHTML = '';

    const fotasValidas = pendingFotos.filter(f => f !== null);
    for (let i = 0; i < fotasValidas.length; i++) {
        try {
            const blob = await processarImagem(fotasValidas[i].file);
            const inp = document.createElement('input');
            inp.type = 'file';
            inp.name = 'fotos_trabalho[]';
            inp.style.display = 'none';
            const dt = new DataTransfer();
            dt.items.add(new File([blob], `foto_trabalho_${i}.webp`, { type: 'image/webp' }));
            inp.files = dt.files;
            pendingContainer.appendChild(inp);
        } catch (err) {
            console.error(`Erro ao processar foto de trabalho ${i}:`, err);
        }
    }

    // 3. Injeta caminhos de fotos salvas removidas
    const removedContainer = document.getElementById('removedFotosInputs');
    removedContainer.innerHTML = '';
    removedSavedPaths.forEach(path => {
        const inp = document.createElement('input');
        inp.type = 'hidden';
        inp.name = 'fotos_remover[]';
        inp.value = path;
        removedContainer.appendChild(inp);
    });

    _checkoutProcessando = true;
    this.submit();
});
</script>
