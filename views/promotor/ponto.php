<!-- Meu Ponto — Promotor CLT -->
<?php
$entrada       = $ponto['entrada'] ?? null;
$almocoSaida   = $ponto['almoco_saida'] ?? null;
$almocoRetorno = $ponto['almoco_retorno'] ?? null;
$saida         = $ponto['saida'] ?? null;

// Determinar próximo passo
$proximoPasso = 'entrada';
if ($entrada && !$almocoSaida) $proximoPasso = 'almoco_saida';
if ($almocoSaida && !$almocoRetorno) $proximoPasso = 'almoco_retorno';
if ($almocoRetorno && !$saida) $proximoPasso = 'saida';
if ($saida) $proximoPasso = 'completo';

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
?>

<?php if ($flash): ?>
<div data-flash="<?= e($flash['msg']) ?>" data-flash-type="<?= e($flash['type']) ?>"></div>
<?php endif; ?>

<div class="section-header">
    <div>
        <h2><i class="fa-solid fa-clock"></i> Meu Ponto</h2>
        <p class="text-muted"><?= date('l, d \d\e F \d\e Y') ?></p>
    </div>
</div>

<div class="ponto-wrapper">
    <!-- Status Visual -->
    <div class="section-card ponto-status-card">
        <div class="ponto-timeline">
            <?php
            $steps = [
                ['key' => 'entrada',        'icon' => 'fa-right-to-bracket', 'label' => 'Entrada',          'time' => $entrada],
                ['key' => 'almoco_saida',   'icon' => 'fa-utensils',         'label' => 'Saída Almoço',     'time' => $almocoSaida],
                ['key' => 'almoco_retorno', 'icon' => 'fa-rotate-left',      'label' => 'Retorno Almoço',   'time' => $almocoRetorno],
                ['key' => 'saida',          'icon' => 'fa-right-from-bracket','label' => 'Saída',            'time' => $saida],
            ];
            ?>
            <?php foreach ($steps as $i => $step): ?>
            <?php
                $isDone    = !empty($step['time']);
                $isCurrent = ($step['key'] === $proximoPasso);
                $status    = $isDone ? 'done' : ($isCurrent ? 'current' : 'pending');
            ?>
            <div class="ponto-step ponto-step-<?= $status ?>">
                <div class="ponto-step-icon">
                    <?php if ($isDone): ?>
                        <i class="fa-solid fa-check"></i>
                    <?php else: ?>
                        <i class="fa-solid <?= $step['icon'] ?>"></i>
                    <?php endif; ?>
                </div>
                <div class="ponto-step-info">
                    <span class="ponto-step-label"><?= $step['label'] ?></span>
                    <?php if ($isDone): ?>
                        <span class="ponto-step-time"><?= substr($step['time'], 0, 5) ?></span>
                    <?php elseif ($isCurrent): ?>
                        <span class="ponto-step-time ponto-aguardando">Aguardando...</span>
                    <?php else: ?>
                        <span class="ponto-step-time ponto-pendente">—</span>
                    <?php endif; ?>
                </div>
            </div>
            <?php if ($i < 3): ?>
                <div class="ponto-connector <?= $isDone ? 'ponto-connector-done' : '' ?>"></div>
            <?php endif; ?>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Botão de Ação -->
    <?php if ($proximoPasso !== 'completo'): ?>
    <div class="section-card ponto-action-card">
        <?php
        $actionLabels = [
            'entrada'        => ['Registrar Entrada',           'fa-right-to-bracket', 'ponto-btn-entrada'],
            'almoco_saida'   => ['Saída para Almoço',           'fa-utensils',         'ponto-btn-almoco'],
            'almoco_retorno' => ['Retorno do Almoço',           'fa-rotate-left',      'ponto-btn-retorno'],
            'saida'          => ['Registrar Saída',             'fa-right-from-bracket','ponto-btn-saida'],
        ];
        $btn = $actionLabels[$proximoPasso];
        ?>
        <form method="POST" action="/promotor/ponto/registrar" data-confirm="Confirmar: <?= $btn[0] ?>?">
            <?= csrf_field() ?>
            <input type="hidden" name="tipo" value="<?= $proximoPasso ?>">
            <button type="submit" class="ponto-btn <?= $btn[2] ?>">
                <i class="fa-solid <?= $btn[1] ?>"></i>
                <span><?= $btn[0] ?></span>
                <small><?= date('H:i') ?></small>
            </button>
        </form>
    </div>
    <?php else: ?>
    <div class="section-card ponto-complete-card">
        <i class="fa-solid fa-circle-check"></i>
        <h3>Ponto completo!</h3>
        <p class="text-muted">Todos os horários do dia foram registrados.</p>
    </div>
    <?php endif; ?>

    <!-- Horas trabalhadas -->
    <?php if ($entrada && $saida): ?>
    <?php
        $totalSeg = strtotime($saida) - strtotime($entrada);
        if ($almocoSaida && $almocoRetorno) {
            $totalSeg -= (strtotime($almocoRetorno) - strtotime($almocoSaida));
        }
        $horas = floor($totalSeg / 3600);
        $minutos = floor(($totalSeg % 3600) / 60);
    ?>
    <div class="section-card ponto-resume-card">
        <div class="ponto-resume-item">
            <i class="fa-regular fa-clock"></i>
            <div>
                <small class="text-muted">Total trabalhado</small>
                <strong><?= $horas ?>h <?= $minutos ?>min</strong>
            </div>
        </div>
        <?php if ($almocoSaida && $almocoRetorno): ?>
        <?php $almSeg = strtotime($almocoRetorno) - strtotime($almocoSaida); ?>
        <div class="ponto-resume-item">
            <i class="fa-solid fa-utensils"></i>
            <div>
                <small class="text-muted">Intervalo almoço</small>
                <strong><?= floor($almSeg / 3600) ?>h <?= floor(($almSeg % 3600) / 60) ?>min</strong>
            </div>
        </div>
        <?php endif; ?>
    </div>
    <?php endif; ?>
