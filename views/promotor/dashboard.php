<!-- Dashboard Promotor — Mobile-First com Mapa Leaflet -->

<!-- Leaflet CSS/JS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<div class="promotor-welcome">
    <h3>Olá, <?= e(Auth::user()['nome']) ?>! 👋</h3>
    <p>Confira sua agenda de hoje.</p>
</div>

<!-- Mapa com PDVs do dia -->
<div class="section-card" style="padding:0; overflow:hidden; border-radius:var(--radius-lg);">
    <div id="mapaPromotor" style="height:280px; width:100%;"></div>
</div>

<!-- Contadores rápidos -->
<?php
$totalVisitas = count($visitasHoje);
$concluidas = count(array_filter($visitasHoje, fn($v) => $v['status'] === 'concluida'));
$pendentes  = count(array_filter($visitasHoje, fn($v) => $v['status'] === 'pendente'));
$emAndamento = count(array_filter($visitasHoje, fn($v) => $v['status'] === 'em_andamento'));
$progresso = $totalVisitas > 0 ? round(($concluidas / $totalVisitas) * 100) : 0;
?>

<div class="kpi-grid compact">
    <div class="card kpi-card blue-border">
        <div class="card-header">
            <span class="card-title">Visitas</span>
            <div class="card-icon bg-blue"><i class="fa-solid fa-location-dot"></i></div>
        </div>
        <div class="card-value"><?= $totalVisitas ?></div>
    </div>
    <div class="card kpi-card green-border">
        <div class="card-header">
            <span class="card-title">Concluídas</span>
            <div class="card-icon bg-green"><i class="fa-solid fa-check"></i></div>
        </div>
        <div class="card-value"><?= $concluidas ?>/<?= $totalVisitas ?></div>
    </div>
</div>

<!-- Barra de progresso -->
<?php if ($totalVisitas > 0): ?>
<div class="section-card" style="padding:var(--space-sm) var(--space-md);">
    <div style="display:flex; align-items:center; gap:var(--space-sm);">
        <span class="text-sm text-muted">Progresso</span>
        <div style="flex:1; background:var(--gray-100); border-radius:20px; height:8px; overflow:hidden;">
            <div style="width:<?= $progresso ?>%; height:100%; background:var(--success); border-radius:20px; transition:width 0.3s;"></div>
        </div>
        <span class="text-sm" style="font-weight:600;"><?= $progresso ?>%</span>
    </div>
</div>
<?php endif; ?>

<!-- Lista de visitas do dia -->
<div class="section-card">
    <div class="section-header">
        <h3 class="section-title">Rota de Hoje</h3>
    </div>

    <?php if (empty($visitasHoje)): ?>
        <div class="empty-state-card">
            <i class="fa-regular fa-calendar-check"></i>
            <p>Nenhuma visita programada para hoje.</p>
        </div>
    <?php else: ?>
        <div class="visit-list">
            <?php 
            $proximoPendente = true;
            foreach ($visitasHoje as $idx => $v): 
                $isConcluida = $v['status'] === 'concluida';
                $isEmAndamento = $v['status'] === 'em_andamento';
                $isPendente = $v['status'] === 'pendente';
                $isProximo = $isPendente && $proximoPendente;
                if ($isProximo) $proximoPendente = false;
            ?>
            <div class="visit-card-v2 <?= $isConcluida ? 'done' : '' ?> <?= $isEmAndamento ? 'active' : '' ?> <?= $isProximo ? 'next' : '' ?>">
                <div class="visit-num">
                    <?php if ($isConcluida): ?>
                        <i class="fa-solid fa-check-circle" style="color:var(--success);"></i>
                    <?php elseif ($isEmAndamento): ?>
                        <i class="fa-solid fa-spinner fa-spin" style="color:var(--primary);"></i>
                    <?php else: ?>
                        <span><?= $idx + 1 ?></span>
                    <?php endif; ?>
                </div>

                <div class="visit-body">
                    <h4><?= e($v['pdv_nome']) ?></h4>
                    <p class="visit-meta text-muted text-sm">
                        <i class="fa-solid fa-location-dot"></i>
                        <?= e($v['pdv_endereco'] ?? $v['pdv_cidade'] ?? 'Endereço não cadastrado') ?>
                    </p>
                    <?php if ($isConcluida && $v['checkin_at']): ?>
                        <p class="text-xs text-muted">
                            <i class="fa-regular fa-clock"></i>
                            <?= date('H:i', strtotime($v['checkin_at'])) ?> — <?= date('H:i', strtotime($v['checkout_at'])) ?>
                        </p>
                    <?php endif; ?>
                </div>

                <div class="visit-actions-v2">
                    <?php if ($isProximo): ?>
                        <!-- Botão navegar no mapa -->
                        <?php if (!empty($v['pdv_lat']) && !empty($v['pdv_lng'])): ?>
                        <a href="https://www.google.com/maps/dir/?api=1&destination=<?= $v['pdv_lat'] ?>,<?= $v['pdv_lng'] ?>&travelmode=driving"
                           target="_blank" class="btn-icon btn-nav" title="Navegar">
                            <i class="fa-solid fa-diamond-turn-right"></i>
                        </a>
                        <?php endif; ?>
                        <a href="/promotor/checkin/<?= $v['id'] ?>" class="btn-sm btn-primary">
                            <i class="fa-solid fa-right-to-bracket"></i> Check-in
                        </a>
                    <?php elseif ($isEmAndamento): ?>
                        <a href="/promotor/visita/<?= $v['id'] ?>" class="btn-sm btn-primary">
                            <i class="fa-solid fa-arrow-right"></i> Continuar
                        </a>
                    <?php elseif ($isConcluida): ?>
                        <span class="status-badge status-done">Concluída</span>
                    <?php else: ?>
                        <span class="status-badge status-pending" style="opacity:0.5;">Aguardando</span>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Próximos dias da semana -->
