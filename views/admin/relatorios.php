<!-- Página de Relatórios -->

<!-- KPIs resumo -->
<div class="kpi-grid">
    <div class="card kpi-card blue-border">
        <div class="card-header">
            <span class="card-title">Lojas Visitadas</span>
            <div class="card-icon bg-blue"><i class="fa-solid fa-store"></i></div>
        </div>
        <div class="card-value"><?= $lojasVisitadasMes ?> / <?= $totalPdvsAtivos ?></div>
        <span class="card-stat"><?= $totalPdvsAtivos > 0 ? round(($lojasVisitadasMes / $totalPdvsAtivos) * 100) : 0 ?>% de cobertura</span>
    </div>

    <?php
    $totalMes = 0; $concluidasMes = 0;
    foreach ($visitasPorStatus as $vs) {
        $totalMes += $vs['total'];
        if ($vs['status'] === 'concluida') $concluidasMes = $vs['total'];
    }
    ?>
    <div class="card kpi-card green-border">
        <div class="card-header">
            <span class="card-title">Visitas Concluídas</span>
            <div class="card-icon bg-green"><i class="fa-solid fa-check-double"></i></div>
        </div>
        <div class="card-value"><?= number_format($concluidasMes) ?></div>
        <span class="card-stat">de <?= number_format($totalMes) ?> programadas</span>
    </div>

    <div class="card kpi-card orange-border">
        <div class="card-header">
            <span class="card-title">Não Visitadas</span>
            <div class="card-icon bg-orange"><i class="fa-solid fa-store-slash"></i></div>
        </div>
        <div class="card-value"><?= count($lojasNaoVisitadas) ?></div>
        <span class="card-stat">lojas sem visita este mês</span>
    </div>

    <div class="card kpi-card red-border">
        <div class="card-header">
            <span class="card-title">Fora do Roteiro</span>
            <div class="card-icon bg-red"><i class="fa-solid fa-triangle-exclamation"></i></div>
        </div>
        <div class="card-value"><?= count($lojasForaRoteiro) ?></div>
        <span class="card-stat">sem rota atribuída</span>
    </div>
</div>

<!-- Gráficos lado a lado -->
<div class="content-grid">
    <div class="section-card">
        <div class="section-header">
            <h3 class="section-title">Visitas por Status</h3>
        </div>
        <div class="chart-container">
            <canvas id="chartStatus"></canvas>
        </div>
    </div>

    <div class="section-card">
        <div class="section-header">
            <h3 class="section-title">Pesquisas por Mês</h3>
        </div>
        <div class="chart-container">
            <canvas id="chartPesquisas"></canvas>
        </div>
    </div>
</div>

<!-- =================================================================
     RANKING DE PROMOTORES (mais e menos visitas)
     ================================================================= -->
