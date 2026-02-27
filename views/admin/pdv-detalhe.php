<!-- Detalhe do PDV -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

<style>
    #map-detalhe { height: 350px; width: 100%; border-radius: 4px; border: 1px solid var(--border); z-index: 1; }
    .map-legend { display: flex; flex-wrap: wrap; gap: 12px; font-size: 12px; color: var(--text-muted); margin-top: 8px; }
    .map-legend-item { display: flex; align-items: center; gap: 5px; }
    .legend-dot { width: 12px; height: 12px; border-radius: 50%; border: 2px solid white; box-shadow: 0 0 3px rgba(0,0,0,0.3); }
    .legend-dot.pdv { background: #ef4444; }
    .legend-dot.checkin { background: #3b82f6; }
</style>

<div class="section-header" style="margin-bottom: var(--space-lg);">
    <div>
        <h2><?= e($pdv['nome']) ?></h2>
        <?php if (!empty($pdv['codigo'])): ?>
            <span class="status-badge status-done"><?= e($pdv['codigo']) ?></span>
        <?php endif; ?>
    </div>
    <div class="action-buttons">
        <a href="/admin/pdvs/<?= $pdv['id'] ?>/editar" class="btn-primary">
            <i class="fa-solid fa-pen"></i> Editar
        </a>
        <a href="/admin/pdvs" class="btn-outline">
            <i class="fa-solid fa-arrow-left"></i> Voltar
        </a>
    </div>
</div>

<div class="section-card">
    <div class="detail-grid">
        <div class="detail-item">
            <span class="detail-label">Código</span>
            <span class="detail-value"><?= e($pdv['codigo'] ?? '—') ?></span>
        </div>
        <div class="detail-item">
            <span class="detail-label">CNPJ</span>
            <span class="detail-value"><?= e($pdv['cnpj'] ?? '—') ?></span>
        </div>
        <div class="detail-item">
            <span class="detail-label">Telefone</span>
            <span class="detail-value"><?= e($pdv['telefone'] ?? '—') ?></span>
        </div>
        <div class="detail-item">
            <span class="detail-label">Responsável</span>
            <span class="detail-value"><?= e($pdv['responsavel'] ?? '—') ?></span>
        </div>
    </div>
</div>

<div class="section-card">
    <h4 style="margin-bottom:var(--space-md);"><i class="fa-solid fa-location-dot"></i> Endereço</h4>
    <div class="detail-grid">
        <div class="detail-item">
            <span class="detail-label">Rua</span>
            <span class="detail-value"><?= e($pdv['rua'] ?? '—') ?></span>
        </div>
        <div class="detail-item">
            <span class="detail-label">Número</span>
            <span class="detail-value"><?= e($pdv['numero'] ?? '—') ?></span>
        </div>
        <div class="detail-item">
            <span class="detail-label">Bairro</span>
            <span class="detail-value"><?= e($pdv['bairro'] ?? '—') ?></span>
        </div>
        <div class="detail-item">
            <span class="detail-label">Cidade</span>
            <span class="detail-value"><?= e($pdv['cidade'] ?? '—') ?></span>
        </div>
        <div class="detail-item">
            <span class="detail-label">UF</span>
            <span class="detail-value"><?= e($pdv['uf'] ?? '—') ?></span>
        </div>
        <div class="detail-item">
            <span class="detail-label">CEP</span>
            <span class="detail-value"><?= e($pdv['cep'] ?? '—') ?></span>
        </div>
        <?php if (!empty($pdv['endereco'])): ?>
        <div class="detail-item" style="grid-column: 1 / -1;">
            <span class="detail-label">Complemento</span>
            <span class="detail-value"><?= e($pdv['endereco']) ?></span>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php if (!empty($pdv['latitude']) && !empty($pdv['longitude'])): ?>
<div class="section-card">
    <h4 style="margin-bottom:var(--space-md);"><i class="fa-solid fa-map-location-dot"></i> Localização</h4>
    <div id="map-detalhe"></div>
    <div class="map-legend">
        <span class="map-legend-item"><span class="legend-dot pdv"></span> Localização do PDV</span>
        <?php if (!empty($ultimosCheckins)): ?>
        <span class="map-legend-item"><span class="legend-dot checkin"></span> Últimos <?= count($ultimosCheckins) ?> check-ins</span>
        <?php endif; ?>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const lat = <?= $pdv['latitude'] ?>;
    const lng = <?= $pdv['longitude'] ?>;

    const map = L.map('map-detalhe').setView([lat, lng], 16);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap'
    }).addTo(map);

    // Ícone vermelho do PDV
    const pdvIcon = L.divIcon({
        html: '<div style="position:relative;"><div style="width:22px;height:22px;background:#ef4444;border:3px solid white;border-radius:50%;box-shadow:0 2px 6px rgba(0,0,0,0.35);"></div><div style="width:0;height:0;border-left:6px solid transparent;border-right:6px solid transparent;border-top:8px solid #ef4444;margin:-2px auto 0;filter:drop-shadow(0 1px 1px rgba(0,0,0,0.2));"></div></div>',
        className: '',
        iconSize: [22, 30],
        iconAnchor: [11, 30]
    });

    L.marker([lat, lng], { icon: pdvIcon })
        .addTo(map)
        .bindPopup('<b><?= e($pdv['nome']) ?></b><br><small><?= e($pdv['rua'] ?? '') ?><?= !empty($pdv['numero']) ? ', ' . e($pdv['numero']) : '' ?></small>')
        .openPopup();

    // Marcadores de check-in (últimos 3)
    <?php if (!empty($ultimosCheckins)): ?>
    const checkinsData = <?= json_encode($ultimosCheckins) ?>;
    const checkinIcon = L.divIcon({
        html: '<div style="width:14px;height:14px;background:#3b82f6;border:2.5px solid white;border-radius:50%;box-shadow:0 1px 4px rgba(0,0,0,0.3);"></div>',
        className: '',
        iconSize: [14, 14],
        iconAnchor: [7, 7]
    });

    checkinsData.forEach(checkin => {
        const dt = new Date(checkin.checkin_at);
        const dataFormatada = dt.toLocaleDateString('pt-BR') + ' ' + dt.toLocaleTimeString('pt-BR', {hour:'2-digit', minute:'2-digit'});
        
        L.marker([parseFloat(checkin.latitude_in), parseFloat(checkin.longitude_in)], { icon: checkinIcon })
            .addTo(map)
            .bindPopup(`<b>${checkin.promotor_nome}</b><br><small>${dataFormatada}</small>`);
    });
    <?php endif; ?>
});
</script>
<?php endif; ?>