</div>

<style>
.ponto-wrapper {
    max-width: 600px;
    margin: 0 auto;
}

/* Timeline */
.ponto-timeline {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0;
    padding: var(--space-lg) var(--space-md);
}

.ponto-step {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 8px;
    min-width: 80px;
}

.ponto-step-icon {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
    transition: all 0.3s;
}

.ponto-step-done .ponto-step-icon {
    background: linear-gradient(135deg, #059669, #047857);
    color: #fff;
    box-shadow: 0 4px 12px rgba(5, 150, 105, 0.3);
}

.ponto-step-current .ponto-step-icon {
    background: linear-gradient(135deg, #2563eb, #1d4ed8);
    color: #fff;
    box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
    animation: ponto-pulse 2s infinite;
}

.ponto-step-pending .ponto-step-icon {
    background: var(--gray-100);
    color: var(--gray-400);
}

.ponto-step-label {
    font-size: 11px;
    font-weight: 600;
    color: var(--text-secondary);
    text-align: center;
}

.ponto-step-time {
    font-size: 14px;
    font-weight: 700;
    color: var(--text-primary);
}

.ponto-aguardando {
    color: var(--primary);
    font-size: 12px;
}

.ponto-pendente {
    color: var(--gray-300);
}

.ponto-connector {
    width: 40px;
    height: 3px;
    background: var(--gray-200);
    border-radius: 2px;
    margin-bottom: 36px;
}

.ponto-connector-done {
    background: linear-gradient(90deg, #059669, #10b981);
}

/* Botão principal */
.ponto-action-card {
    text-align: center;
    padding: var(--space-lg);
}

.ponto-btn {
    display: inline-flex;
    flex-direction: column;
    align-items: center;
    gap: 8px;
    padding: 24px 48px;
    border: none;
    border-radius: 16px;
    cursor: pointer;
    font-size: 16px;
    font-weight: 700;
    color: #fff;
    transition: all 0.3s;
    min-width: 200px;
}

.ponto-btn i {
    font-size: 28px;
}

.ponto-btn small {
    font-size: 24px;
    font-weight: 300;
    opacity: 0.9;
}

.ponto-btn-entrada {
    background: linear-gradient(135deg, #059669, #047857);
    box-shadow: 0 8px 24px rgba(5, 150, 105, 0.35);
}

.ponto-btn-entrada:hover {
    transform: translateY(-2px);
    box-shadow: 0 12px 32px rgba(5, 150, 105, 0.45);
}

.ponto-btn-almoco {
    background: linear-gradient(135deg, #d97706, #b45309);
    box-shadow: 0 8px 24px rgba(217, 119, 6, 0.35);
}

.ponto-btn-almoco:hover {
    transform: translateY(-2px);
    box-shadow: 0 12px 32px rgba(217, 119, 6, 0.45);
}

.ponto-btn-retorno {
    background: linear-gradient(135deg, #2563eb, #1d4ed8);
    box-shadow: 0 8px 24px rgba(37, 99, 235, 0.35);
}

.ponto-btn-retorno:hover {
    transform: translateY(-2px);
    box-shadow: 0 12px 32px rgba(37, 99, 235, 0.45);
}

.ponto-btn-saida {
    background: linear-gradient(135deg, #dc2626, #b91c1c);
    box-shadow: 0 8px 24px rgba(220, 38, 38, 0.35);
}

.ponto-btn-saida:hover {
    transform: translateY(-2px);
    box-shadow: 0 12px 32px rgba(220, 38, 38, 0.45);
}

/* Completo */
.ponto-complete-card {
    text-align: center;
    padding: var(--space-xl);
}

.ponto-complete-card i {
    font-size: 48px;
    color: #059669;
    margin-bottom: var(--space-md);
}

.ponto-complete-card h3 {
    color: #059669;
    margin-bottom: var(--space-xs);
}

/* Resumo */
.ponto-resume-card {
    display: flex;
    gap: var(--space-lg);
    justify-content: center;
    padding: var(--space-lg);
}

.ponto-resume-item {
    display: flex;
    align-items: center;
    gap: var(--space-sm);
}

.ponto-resume-item i {
    font-size: 20px;
    color: var(--primary);
}

.ponto-resume-item small {
    display: block;
}

/* Animation */
@keyframes ponto-pulse {
    0%, 100% { box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3); }
    50% { box-shadow: 0 4px 24px rgba(37, 99, 235, 0.6); }
}

/* Responsive */
@media (max-width: 480px) {
    .ponto-timeline { flex-wrap: wrap; gap: var(--space-sm); }
    .ponto-connector { display: none; }
    .ponto-step { flex-direction: row; min-width: auto; width: 100%; gap: 12px; }
    .ponto-step-info { text-align: left; }
    .ponto-resume-card { flex-direction: column; }
}
</style>

<script>
// ---- Ponto: interceptação offline ----
document.addEventListener('DOMContentLoaded', () => {
    const pontoForm = document.querySelector('.ponto-action-card form');
    if (!pontoForm) return;

    // Remove o data-confirm handler original para adicionar o nosso
    const originalConfirmMsg = pontoForm.dataset.confirm;

    pontoForm.addEventListener('submit', async function(e) {
        // Se offline, intercepta
        if (!navigator.onLine) {
            e.preventDefault();
            // Não precisa de confirmação se já confirmou via data-confirm
            
            const tipo = this.querySelector('[name="tipo"]').value;
            const labels = {
                entrada: 'Entrada',
                almoco_saida: 'Saída para almoço',
                almoco_retorno: 'Retorno do almoço',
                saida: 'Saída',
            };

            try {
                const enqueued = await OfflineSync.interceptIfOffline(
                    'ponto',
                    this.action,
                    this,
                    { tipo_label: labels[tipo] || tipo, hora: new Date().toLocaleTimeString('pt-BR', {hour:'2-digit', minute:'2-digit'}) }
                );
                if (enqueued) {
                    // Atualiza visualmente a tela para mostrar que foi registrado
                    const btn = this.querySelector('.ponto-btn');
                    if (btn) {
                        btn.innerHTML = '<i class="fa-solid fa-cloud-arrow-up"></i><span>Pendente de Sincronização</span><small>' + new Date().toLocaleTimeString('pt-BR', {hour:'2-digit', minute:'2-digit'}) + '</small>';
                        btn.disabled = true;
                        btn.style.opacity = '0.7';
                    }
                }
            } catch (err) {
                console.error('Erro ao enfileirar ponto offline:', err);
            }
        }
        // Se online, o fluxo normal (data-confirm → submit) continua
    });
});
</script>

<!-- Cache ponto para uso offline -->
<script>
localStorage.setItem('griffus_ponto', JSON.stringify({
    proximoPasso: '<?= $proximoPasso ?>',
}));
localStorage.setItem('griffus_csrf', document.querySelector('[name="_token"]')?.value || '<?= csrf_token() ?>');
</script>


