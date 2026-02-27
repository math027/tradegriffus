<!-- Tela de Check-in (Promotor) — Foto da fachada obrigatória -->
<div class="section-card" style="max-width: 600px; margin: 0 auto;">
    <div style="text-align: center; margin-bottom: var(--space-lg);">
        <div class="card-icon bg-blue" style="margin: 0 auto var(--space-md); width: 56px; height: 56px; font-size: 24px;">
            <i class="fa-solid fa-camera"></i>
        </div>
        <h3>Check-in</h3>
        <p class="text-muted" style="font-size:var(--font-base);"><?= e($visita['pdv_nome']) ?></p>
        <?php if (!empty($visita['pdv_endereco'])): ?>
            <p class="text-sm text-muted"><?= e($visita['pdv_endereco']) ?></p>
        <?php endif; ?>
    </div>

    <form method="POST" action="/promotor/checkin/<?= $visita['id'] ?>" id="checkinForm" enctype="multipart/form-data">
        <?= csrf_field() ?>
        <input type="hidden" name="latitude" id="latitude">
        <input type="hidden" name="longitude" id="longitude">

        <!-- Status de localização -->
        <div class="geo-status" id="geoStatus">
            <i class="fa-solid fa-spinner fa-spin"></i> Obtendo localização...
        </div>

        <!-- Foto da fachada — OBRIGATÓRIA -->
        <div class="form-group">
            <label for="foto" style="font-weight:600;">
                <i class="fa-solid fa-store"></i> Foto da Fachada <span style="color:var(--danger);">*</span>
            </label>
            <p class="text-sm text-muted" style="margin-bottom:var(--space-xs);">
                Tire uma foto da fachada do ponto de venda
            </p>

            <!-- Preview container -->
            <div id="fotoPreviewContainer" style="display:none; margin-bottom:var(--space-sm); position:relative;">
                <img id="fotoPreview" style="width:100%; border-radius:var(--radius); max-height:300px; object-fit:cover;">
                <button type="button" onclick="removerFoto()" 
                        style="position:absolute; top:8px; right:8px; background:rgba(0,0,0,0.6); color:white; 
                               border:none; border-radius:50%; width:32px; height:32px; cursor:pointer; font-size:14px;">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <label class="foto-capture-btn" id="fotoCaptureBtn">
                <input type="file" id="foto" name="foto" accept="image/*" capture="environment"
                       required style="display:none;" onchange="previewFoto(this)">
                <i class="fa-solid fa-camera" style="font-size:24px;"></i>
                <span>Tirar Foto</span>
            </label>
        </div>

        <button type="submit" class="btn-primary" style="width: 100%; padding: 14px;" id="btnCheckin" disabled>
            <i class="fa-solid fa-right-to-bracket"></i> Confirmar Check-in
        </button>

        <a href="/promotor/dashboard" class="btn-outline" style="width:100%; padding:12px; margin-top:var(--space-sm); text-align:center; display:block;">
            Voltar
        </a>
    </form>
</div>

<style>
.foto-capture-btn {
    display: flex; flex-direction: column; align-items: center; justify-content: center;
    gap: var(--space-xs);
    padding: var(--space-lg);
    border: 2px dashed var(--border);
    border-radius: var(--radius);
    cursor: pointer;
    color: var(--text-muted);
    transition: all 0.2s;
    background: var(--gray-50);
}
.foto-capture-btn:hover {
    border-color: var(--primary);
    color: var(--primary);
    background: rgba(99,102,241,0.05);
}
.foto-capture-btn.has-photo {
    border-color: var(--success);
    background: rgba(16,185,129,0.05);
    color: var(--success);
}

.geo-status {
    text-align: center;
    padding: var(--space-sm) var(--space-md);
    border-radius: var(--radius);
    background: var(--gray-50);
    margin-bottom: var(--space-md);
    font-size: var(--font-sm);
}
</style>