<!-- ==========================================
     HISTÓRICO DE VISITAS (Fotos + Timeline)
     ========================================== -->
<?php if (!empty($visitas)): ?>
<div class="section-card">
    <h4 style="margin-bottom:var(--space-md);">
        <i class="fa-solid fa-clock-rotate-left"></i> Histórico de Visitas
        <span class="text-muted text-xs font-weight-normal" style="margin-left:6px;">(<?= count($visitas) ?> visita<?= count($visitas) > 1 ? 's' : '' ?>)</span>
    </h4>

    <div class="visitas-timeline">
        <?php foreach ($visitas as $visita): ?>
        <?php
            $fotos = json_decode($visita['fotos_trabalho'] ?? '[]', true) ?: [];
            $todasFotos = [];
            if (!empty($visita['foto_checkin']))  $todasFotos[] = ['path' => $visita['foto_checkin'], 'label' => 'Fachada (Check-in)'];
            foreach ($fotos as $f) $todasFotos[] = ['path' => $f, 'label' => 'Foto de trabalho'];
            if (!empty($visita['foto_checkout'])) $todasFotos[] = ['path' => $visita['foto_checkout'], 'label' => 'Check-out'];

            $statusMap = ['concluida' => ['Concluída','done'], 'em_andamento' => ['Em andamento','progress'], 'pendente' => ['Pendente','pending'], 'justificada' => ['Justificada','warning']];
            $st = $statusMap[$visita['status']] ?? ['—',''];

            $duracao = '';
            if ($visita['checkin_at'] && $visita['checkout_at']) {
                $diff = strtotime($visita['checkout_at']) - strtotime($visita['checkin_at']);
                $h = floor($diff / 3600);
                $m = floor(($diff % 3600) / 60);
                $duracao = $h > 0 ? "{$h}h {$m}min" : "{$m}min";
            }
        ?>
        <div class="visita-card">
            <div class="visita-header">
                <div class="visita-info">
                    <strong><?= e($visita['promotor_nome']) ?></strong>
                    <span class="text-muted text-sm">
                        <?= date('d/m/Y', strtotime($visita['checkin_at'])) ?>
                        &middot; <?= date('H:i', strtotime($visita['checkin_at'])) ?>
                        <?php if ($visita['checkout_at']): ?>
                            — <?= date('H:i', strtotime($visita['checkout_at'])) ?>
                        <?php endif; ?>
                    </span>
                </div>
                <div class="visita-meta">
                    <?php if ($duracao): ?>
                        <span class="visita-duracao"><i class="fa-regular fa-clock"></i> <?= $duracao ?></span>
                    <?php endif; ?>
                    <span class="status-badge status-<?= $st[1] ?>"><?= $st[0] ?></span>
                </div>
            </div>

            <?php if (!empty($todasFotos)): ?>
            <div class="fotos-grid">
                <?php foreach ($todasFotos as $foto): ?>
                <div class="foto-thumb" onclick="abrirFoto('<?= e($foto['path']) ?>', '<?= e($foto['label']) ?>')">
                    <img src="/<?= e($foto['path']) ?>" alt="<?= e($foto['label']) ?>" loading="lazy">
                    <span class="foto-label"><?= e($foto['label']) ?></span>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <?php if (!empty($visita['observacao'])): ?>
            <div class="visita-obs">
                <i class="fa-regular fa-comment"></i>
                <?= nl2br(e($visita['observacao'])) ?>
            </div>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<!-- ==========================================
     PESQUISAS RESPONDIDAS
     ========================================== -->
