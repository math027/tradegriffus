<!-- KPI Cards -->
<div class="kpi-grid">
    <div class="card kpi-card blue-border">
        <div class="card-header">
            <span class="card-title">Visitas Hoje</span>
            <div class="card-icon bg-blue"><i class="fa-solid fa-location-dot"></i></div>
        </div>
        <div class="card-value"><?= number_format($visitasHoje) ?></div>
        <span class="card-stat">Programadas para hoje</span>
    </div>

    <div class="card kpi-card green-border">
        <div class="card-header">
            <span class="card-title">Promotores Ativos</span>
            <div class="card-icon bg-green"><i class="fa-solid fa-users"></i></div>
        </div>
        <div class="card-value"><?= number_format($totalPromotores) ?></div>
        <span class="card-stat">Cadastrados</span>
    </div>

    <div class="card kpi-card orange-border">
        <div class="card-header">
            <span class="card-title">Total PDVs</span>
            <div class="card-icon bg-orange"><i class="fa-solid fa-store"></i></div>
        </div>
        <div class="card-value"><?= number_format($totalPdvs) ?></div>
        <span class="card-stat">Pontos ativos</span>
    </div>

    <div class="card kpi-card red-border">
        <div class="card-header">
            <span class="card-title">Pesquisas</span>
            <div class="card-icon bg-red"><i class="fa-solid fa-clipboard-check"></i></div>
        </div>
        <div class="card-value"><?= number_format($pesquisasRealizadas) ?></div>
        <span class="card-stat">Respostas coletadas</span>
    </div>
</div>

<!-- Gráfico + Activity Feed -->
<div class="content-grid">
    <div class="section-card">
        <div class="section-header">
            <h3 class="section-title">Produtividade da Equipe (Visitas x Dia)</h3>
            <a href="/admin/relatorios" class="link-action">Ver Relatório Completo</a>
        </div>
        <div class="chart-container">
            <canvas id="visitsChart"></canvas>
        </div>
    </div>

    <div class="section-card">
        <div class="section-header">
            <h3 class="section-title">Últimos Check-ins</h3>
        </div>
        <div class="activity-list">
            <?php if (empty($ultimosCheckins)): ?>
                <p class="empty-state">Nenhum check-in registrado ainda.</p>
            <?php else: ?>
                <?php foreach ($ultimosCheckins as $checkin): ?>
                <div class="activity-item">
                    <?= avatar_html($checkin['promotor_avatar'] ?? null, $checkin['promotor_nome']) ?>
                    <div class="activity-info">
                        <h5><?= e($checkin['promotor_nome']) ?></h5>
                        <p>Check-in em <?= e($checkin['pdv_nome']) ?></p>
                    </div>
                    <span class="activity-time"><?= hora_br($checkin['checkin_at']) ?></span>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Tabela de visitas recentes -->
<div class="section-card">
    <div class="section-header">
        <h3 class="section-title">Visitas de Hoje</h3>
    </div>
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Promotor</th>
                    <th>PDV / Cliente</th>
                    <th>Entrada</th>
                    <th>Saída</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($visitasRecentes)): ?>
                    <tr><td colspan="5" class="empty-state">Nenhuma visita programada para hoje.</td></tr>
                <?php else: ?>
                    <?php foreach ($visitasRecentes as $v): ?>
                    <tr>
                        <td class="promoter-cell">
                            <?= avatar_html($v['promotor_avatar'] ?? null, $v['promotor_nome'], true) ?>
                            <?= e($v['promotor_nome']) ?>
                        </td>
                        <td><?= e($v['pdv_nome']) ?></td>
                        <td><?= hora_br($v['checkin_at'] ?? '') ?></td>
                        <td><?= hora_br($v['checkout_at'] ?? '') ?></td>
                        <td><span class="status-badge <?= status_class($v['status']) ?>"><?= status_label($v['status']) ?></span></td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
// Carrega dados do gráfico via API
document.addEventListener('DOMContentLoaded', async () => {
    try {
        const res = await fetch('/api/dashboard/stats');
        const data = await res.json();

        const ctx = document.getElementById('visitsChart').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: data.visitasPorDia.map(d => {
                    const dt = new Date(d.dia + 'T00:00:00');
                    return dt.toLocaleDateString('pt-BR', { weekday: 'short', day: 'numeric' });
                }),
                datasets: [{
                    label: 'Visitas',
                    data: data.visitasPorDia.map(d => d.total),
                    backgroundColor: 'rgba(37, 99, 235, 0.8)',
                    borderRadius: 6,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, ticks: { stepSize: 1 } }
                }
            }
        });
    } catch (e) {
        // Sem dados para o gráfico
    }
});
</script>