<!-- Loading Overlay — Check-in Griffus SA -->
<div id="loadingOverlay" style="display:none; position:fixed; inset:0; z-index:9999;
     background: linear-gradient(135deg, #3d1a6b 0%, #7b2d8b 45%, #c44fa3 80%, #e8789e 100%);
     flex-direction:column; align-items:center; justify-content:center; gap:0;">

    <!-- Bolhas decorativas -->
    <div style="position:absolute; inset:0; overflow:hidden; pointer-events:none;">
        <div style="position:absolute; width:180px; height:180px; border-radius:50%;
                    background:rgba(255,255,255,0.07); top:-40px; left:-50px;"></div>
        <div style="position:absolute; width:120px; height:120px; border-radius:50%;
                    background:rgba(255,255,255,0.05); bottom:60px; right:-30px;"></div>
        <div style="position:absolute; width:80px; height:80px; border-radius:50%;
                    background:rgba(255,255,255,0.08); top:30%; left:15%;"></div>
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
            <i class="fa-solid fa-camera" style="color:rgba(255,255,255,0.8); font-size:16px;"></i>
        </div>
    </div>

    <!-- Texto de status -->
    <div style="text-align:center;">
        <p style="color:#fff; font-size:20px; font-weight:700; margin:0 0 8px;
                  letter-spacing:0.5px;">Realizando Check-in</p>
        <p style="color:rgba(255,255,255,0.7); font-size:14px; margin:0;">
            Aguarde, processando sua foto...
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

<script>
let geoReady = false;
let fotoReady = false;

function updateBtn() {
    document.getElementById('btnCheckin').disabled = !fotoReady;
}

function previewFoto(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = (e) => {
            document.getElementById('fotoPreview').src = e.target.result;
            document.getElementById('fotoPreviewContainer').style.display = 'block';
            document.getElementById('fotoCaptureBtn').style.display = 'none';
            fotoReady = true;
            updateBtn();
        };
        reader.readAsDataURL(input.files[0]);
    }
}

function removerFoto() {
    document.getElementById('foto').value = '';
    document.getElementById('fotoPreviewContainer').style.display = 'none';
    document.getElementById('fotoCaptureBtn').style.display = 'flex';
    fotoReady = false;
    updateBtn();
}

// Intercepta o submit para processar a imagem no browser antes de enviar
let _checkinProcessando = false;
document.getElementById('checkinForm').addEventListener('submit', async function(e) {
    if (_checkinProcessando) return; // segunda chamada após processamento — deixa passar
    e.preventDefault();

    // ---- OFFLINE: enfileira no IndexedDB ----
    if (!navigator.onLine) {
        try {
            // Processa a imagem antes de salvar
            const input = document.getElementById('foto');
            if (input.files && input.files[0]) {
                try {
                    const blob = await processarImagem(input.files[0]);
                    substituirArquivoNoInput(input, blob, 'foto_checkin.webp');
                } catch (err) { /* usa original */ }
            }

            const enqueued = await OfflineSync.interceptIfOffline(
                'checkin',
                this.action,
                this,
                { pdv_nome: '<?= e($visita['pdv_nome'] ?? '') ?>' }
            );
            if (enqueued) {
                // Redireciona para dashboard com feedback
                setTimeout(() => { window.location.href = '/promotor/dashboard'; }, 1500);
                return;
            }
        } catch (err) {
            console.error('Erro ao enfileirar offline:', err);
        }
    }

    // ---- ONLINE: fluxo normal ----
    const input = document.getElementById('foto');
    if (!input.files || !input.files[0]) return;

    // Exibe loading
    document.getElementById('loadingOverlay').style.display = 'flex';

    try {
        const blob = await processarImagem(input.files[0]);
        substituirArquivoNoInput(input, blob, 'foto_checkin.webp');
    } catch (err) {
        console.error('Erro ao processar imagem:', err);
    }

    _checkinProcessando = true;
    this.submit();
});

document.addEventListener('DOMContentLoaded', () => {
    const status = document.getElementById('geoStatus');

    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
            (pos) => {
                document.getElementById('latitude').value = pos.coords.latitude;
                document.getElementById('longitude').value = pos.coords.longitude;
                status.innerHTML = '<i class="fa-solid fa-check" style="color:var(--success);"></i> Localização obtida';
                status.style.color = 'var(--success)';
                geoReady = true;
            },
            (err) => {
                status.innerHTML = '<i class="fa-solid fa-triangle-exclamation" style="color:var(--warning);"></i> Localização indisponível — check-in sem GPS';
                status.style.color = 'var(--warning)';
                geoReady = false;
            },
            { enableHighAccuracy: true, timeout: 10000 }
        );
    } else {
        status.innerHTML = 'GPS não disponível';
    }
});
</script>