<?php if (!empty($respostas)): ?>
<div class="section-card">
    <h4 style="margin-bottom:var(--space-md);">
        <i class="fa-solid fa-clipboard-check"></i> Pesquisas Respondidas
        <span class="text-muted text-xs font-weight-normal" style="margin-left:6px;">(<?= count($respostas) ?>)</span>
    </h4>

    <div class="pesquisas-lista">
        <?php foreach ($respostas as $resp): ?>
        <?php
            $campos = json_decode($resp['pesquisa_campos'] ?? '[]', true) ?: [];
            $dados = json_decode($resp['dados'] ?? '{}', true) ?: [];
        ?>
        <div class="pesquisa-card">
            <div class="pesquisa-header">
                <div>
                    <strong><?= e($resp['pesquisa_titulo']) ?></strong>
                    <span class="text-muted text-sm">
                        por <?= e($resp['promotor_nome']) ?> &middot; <?= date('d/m/Y H:i', strtotime($resp['created_at'])) ?>
                    </span>
                </div>
            </div>
            <div class="pesquisa-respostas">
                <?php foreach ($campos as $campo): ?>
                <?php
                    $nome = $campo['nome'] ?? $campo['label'] ?? '';
                    $chave = $campo['name'] ?? $campo['nome'] ?? '';
                    $valor = $dados[$chave] ?? '—';
                    if (is_array($valor)) $valor = implode(', ', $valor);
                ?>
                <div class="resp-item">
                    <span class="resp-pergunta"><?= e($nome) ?></span>
                    <span class="resp-valor"><?= e($valor) ?></span>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<!-- Modal Lightbox para fotos -->
<div id="fotoModal" class="foto-modal" onclick="fecharFoto()">
    <div class="foto-modal-content" onclick="event.stopPropagation()">
        <button class="foto-modal-close" onclick="fecharFoto()">&times;</button>
        <img id="fotoModalImg" src="" alt="">
        <p id="fotoModalLabel" class="foto-modal-label"></p>
    </div>