<div class="section-card">
    <div class="section-header">
        <h3 class="section-title">Próximos Dias</h3>
        <a href="/promotor/rotas" class="link-action">Ver Completa</a>
    </div>

    <?php
    $hoje = date('Y-m-d');
    $temProximos = false;
    foreach ($semanaAtual as $dia):
        if ($dia['data'] <= $hoje) continue;
        if (empty($dia['pdvs'])) continue;
        $temProximos = true;
    ?>
    <div style="margin-bottom: var(--space-sm);">
        <strong style="font-size:var(--font-sm); color:var(--text-muted);">
            <?= $dia['dia_nome'] ?> (<?= date('d/m', strtotime($dia['data'])) ?>)
        </strong>
        <div style="display:flex; gap:6px; flex-wrap:wrap; margin-top:4px;">
            <?php foreach ($dia['pdvs'] as $pdv): ?>
                <span class="status-badge status-done" style="font-size:11px;">
                    <?= e($pdv['nome']) ?>
                </span>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endforeach; ?>
    <?php if (!$temProximos): ?>
        <p class="text-muted text-sm" style="text-align:center; padding:var(--space-md);">
            Nenhuma visita nos próximos dias.
        </p>
    <?php endif; ?>
</div>

<style>
/* --- Visit Cards V2 (sequencial) --- */
.visit-card-v2 {
    display: flex;
    align-items: center;
    gap: var(--space-md);
    padding: var(--space-md);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    margin-bottom: var(--space-xs);
    transition: all 0.2s;
    background: var(--white);
}
.visit-card-v2.done {
    opacity: 0.6;
    background: var(--gray-50);
}
.visit-card-v2.active {
    border-color: var(--primary);
    box-shadow: 0 0 0 2px rgba(99,102,241,0.15);
}
.visit-card-v2.next {
    border-color: var(--success);
    box-shadow: 0 0 0 2px rgba(16,185,129,0.15);
}

.visit-num {
    width: 36px; height: 36px;
    border-radius: 50%;
    background: var(--gray-100);
    display: flex; align-items: center; justify-content: center;
    font-weight: 700; font-size: 14px; color: var(--text-muted);
    flex-shrink: 0;
}
.visit-card-v2.next .visit-num {
    background: var(--success);
    color: white;
}
.visit-card-v2.active .visit-num {
    background: var(--primary-light);
}

.visit-body { flex: 1; min-width: 0; }
.visit-body h4 { 
    font-size: var(--font-sm); margin: 0 0 2px;
    white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
}
.visit-body .visit-meta {
    white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
}

.visit-actions-v2 {
    display: flex; align-items: center; gap: var(--space-xs);
    flex-shrink: 0;
}

