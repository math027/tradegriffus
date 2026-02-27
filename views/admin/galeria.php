<!-- Galeria de Fotos -->
<div style="display:flex; justify-content:flex-end; margin-bottom:var(--space-md);">
    <button class="btn-outline btn-danger" onclick="executarLimpeza()" title="Remover fotos e respostas com mais de 45 dias">
        <i class="fa-solid fa-trash-can"></i> Limpeza (45 dias)
    </button>
</div>

<?php if (empty($visitas)): ?>
<div class="section-card">
    <div class="empty-state" style="padding:60px 20px; text-align:center;">
        <i class="fa-regular fa-image" style="font-size:48px; color:var(--gray-300); margin-bottom:16px;"></i>
        <p class="text-muted">Nenhuma foto registrada ainda.</p>
    </div>
</div>
<?php else: ?>

<?php foreach ($visitas as $visita): ?>
<?php
    $fotos = json_decode($visita['fotos_trabalho'] ?? '[]', true) ?: [];
    $todasFotos = [];
    if (!empty($visita['foto_checkin']))  $todasFotos[] = ['path' => $visita['foto_checkin'], 'label' => 'Fachada (Check-in)'];
    foreach ($fotos as $f) $todasFotos[] = ['path' => $f, 'label' => 'Foto de trabalho'];
    if (!empty($visita['foto_checkout'])) $todasFotos[] = ['path' => $visita['foto_checkout'], 'label' => 'Check-out'];
    if (empty($todasFotos)) continue;

    $duracao = '';
    if ($visita['checkin_at'] && $visita['checkout_at']) {
        $diff = strtotime($visita['checkout_at']) - strtotime($visita['checkin_at']);
        $h = floor($diff / 3600);
        $m = floor(($diff % 3600) / 60);
        $duracao = $h > 0 ? "{$h}h {$m}min" : "{$m}min";
    }
?>
<div class="section-card galeria-visita">
    <div class="galeria-header">
        <div class="galeria-info">
            <a href="/admin/pdvs/<?= $visita['pdv_id'] ?>" class="galeria-pdv"><?= e($visita['pdv_nome']) ?></a>
            <span class="text-muted text-sm">
                <i class="fa-regular fa-user"></i> <?= e($visita['promotor_nome']) ?>
                &middot; <?= date('d/m/Y', strtotime($visita['checkin_at'])) ?>
                &middot; <?= date('H:i', strtotime($visita['checkin_at'])) ?>
                <?php if ($visita['checkout_at']): ?>— <?= date('H:i', strtotime($visita['checkout_at'])) ?><?php endif; ?>
                <?php if ($duracao): ?>&middot; <i class="fa-regular fa-clock"></i> <?= $duracao ?><?php endif; ?>
            </span>
        </div>
    </div>
    <div class="galeria-grid">
        <?php foreach ($todasFotos as $foto): ?>
        <div class="galeria-thumb" onclick="abrirFoto('<?= e($foto['path']) ?>', '<?= e($visita['pdv_nome'] . ' — ' . $foto['label']) ?>')">
            <img src="/<?= e($foto['path']) ?>" alt="<?= e($foto['label']) ?>" loading="lazy">
            <span class="galeria-label"><?= e($foto['label']) ?></span>
        </div>
        <?php endforeach; ?>
    </div>
    <?php if (!empty($visita['observacao'])): ?>
    <div class="galeria-obs">
        <i class="fa-regular fa-comment"></i> <?= nl2br(e($visita['observacao'])) ?>
    </div>
    <?php endif; ?>
</div>
<?php endforeach; ?>
<?php endif; ?>

<!-- Modal Lightbox -->
<div id="fotoModal" class="foto-modal" onclick="fecharFoto()">
    <div class="foto-modal-content" onclick="event.stopPropagation()">
        <button class="foto-modal-close" onclick="fecharFoto()">&times;</button>
        <img id="fotoModalImg" src="" alt="">
        <p id="fotoModalLabel" class="foto-modal-label"></p>
    </div>