</div>

<style>
/* === VISITAS TIMELINE === */
.visitas-timeline { display: flex; flex-direction: column; gap: var(--space-md); }

.visita-card {
    border: 1px solid var(--border);
    border-radius: var(--radius);
    padding: var(--space-md);
    transition: box-shadow 0.2s;
}
.visita-card:hover { box-shadow: 0 2px 8px rgba(0,0,0,0.06); }

.visita-header {
    display: flex; justify-content: space-between; align-items: flex-start;
    margin-bottom: var(--space-sm); flex-wrap: wrap; gap: var(--space-xs);
}
.visita-info { display: flex; flex-direction: column; gap: 2px; }
.visita-meta { display: flex; align-items: center; gap: var(--space-sm); }
.visita-duracao { font-size: var(--font-sm); color: var(--text-muted); }

.fotos-grid {
    display: grid; grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
    gap: var(--space-sm); margin-top: var(--space-sm);
}
.foto-thumb {
    position: relative; cursor: pointer; border-radius: var(--radius);
    overflow: hidden; aspect-ratio: 4/3; background: var(--gray-50);
    border: 1px solid var(--border); transition: transform 0.15s, box-shadow 0.15s;
}
.foto-thumb:hover { transform: scale(1.03); box-shadow: 0 3px 10px rgba(0,0,0,0.12); }
.foto-thumb img { width: 100%; height: 100%; object-fit: cover; }
.foto-label {
    position: absolute; bottom: 0; left: 0; right: 0;
    background: linear-gradient(transparent, rgba(0,0,0,0.7));
    color: white; font-size: 10px; padding: 12px 6px 4px; text-align: center;
}

.visita-obs {
    margin-top: var(--space-sm); padding: var(--space-sm);
    background: var(--gray-50); border-radius: var(--radius);
    font-size: var(--font-sm); color: var(--text-secondary);
    display: flex; gap: var(--space-xs); align-items: flex-start;
}
.visita-obs i { margin-top: 2px; color: var(--text-muted); }

/* === PESQUISAS === */
.pesquisas-lista { display: flex; flex-direction: column; gap: var(--space-md); }

.pesquisa-card {
    border: 1px solid var(--border);
    border-radius: var(--radius);
    overflow: hidden;
}
.pesquisa-header {
    padding: var(--space-sm) var(--space-md);
    background: var(--gray-50);
    display: flex; justify-content: space-between; align-items: center;
}
.pesquisa-header strong { display: block; margin-bottom: 2px; }

.pesquisa-respostas {
    display: grid; grid-template-columns: 1fr;
}
.resp-item {
    display: flex; justify-content: space-between; align-items: center;
    padding: var(--space-xs) var(--space-md);
    border-bottom: 1px solid var(--border);
    gap: var(--space-md);
}
.resp-item:last-child { border-bottom: none; }
.resp-pergunta { font-size: var(--font-sm); color: var(--text-muted); flex: 0 0 auto; max-width: 50%; }
.resp-valor { font-size: var(--font-sm); font-weight: 600; text-align: right; word-break: break-word; }

/* === LIGHTBOX MODAL === */
.foto-modal {
    display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%;
    background: rgba(0,0,0,0.85); z-index: 9999;
    justify-content: center; align-items: center;
}
.foto-modal.active { display: flex; }
.foto-modal-content {
    position: relative; max-width: 90vw; max-height: 90vh;
    text-align: center;
}
.foto-modal-content img {
    max-width: 100%; max-height: 85vh; border-radius: 8px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.4);
}
.foto-modal-close {
    position: absolute; top: -14px; right: -14px;
    background: white; color: #333; border: none; border-radius: 50%;
    width: 36px; height: 36px; font-size: 22px; cursor: pointer;
    box-shadow: 0 2px 8px rgba(0,0,0,0.3); z-index: 1;
    display: flex; align-items: center; justify-content: center;
}
.foto-modal-label {
    color: white; margin-top: var(--space-sm); font-size: var(--font-sm);
}
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

document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') fecharFoto();
});
</script>
