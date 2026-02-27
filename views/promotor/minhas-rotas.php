<!-- Minhas Rotas — Promotor (semana atual) com status de visitas -->
<div class="promotor-welcome">
    <h3>📍 Minhas Rotas</h3>
    <p>Sua programação semanal de visitas</p>
</div>

<!-- Navegação da semana -->
<div style="display:flex; align-items:center; justify-content:center; gap:var(--space-md); margin-bottom:var(--space-lg);">
    <a href="/promotor/rotas?data=<?= date('Y-m-d', strtotime($segunda . ' -7 days')) ?>" class="btn-icon">
        <i class="fa-solid fa-chevron-left"></i>
    </a>
    <span style="font-weight:600;">
        <?= date('d/m', strtotime($segunda)) ?> — <?= date('d/m/Y', strtotime($segunda . ' +6 days')) ?>
    </span>
    <a href="/promotor/rotas?data=<?= date('Y-m-d', strtotime($segunda . ' +7 days')) ?>" class="btn-icon">
        <i class="fa-solid fa-chevron-right"></i>
    </a>
</div>

<?php
$hoje = date('Y-m-d');
foreach ($semana as $dia):
    $isHoje = $dia['data'] === $hoje;
    $isPast = $dia['data'] < $hoje;
?>
<div class="section-card <?= $isHoje ? 'dia-hoje' : '' ?>" style="<?= $isPast ? 'opacity:0.6;' : '' ?>">
    <div class="section-header">
        <div>
            <h4>
                <?= $dia['dia_nome'] ?>
                <span class="text-muted text-sm" style="font-weight:400; margin-left:var(--space-xs);">
                    <?= date('d/m', strtotime($dia['data'])) ?>
                </span>
                <?php if ($isHoje): ?>
                    <span class="status-badge status-progress" style="margin-left:var(--space-sm);">HOJE</span>
                <?php endif; ?>
            </h4>
        </div>
        <span class="text-muted text-sm"><?= count($dia['pdvs']) ?> PDV(s)</span>
    </div>

    <?php if (empty($dia['pdvs'])): ?>
        <p class="text-muted text-sm" style="text-align:center; padding:var(--space-md);">
            <i class="fa-solid fa-coffee"></i> Sem visitas programadas
        </p>
    <?php else: ?>
        <div class="visit-list">
            <?php foreach ($dia['pdvs'] as $idx => $pdv): 
                // Cruzar com visitas de hoje para mostrar status
                $visita = $visitasPorPdv[$pdv['id']] ?? null;
                $statusVisita = $visita ? $visita['status'] : null;
                $isConcluida = $statusVisita === 'concluida';
                $isEmAndamento = $statusVisita === 'em_andamento';
            ?>
            <div class="visit-card <?= $isConcluida ? 'visit-done' : '' ?> <?= $isEmAndamento ? 'visit-active' : '' ?>">
                <div class="visit-info">
                    <h4>
                        <span style="color:var(--primary); margin-right:6px;"><?= $idx + 1 ?>.</span>
                        <?= e($pdv['nome']) ?>
                        <?php if ($isConcluida): ?>
                            <i class="fa-solid fa-check-circle" style="color:var(--success); margin-left:6px;"></i>
                        <?php elseif ($isEmAndamento): ?>
                            <i class="fa-solid fa-spinner fa-spin" style="color:var(--primary); margin-left:6px;"></i>
                        <?php endif; ?>
                    </h4>
                    <div class="visit-meta">
                        <i class="fa-solid fa-location-dot"></i>
                        <?= e($pdv['endereco'] ?? 'Endereço não cadastrado') ?>
                        <?php if ($pdv['origem'] === 'temporario'): ?>
                            <span class="status-badge status-pending" style="font-size:9px; margin-left:8px;">TEMP</span>
                        <?php endif; ?>
                    </div>
                    <?php if ($isConcluida && $visita['checkin_at']): ?>
                        <span class="text-xs text-muted">
                            <i class="fa-regular fa-clock"></i>
                            <?= date('H:i', strtotime($visita['checkin_at'])) ?> — <?= date('H:i', strtotime($visita['checkout_at'])) ?>
                        </span>
                    <?php endif; ?>
                </div>
                <?php if ($isHoje && !$isPast): ?>
                <div class="visit-actions">
                    <?php if ($isConcluida): ?>
                        <span class="status-badge status-done">Concluída</span>
                    <?php elseif ($isEmAndamento): ?>
                        <a href="/promotor/visita/<?= $visita['id'] ?>" class="btn-primary btn-sm">
                            <i class="fa-solid fa-arrow-right"></i> Continuar
                        </a>
                    <?php elseif ($visita): ?>
                        <a href="/promotor/checkin/<?= $visita['id'] ?>" class="btn-success btn-sm">
                            <i class="fa-solid fa-location-crosshairs"></i> Check-in
                        </a>
                    <?php else: ?>
                        <span class="status-badge status-pending" style="opacity:0.5;">Aguardando</span>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
<?php endforeach; ?>

<style>
.dia-hoje { border-left: 4px solid var(--primary); }
.visit-done { opacity: 0.6; background: var(--gray-50); }
.visit-active { border-left: 3px solid var(--primary); }
</style>