<div class="section-card">
    <div class="section-header">
        <h3 class="section-title"><i class="fa-solid fa-ranking-star"></i> Ranking de Promotores (<?= e($mesLabel) ?>)</h3>
        <a href="/admin/relatorios/exportar?tipo=lojas-visitadas" class="btn-outline btn-sm">
            <i class="fa-solid fa-download"></i> Exportar CSV
        </a>
    </div>
    <div class="table-container">
        <table id="tabelaRanking" data-paginate="true" data-per-page="10">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Promotor</th>
                    <th>Total Visitas</th>
                    <th>Concluídas</th>
                    <th>Pendentes</th>
                    <th>Taxa</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($visitasPorPromotor)): ?>
                    <tr><td colspan="6" class="empty-state">Sem dados para o período.</td></tr>
                <?php else: ?>
                    <?php foreach ($visitasPorPromotor as $i => $vp): ?>
                    <tr>
                        <td>
                            <?php if ($i === 0): ?>
                                <span style="font-size:18px;">🥇</span>
                            <?php elseif ($i === 1): ?>
                                <span style="font-size:18px;">🥈</span>
                            <?php elseif ($i === 2): ?>
                                <span style="font-size:18px;">🥉</span>
                            <?php else: ?>
                                <strong><?= $i + 1 ?></strong>
                            <?php endif; ?>
                        </td>
                        <td class="promoter-cell">
                            <?= avatar_html($vp['avatar'] ?? null, $vp['nome'], true) ?>
                            <strong><?= e($vp['nome']) ?></strong>
                        </td>
                        <td><?= $vp['total_visitas'] ?></td>
                        <td style="color:var(--success);"><?= $vp['concluidas'] ?></td>
                        <td style="color:var(--warning);"><?= $vp['pendentes'] ?></td>
                        <td>
                            <?php $taxa = $vp['total_visitas'] > 0 ? round(($vp['concluidas'] / $vp['total_visitas']) * 100) : 0; ?>
                            <div class="progress-bar" style="width:100px;display:inline-block;vertical-align:middle;">
                                <div class="progress-fill" style="width:<?= $taxa ?>%;background:<?= $taxa >= 80 ? 'var(--success)' : ($taxa >= 50 ? 'var(--warning)' : 'var(--danger)') ?>;"></div>
                            </div>
                            <span class="text-sm" style="margin-left:6px;"><?= $taxa ?>%</span>
                        </td>
                    </tr>
                    <?php endforeach; ?>

                    <!-- Destaque: mais e menos visitas -->
                    <?php
                        $maisVisitas = $visitasPorPromotor[0] ?? null;
                        $menosVisitas = end($visitasPorPromotor);
                        if ($menosVisitas && $maisVisitas && $maisVisitas['id'] === $menosVisitas['id']) $menosVisitas = null;
                    ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <?php if (!empty($maisVisitas) || !empty($menosVisitas)): ?>
    <div class="ranking-destaque">
        <?php if ($maisVisitas): ?>
        <div class="destaque-card destaque-top">
            <i class="fa-solid fa-arrow-up"></i>
            <div>
                <strong>Mais visitas:</strong> <?= e($maisVisitas['nome']) ?> 
                <span class="text-muted">(<?= $maisVisitas['concluidas'] ?> concluídas)</span>
            </div>
        </div>
        <?php endif; ?>
        <?php if ($menosVisitas): ?>
        <div class="destaque-card destaque-bottom">
            <i class="fa-solid fa-arrow-down"></i>
            <div>
                <strong>Menos visitas:</strong> <?= e($menosVisitas['nome']) ?> 
                <span class="text-muted">(<?= $menosVisitas['concluidas'] ?> concluídas)</span>
            </div>
        </div>
        <?php endif; ?>
    </div>
    <?php endif; ?>
</div>

<!-- =================================================================
     LOJAS NÃO VISITADAS NO MÊS
     ================================================================= -->
<div class="section-card">
    <div class="section-header">
        <h3 class="section-title"><i class="fa-solid fa-store-slash"></i> Lojas Não Visitadas (<?= e($mesLabel) ?>)</h3>
        <a href="/admin/relatorios/exportar?tipo=lojas-nao-visitadas" class="btn-outline btn-sm">
            <i class="fa-solid fa-download"></i> Exportar CSV
        </a>
    </div>
    <div class="table-container">
        <table id="tabelaNaoVisitadas" data-paginate="true" data-per-page="10">
            <thead>
                <tr>
                    <th>Nome</th>
                    <th>Código</th>
                    <th>Cidade</th>
                    <th>UF</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($lojasNaoVisitadas)): ?>
                    <tr><td colspan="4" class="empty-state">🎉 Todas as lojas foram visitadas neste mês!</td></tr>
                <?php else: ?>
                    <?php foreach ($lojasNaoVisitadas as $loja): ?>
                    <tr>
                        <td>
                            <a href="/admin/pdvs/<?= $loja['id'] ?>" style="color:var(--primary);font-weight:500;">
                                <?= e($loja['nome']) ?>
                            </a>
                        </td>
                        <td><?= e($loja['codigo'] ?? '—') ?></td>
                        <td><?= e($loja['cidade'] ?? '—') ?></td>
                        <td><?= e($loja['uf'] ?? '—') ?></td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- =================================================================
     LOJAS FORA DO ROTEIRO
     ================================================================= -->