</div>

<style>
.galeria-visita { margin-bottom: 0; }

.galeria-header {
    display: flex; justify-content: space-between; align-items: center;
    margin-bottom: var(--space-sm); flex-wrap: wrap; gap: var(--space-xs);
}
.galeria-info { display: flex; flex-direction: column; gap: 2px; }
.galeria-pdv {
    font-weight: 700; font-size: var(--font-md); color: var(--primary);
    text-decoration: none;
}
.galeria-pdv:hover { text-decoration: underline; }

.galeria-grid {
    display: grid; grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
    gap: var(--space-sm);
}
.galeria-thumb {
    position: relative; cursor: pointer; border-radius: var(--radius);
    overflow: hidden; aspect-ratio: 4/3; background: var(--gray-50);
    border: 1px solid var(--border); transition: transform 0.15s, box-shadow 0.15s;
}
.galeria-thumb:hover { transform: scale(1.03); box-shadow: 0 4px 12px rgba(0,0,0,0.15); }
.galeria-thumb img { width: 100%; height: 100%; object-fit: cover; }
.galeria-label {
    position: absolute; bottom: 0; left: 0; right: 0;
    background: linear-gradient(transparent, rgba(0,0,0,0.7));
    color: white; font-size: 11px; padding: 14px 8px 5px; text-align: center;
}

.galeria-obs {
    margin-top: var(--space-sm); padding: var(--space-sm);
    background: var(--gray-50); border-radius: var(--radius);
    font-size: var(--font-sm); color: var(--text-secondary);
    display: flex; gap: var(--space-xs); align-items: flex-start;
}

/* Lightbox */
.foto-modal {
    display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%;
    background: rgba(0,0,0,0.85); z-index: 9999;
    justify-content: center; align-items: center;
}
.foto-modal.active { display: flex; }
.foto-modal-content { position: relative; max-width: 90vw; max-height: 90vh; text-align: center; }
.foto-modal-content img { max-width: 100%; max-height: 85vh; border-radius: 8px; box-shadow: 0 4px 20px rgba(0,0,0,0.4); }
.foto-modal-close {
    position: absolute; top: -14px; right: -14px;
    background: white; color: #333; border: none; border-radius: 50%;
    width: 36px; height: 36px; font-size: 22px; cursor: pointer;
    box-shadow: 0 2px 8px rgba(0,0,0,0.3); z-index: 1;
    display: flex; align-items: center; justify-content: center;
}
.foto-modal-label { color: white; margin-top: var(--space-sm); font-size: var(--font-sm); }

/* Danger button */
.btn-danger { color: #dc2626 !important; border-color: #fca5a5 !important; }
.btn-danger:hover { background: #fef2f2 !important; }
</style>

<script>
function abrirFoto(path, label) {
    const modal = document.getElementById('fotoModal');
    document.getElementById('fotoModalImg').src = '/' + path;
    document.getElementById('fotoModalLabel').textContent = label;
    modal.classList.add('active');
    document.body.style.overflow = 'hidden';
}

function fecharFoto() {
    document.getElementById('fotoModal').classList.remove('active');
    document.body.style.overflow = '';
}

document.addEventListener('keydown', e => { if (e.key === 'Escape') fecharFoto(); });

async function executarLimpeza() {
    const confirmed = await App.confirm('Tem certeza? Isso removerá permanentemente fotos e respostas de pesquisas com mais de 45 dias.');
    if (!confirmed) return;
    
    try {
        const res = await fetch('/admin/limpeza', { method: 'POST' });
        const data = await res.json();
        App.toast(data.mensagem, data.sucesso ? 'success' : 'danger');
        if (data.sucesso) setTimeout(() => location.reload(), 1500);
    } catch (err) {
        App.toast('Erro ao executar limpeza: ' + err.message, 'danger');
    }
}
</script>