/* Botão navegação */
.btn-nav {
    width: 36px; height: 36px;
    border-radius: 50%;
    background: var(--blue-50, #eff6ff);
    color: var(--primary);
    display: flex; align-items: center; justify-content: center;
    font-size: 16px;
    transition: all 0.2s;
    border: none; cursor: pointer;
}
.btn-nav:hover {
    background: var(--primary);
    color: white;
}
</style>

<script>
// ---- Mapa Leaflet ----
document.addEventListener('DOMContentLoaded', () => {
    const visitas = <?= json_encode(array_map(fn($v) => [
        'id'     => $v['id'],
        'nome'   => $v['pdv_nome'],
        'lat'    => (float) ($v['pdv_lat'] ?? 0),
        'lng'    => (float) ($v['pdv_lng'] ?? 0),
        'status' => $v['status'],
        'endereco' => $v['pdv_endereco'] ?? '',
    ], $visitasHoje)) ?>;

    const mapEl = document.getElementById('mapaPromotor');
    if (!mapEl) return;

    // Filtra só os que tem coordenadas
    const comCoord = visitas.filter(v => v.lat && v.lng);

    if (comCoord.length === 0) {
        mapEl.innerHTML = '<div style="display:flex;align-items:center;justify-content:center;height:100%;color:var(--text-muted);"><i class="fa-solid fa-map-location-dot" style="font-size:40px;opacity:0.2;margin-right:12px;"></i><span>Nenhum PDV com coordenadas</span></div>';
        return;
    }

    const map = L.map('mapaPromotor', { zoomControl: false });
    L.control.zoom({ position: 'topright' }).addTo(map);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap',
        maxZoom: 18,
    }).addTo(map);

    const statusColors = {
        'pendente':      '#9CA3AF',
        'em_andamento':  '#6366F1',
        'concluida':     '#10B981',
    };

    const bounds = [];
    comCoord.forEach((v, i) => {
        const color = statusColors[v.status] || '#9CA3AF';
        const icon = L.divIcon({
            className: 'map-marker-custom',
            html: `<div style="
                background:${color}; color:white; width:28px; height:28px;
                border-radius:50%; display:flex; align-items:center; justify-content:center;
                font-weight:700; font-size:12px; border:3px solid white;
                box-shadow:0 2px 6px rgba(0,0,0,0.3);
            ">${i + 1}</div>`,
            iconSize: [28, 28],
            iconAnchor: [14, 14],
        });

        L.marker([v.lat, v.lng], { icon }).addTo(map)
            .bindPopup(`<strong>${v.nome}</strong><br><small>${v.endereco}</small>`);

        bounds.push([v.lat, v.lng]);
    });

    if (bounds.length === 1) {
        map.setView(bounds[0], 15);
    } else {
        map.fitBounds(bounds, { padding: [30, 30] });
    }

    // Adicionar posição atual do promotor
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(pos => {
            const { latitude, longitude } = pos.coords;
            const myIcon = L.divIcon({
                className: 'map-marker-custom',
                html: `<div style="
                    background:#3B82F6; color:white; width:22px; height:22px;
                    border-radius:50%; border:3px solid white;
                    box-shadow:0 0 0 6px rgba(59,130,246,0.25);
                "></div>`,
                iconSize: [22, 22],
                iconAnchor: [11, 11],
            });
            L.marker([latitude, longitude], { icon: myIcon }).addTo(map)
                .bindPopup('📍 Minha posição');
        }, () => {}, { enableHighAccuracy: true, timeout: 5000 });
    }
});
</script>

<!-- Cache de dados para modo offline -->
<script>
document.addEventListener('DOMContentLoaded', () => {
    // Salva dados das visitas no localStorage para o modo offline
    const visitasData = <?= json_encode(array_map(fn($v) => [
        'id'           => $v['id'],
        'pdv_nome'     => $v['pdv_nome'],
        'pdv_endereco' => $v['pdv_endereco'] ?? $v['pdv_cidade'] ?? '',
        'pdv_lat'      => (float) ($v['pdv_lat'] ?? 0),
        'pdv_lng'      => (float) ($v['pdv_lng'] ?? 0),
        'status'       => $v['status'],
        'checkin_at'   => $v['checkin_at'] ?? null,
        'checkout_at'  => $v['checkout_at'] ?? null,
    ], $visitasHoje)) ?>;

    localStorage.setItem('griffus_visits_today', JSON.stringify({
        date: new Date().toISOString().split('T')[0],
        visits: visitasData,
    }));

    // Salva nome do usuário e CSRF para uso offline
    localStorage.setItem('griffus_user_name', <?= json_encode(Auth::user()['nome'] ?? '') ?>);
    localStorage.setItem('griffus_csrf', document.querySelector('[name="_token"]')?.value || '<?= csrf_token() ?>');
});
</script>