<div class="section-card">
    <div class="section-header">
        <h3 class="section-title"><i class="fa-solid fa-triangle-exclamation"></i> Lojas Fora do Roteiro</h3>
        <a href="/admin/relatorios/exportar?tipo=lojas-fora-roteiro" class="btn-outline btn-sm">
            <i class="fa-solid fa-download"></i> Exportar CSV
        </a>
    </div>
    <p class="text-muted text-sm" style="margin-bottom:var(--space-sm);">PDVs ativos que não estão em nenhuma rota fixa de promotor.</p>
    <div class="table-container">
        <table id="tabelaForaRoteiro" data-paginate="true" data-per-page="10">
            <thead>
                <tr>
                    <th>Nome</th>
                    <th>Código</th>
                    <th>Cidade</th>
                    <th>UF</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($lojasForaRoteiro)): ?>
                    <tr><td colspan="4" class="empty-state">✅ Todos os PDVs estão em rotas.</td></tr>
                <?php else: ?>
                    <?php foreach ($lojasForaRoteiro as $loja): ?>
                    <tr>
                        <td>
                            <a href="/admin/pdvs/<?= $loja['id'] ?>" style="color:var(--primary);font-weight:500;">
                                <?= e($loja['nome']) ?>
                            </a>
                        </td>
                        <td><?= e($loja['codigo'] ?? '—') ?></td>
                        <td><?= e($loja['cidade'] ?? '—') ?></td>
                        <td><?= e($loja['uf'] ?? '—') ?></td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Top 10 PDVs -->
<div class="section-card">
    <div class="section-header">
        <h3 class="section-title"><i class="fa-solid fa-trophy"></i> Top 10 PDVs Mais Visitados (30 dias)</h3>
    </div>
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Ponto de Venda</th>
                    <th>Visitas</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($pdvsMaisVisitados)): ?>
                    <tr><td colspan="3" class="empty-state">Sem dados.</td></tr>
                <?php else: ?>
                    <?php foreach ($pdvsMaisVisitados as $i => $pdv): ?>
                    <tr>
                        <td><strong><?= $i + 1 ?></strong></td>
                        <td><?= e($pdv['nome']) ?></td>
                        <td><strong><?= $pdv['total_visitas'] ?></strong></td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<style>
/* Ranking destaque cards */
.ranking-destaque {
    display: flex; gap: var(--space-md); padding: var(--space-md);
    border-top: 1px solid var(--border); flex-wrap: wrap;
}
.destaque-card {
    display: flex; align-items: center; gap: var(--space-sm);
    padding: 8px 14px; border-radius: var(--radius); font-size: var(--font-sm);
    flex: 1; min-width: 200px;
}
.destaque-top { background: rgba(16,185,129,0.08); color: #059669; }
.destaque-top i { color: #10b981; }
.destaque-bottom { background: rgba(239,68,68,0.08); color: #dc2626; }
.destaque-bottom i { color: #ef4444; }

/* KPI extra colors */
.red-border { border-left: 4px solid #ef4444; }
.bg-red { background: rgba(239,68,68,0.15); color: #ef4444; }

/* Small button */
.btn-sm { padding: 6px 12px; font-size: 12px; }
</style>

<script>
document.addEventListener('DOMContentLoaded', () => {
    // Gráfico de status
    const statusData = <?= json_encode($visitasPorStatus) ?>;
    const statusMap = { pendente: '#f59e0b', em_andamento: '#2563eb', concluida: '#10b981', justificada: '#6b7280' };
    const statusLabels = { pendente: 'Pendente', em_andamento: 'Em Andamento', concluida: 'Concluída', justificada: 'Justificada' };

    new Chart(document.getElementById('chartStatus'), {
        type: 'doughnut',
        data: {
            labels: statusData.map(s => statusLabels[s.status] || s.status),
            datasets: [{
                data: statusData.map(s => s.total),
                backgroundColor: statusData.map(s => statusMap[s.status] || '#ccc'),
                borderWidth: 0,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { position: 'bottom' } }
        }
    });

    // Gráfico pesquisas por mês
    const pesqData = <?= json_encode($pesquisasPorMes) ?>;
    new Chart(document.getElementById('chartPesquisas'), {
        type: 'line',
        data: {
            labels: pesqData.map(p => p.mes),
            datasets: [{
                label: 'Respostas',
                data: pesqData.map(p => p.total),
                borderColor: '#2563eb',
                backgroundColor: 'rgba(37,99,235,0.1)',
                fill: true,
                tension: 0.3,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: { y: { beginAtZero: true } }
        }
    });
});
</script>
