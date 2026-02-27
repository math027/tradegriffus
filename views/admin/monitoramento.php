<!-- Monitoramento em Tempo Real -->
<div style="display:flex; justify-content:flex-end; margin-bottom:var(--space-md);">
    <button class="btn-outline" onclick="location.reload()">
        <i class="fa-solid fa-arrows-rotate"></i> Atualizar
    </button>
</div>

<!-- KPI Cards -->
<div class="kpi-grid">
    <div class="card kpi-card blue-border">
        <div class="card-header">
            <span class="card-title">Visitas do Dia</span>
            <div class="card-icon bg-blue"><i class="fa-solid fa-location-dot"></i></div>
        </div>
        <div class="card-value"><?= $totais['concluidas'] + $totais['em_andamento'] ?> / <?= $totais['total_pdvs'] ?></div>
        <span class="card-stat">Realizadas / Programadas</span>
    </div>

    <div class="card kpi-card orange-border">
        <div class="card-header">
            <span class="card-title">Pendentes</span>
            <div class="card-icon bg-orange"><i class="fa-solid fa-clock"></i></div>
        </div>
        <div class="card-value"><?= $totais['pendentes'] ?></div>
        <span class="card-stat">Aguardando visita</span>
    </div>

    <div class="card kpi-card green-border">
        <div class="card-header">
            <span class="card-title">Concluídas</span>
            <div class="card-icon bg-green"><i class="fa-solid fa-check-double"></i></div>
        </div>
        <div class="card-value"><?= $totais['concluidas'] ?></div>
        <span class="card-stat">Visitas finalizadas</span>
    </div>

    <div class="card kpi-card purple-border">
        <div class="card-header">
            <span class="card-title">Progresso</span>
            <div class="card-icon bg-purple"><i class="fa-solid fa-percent"></i></div>
        </div>
        <div class="card-value"><?= $totais['progresso'] ?>%</div>
        <span class="card-stat">Dia / Em andamento</span>
    </div>
</div>

<!-- Tabela de Monitoramento -->
<div class="section-card">
    <div class="section-header">
        <h3 class="section-title">Promotores</h3>
        <input type="text" placeholder="Buscar promotor..." class="search-input" id="searchMonitor">
    </div>

    <div class="table-container">
        <table id="tabelaMonitor" class="monitor-table">
            <thead>
                <tr>
                    <th style="width:280px;">Dados Gerais</th>
                    <th style="width:180px;">Destino</th>
                    <th>Situação das Visitas</th>
                    <th style="width:70px; text-align:center;">%</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($monitores)): ?>
                    <tr><td colspan="4" class="empty-state">Nenhum promotor com rota hoje.</td></tr>
                <?php else: ?>
                    <?php foreach ($monitores as $mIdx => $m): 
                        $pr = $m['promotor'];
                        $sc = $m['status_counts'];
                        // Recolhe observações dos PDVs
                        $observacoes = [];
                        foreach ($m['pdvs'] as $pdv) {
                            if (!empty($pdv['observacao'])) {
                                $observacoes[] = ['pdv' => $pdv['nome'], 'texto' => $pdv['observacao']];
                            }
                        }
                        $temDetalhes = !empty($observacoes) || !empty($m['respostas']);
                    ?>
                    <tr class="<?= $temDetalhes ? 'has-details' : '' ?>" <?= $temDetalhes ? "onclick=\"toggleDetalhes({$mIdx})\"" : '' ?> style="<?= $temDetalhes ? 'cursor:pointer;' : '' ?>">
                        <!-- Dados Gerais -->
                        <td class="monitor-dados">
                            <div class="monitor-nome">
                                <?= avatar_html($pr['avatar'] ?? null, $pr['nome']) ?>
                                <div>
                                    <strong><?= e($pr['nome']) ?></strong>
                                    <span class="monitor-conexao text-muted text-xs">
                                        <?php if ($m['ultima_conexao']): ?>
                                            <i class="fa-solid fa-wifi" style="color:var(--success);"></i>
                                            Última conexão: <?= hora_br($m['ultima_conexao']) ?>
                                        <?php else: ?>
                                            <i class="fa-solid fa-wifi" style="color:var(--gray-300);"></i>
                                            Sem atividade hoje
                                        <?php endif; ?>
                                    </span>
                                </div>
                            </div>
                            <!-- Mini contadores -->
                            <div class="monitor-counters">
                                <span class="mc mc-green" title="Concluídas">
                                    <i class="fa-solid fa-check"></i> <?= $sc['concluida'] ?>
                                </span>
                                <span class="mc mc-orange" title="Em andamento">
                                    <i class="fa-solid fa-location-dot"></i> <?= $sc['em_andamento'] ?>
                                </span>
                                <span class="mc mc-gray" title="Pendentes">
                                    <i class="fa-regular fa-clock"></i> <?= $sc['pendente'] ?>
                                </span>
                                <span class="mc mc-total" title="Total de PDVs">
                                    <i class="fa-solid fa-store"></i> <?= $m['total_pdvs'] ?>
                                </span>
                                <?php if (!empty($observacoes)): ?>
                                <span class="mc mc-obs" title="Observações">
                                    <i class="fa-regular fa-comment"></i> <?= count($observacoes) ?>
                                </span>
                                <?php endif; ?>
                                <?php if (!empty($m['respostas'])): ?>
                                <span class="mc mc-pesq" title="Pesquisas respondidas">
                                    <i class="fa-solid fa-clipboard-check"></i> <?= count($m['respostas']) ?>
                                </span>
                                <?php endif; ?>
                            </div>
                        </td>

                        <!-- Destino -->
                        <td class="monitor-destino">
                            <?php if ($m['destino']): ?>
                                <div class="destino-card">
                                    <?php if (($m['destino']['status'] ?? '') === 'em_andamento'): ?>
                                        <span class="destino-badge badge-orange">
                                            <i class="fa-solid fa-location-dot fa-beat"></i> No local
                                        </span>
                                    <?php else: ?>
                                        <span class="destino-badge badge-blue">
                                            <i class="fa-solid fa-arrow-right"></i> Próximo
                                        </span>
                                    <?php endif; ?>
                                    <strong class="text-sm"><?= e($m['destino']['nome']) ?></strong>
                                </div>
                            <?php else: ?>
                                <span class="text-muted text-sm">
                                    <?= $m['total_pdvs'] > 0 ? '✅ Rota completa' : '—' ?>
                                </span>
                            <?php endif; ?>
                        </td>

                        <!-- Situação das Visitas (ícones) -->
                        <td class="monitor-visitas">
                            <?php if (empty($m['pdvs'])): ?>
                                <span class="text-muted text-sm">Sem rota hoje</span>
                            <?php else: ?>
                                <div class="visitas-icons">
                                    <?php foreach ($m['pdvs'] as $pdv): ?>
                                        <div class="visit-dot <?= 'dot-' . $pdv['status'] ?> <?= !empty($pdv['observacao']) ? 'has-obs' : '' ?>"
                                             title="<?= e($pdv['nome']) ?> — <?= ucfirst(str_replace('_', ' ', $pdv['status'])) ?><?= !empty($pdv['observacao']) ? ' | Obs: ' . e(mb_substr($pdv['observacao'], 0, 60)) . '...' : '' ?>"
                                             onclick="event.stopPropagation(); verFotosDia(<?= $pdv['id'] ?>, '<?= e(addslashes($pdv['nome'])) ?>')">
                                            <?php if ($pdv['status'] === 'concluida'): ?>
                                                <i class="fa-solid fa-store"></i>
                                            <?php elseif ($pdv['status'] === 'em_andamento'): ?>
                                                <i class="fa-solid fa-location-dot"></i>
                                            <?php else: ?>
                                                <i class="fa-solid fa-store"></i>
                                            <?php endif; ?>
                                            <?php if (!empty($pdv['observacao'])): ?>
                                                <span class="obs-dot"></span>
                                            <?php endif; ?>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </td>

                        <!-- Progresso -->
                        <td class="monitor-progresso">
                            <div class="progress-circle <?= $m['progresso'] >= 100 ? 'complete' : ($m['progresso'] > 0 ? 'partial' : '') ?>">
                                <?= $m['progresso'] ?>%
                            </div>
                        </td>
                    </tr>

                    <?php if ($temDetalhes): ?>
                    <!-- Linha de detalhes (observações + pesquisas) -->
                    <tr class="detail-row" id="detalhes-<?= $mIdx ?>" style="display:none;">
                        <td colspan="4" style="padding:0;">
                            <div class="detail-content">
                                <?php if (!empty($observacoes)): ?>
                                <div class="detail-section">
                                    <h5><i class="fa-regular fa-comment"></i> Observações</h5>
                                    <?php foreach ($observacoes as $obs): ?>
                                    <div class="obs-item">
                                        <span class="obs-pdv"><?= e($obs['pdv']) ?></span>
                                        <span class="obs-texto"><?= nl2br(e($obs['texto'])) ?></span>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                                <?php endif; ?>

                                <?php if (!empty($m['respostas'])): ?>
                                <div class="detail-section">
                                    <h5><i class="fa-solid fa-clipboard-check"></i> Pesquisas Respondidas</h5>
                                    <?php foreach ($m['respostas'] as $resp): ?>
                                    <?php
                                        $campos = json_decode($resp['pesquisa_campos'] ?? '[]', true) ?: [];
                                        $dados = json_decode($resp['dados'] ?? '{}', true) ?: [];
                                    ?>
                                    <div class="pesq-item">
                                        <div class="pesq-header">
                                            <strong><?= e($resp['pesquisa_titulo']) ?></strong>
                                            <span class="text-muted text-xs"><?= e($resp['pdv_nome']) ?> · <?= date('H:i', strtotime($resp['created_at'])) ?></span>
                                        </div>
                                        <div class="pesq-dados">
                                            <?php foreach ($campos as $campo): ?>
                                            <?php
                                                $nome = $campo['nome'] ?? $campo['label'] ?? '';
                                                $chave = $campo['name'] ?? $campo['nome'] ?? '';
                                                $valor = $dados[$chave] ?? '—';
                                                if (is_array($valor)) $valor = implode(', ', $valor);
                                            ?>
                                            <div class="pesq-campo">
                                                <span class="pesq-label"><?= e($nome) ?></span>
                                                <span class="pesq-valor"><?= e($valor) ?></span>
                                            </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endif; ?>

                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<style>
/* — Monitor Table — */
.monitor-table td { vertical-align: middle; padding: 12px var(--space-md); }

.monitor-dados { display: flex; flex-direction: column; gap: 8px; }
.monitor-nome { display: flex; align-items: center; gap: var(--space-sm); }
.monitor-nome > div { display: flex; flex-direction: column; gap: 1px; }
.monitor-conexao { display: flex; align-items: center; gap: 4px; }

/* Mini contadores */
.monitor-counters { display: flex; gap: 6px; padding-left: 44px; }
.mc {
    display: flex; align-items: center; gap: 3px;
    font-size: 12px; font-weight: 600;
    padding: 2px 8px; border-radius: 12px;
}
.mc-green { background: rgba(16,185,129,0.1); color: #059669; }
.mc-orange { background: rgba(245,158,11,0.1); color: #d97706; }
.mc-gray { background: rgba(107,114,128,0.1); color: #6b7280; }
.mc-total { background: rgba(37,99,235,0.1); color: var(--primary); }

/* Destino */
.monitor-destino { }
.destino-card { display: flex; flex-direction: column; gap: 4px; }
.destino-badge {
    display: inline-flex; align-items: center; gap: 4px;
    font-size: 11px; font-weight: 600; padding: 2px 8px;
    border-radius: 6px; width: fit-content;
}
.badge-orange { background: #fef3c7; color: #d97706; }
.badge-blue { background: #dbeafe; color: #2563eb; }

/* Ícones de visitas */
.visitas-icons { display: flex; flex-wrap: wrap; gap: 5px; }
.visit-dot {
    width: 32px; height: 32px; border-radius: 6px;
    display: flex; align-items: center; justify-content: center;
    font-size: 14px; cursor: pointer; transition: transform 0.2s;
    position: relative;
}
.visit-dot:hover { transform: scale(1.2); z-index: 1; }

.dot-concluida { background: #d1fae5; color: #059669; }
.dot-em_andamento { background: #fef3c7; color: #d97706; animation: pulse-dot 2s infinite; }
.dot-pendente { background: #f3f4f6; color: #9ca3af; }

@keyframes pulse-dot {
    0%, 100% { box-shadow: 0 0 0 0 rgba(245,158,11,0.4); }
    50% { box-shadow: 0 0 0 6px rgba(245,158,11,0); }
}

/* Progresso circular */
.progress-circle {
    width: 50px; height: 50px; border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-weight: 700; font-size: 13px;
    border: 3px solid var(--gray-200);
    color: var(--text-muted);
    margin: 0 auto;
}
.progress-circle.partial { border-color: #f59e0b; color: #d97706; }
.progress-circle.complete { border-color: #10b981; color: #059669; background: #d1fae5; }

/* KPI purple */
.purple-border { border-left: 4px solid #7c3aed; }
.bg-purple { background: rgba(124,58,237,0.15); color: #7c3aed; }

.mc-obs { background: rgba(168,85,247,0.1); color: #7c3aed; }
.mc-pesq { background: rgba(14,165,233,0.1); color: #0284c7; }

/* Observação dot */
.obs-dot {
    position: absolute; top: -2px; right: -2px;
    width: 8px; height: 8px; border-radius: 50%;
    background: #7c3aed; border: 1.5px solid white;
}
.visit-dot.has-obs { outline: 2px solid rgba(124,58,237,0.3); outline-offset: 1px; }

/* Detail rows */
.detail-row td { background: var(--gray-50) !important; }
.detail-content { padding: 16px 20px; }
.detail-section { margin-bottom: 16px; }
.detail-section:last-child { margin-bottom: 0; }
.detail-section h5 {
    font-size: 13px; color: var(--text-secondary); margin-bottom: 8px;
    display: flex; align-items: center; gap: 6px;
}
.obs-item {
    display: flex; gap: 8px; align-items: flex-start;
    padding: 6px 10px; background: white; border-radius: 6px;
    margin-bottom: 4px; border: 1px solid var(--border);
}
.obs-pdv {
    font-size: 11px; font-weight: 600; color: var(--primary);
    white-space: nowrap; min-width: 100px;
}
.obs-texto { font-size: 13px; color: var(--text-secondary); }
.pesq-item {
    background: white; border-radius: 6px; overflow: hidden;
    border: 1px solid var(--border); margin-bottom: 6px;
}
.pesq-header {
    padding: 8px 12px; background: var(--gray-50);
    display: flex; justify-content: space-between; align-items: center;
    flex-wrap: wrap; gap: 4px;
}
.pesq-dados { padding: 0; }
.pesq-campo {
    display: flex; justify-content: space-between; align-items: center;
    padding: 5px 12px; border-bottom: 1px solid var(--border);
    font-size: 12px; gap: 8px;
}
.pesq-campo:last-child { border-bottom: none; }
.pesq-label { color: var(--text-muted); }
.pesq-valor { font-weight: 600; text-align: right; word-break: break-word; }

tr.has-details:hover { background: rgba(37,99,235,0.03); }
tr.has-details td:first-child::before {
    content: '\f078'; font-family: 'Font Awesome 6 Free'; font-weight: 900;
    font-size: 10px; color: var(--text-muted); margin-right: 6px;
    transition: transform 0.2s; display: inline-block;
}
tr.has-details.expanded td:first-child::before {
    transform: rotate(180deg);
}

/* Responsive */
@media (max-width: 1024px) {
    .monitor-table thead { display: none; }
    .monitor-table tbody tr:not(.detail-row) {
        display: flex; flex-direction: column;
        border-bottom: 2px solid var(--border); padding: var(--space-md) 0;
    }
    .monitor-table td { border: none; padding: 4px var(--space-md); }
    .monitor-counters { padding-left: 0; }
    .visitas-icons { gap: 4px; }
    .visit-dot { width: 28px; height: 28px; font-size: 12px; }
    .detail-row td { display: block; }
}
</style>

<script>
// Busca na tabela
document.getElementById('searchMonitor')?.addEventListener('input', function() {
    const term = this.value.toLowerCase();
    document.querySelectorAll('#tabelaMonitor tbody tr').forEach(row => {
        row.style.display = row.textContent.toLowerCase().includes(term) ? '' : 'none';
    });
});

// Auto-refresh a cada 60 segundos
let autoRefresh = setInterval(() => location.reload(), 60000);

// Toggle detalhes (observações + pesquisas)
function toggleDetalhes(idx) {
    const row = document.getElementById('detalhes-' + idx);
    const parentTr = row.previousElementSibling;
    if (!row) return;
    const visible = row.style.display !== 'none';
    row.style.display = visible ? 'none' : '';
    if (parentTr) parentTr.classList.toggle('expanded', !visible);
}

// === MODAL DE FOTOS DO DIA ===
async function verFotosDia(pdvId, pdvNome) {
    clearInterval(autoRefresh); // Pausa auto-refresh enquanto modal estiver aberto

    const modal = document.getElementById('fotosModal');
    const titulo = document.getElementById('fotosModalTitulo');
    const corpo = document.getElementById('fotosModalCorpo');

    titulo.textContent = pdvNome + ' — Fotos de Hoje';
    corpo.innerHTML = '<div style="text-align:center;padding:30px;"><i class="fa-solid fa-spinner fa-spin" style="font-size:24px;color:var(--primary);"></i><br><span class="text-muted text-sm">Carregando fotos...</span></div>';
    modal.classList.add('active');
    document.body.style.overflow = 'hidden';

    try {
        const res = await fetch(`/api/pdvs/${pdvId}/fotos-dia`);
        const data = await res.json();

        if (!data.fotos || data.fotos.length === 0) {
            corpo.innerHTML = '<div style="text-align:center;padding:40px;"><i class="fa-regular fa-image" style="font-size:36px;color:var(--gray-300);margin-bottom:10px;"></i><p class="text-muted">Nenhuma foto registrada hoje.</p></div>';
            return;
        }

        let html = '<div class="monitor-fotos-grid">';
        data.fotos.forEach(f => {
            html += `<div class="monitor-foto-thumb" onclick="abrirFotoFull('${f.path}', '${f.tipo}')">
                <img src="/${f.path}" alt="${f.tipo}" loading="lazy">
                <div class="monitor-foto-info">
                    <span class="monitor-foto-tipo">${f.tipo}</span>
                    <span class="monitor-foto-meta">${f.promotor} &middot; ${f.hora}</span>
                </div>
            </div>`;
        });
        html += '</div>';
        corpo.innerHTML = html;
    } catch (err) {
        corpo.innerHTML = '<div style="text-align:center;padding:30px;"><p class="text-muted">Erro ao carregar fotos.</p></div>';
    }
}

function fecharFotosModal() {
    document.getElementById('fotosModal').classList.remove('active');
    document.body.style.overflow = '';
    autoRefresh = setInterval(() => location.reload(), 60000);
}

function abrirFotoFull(path, label) {
    const modal = document.getElementById('fotoFullModal');
    document.getElementById('fotoFullImg').src = '/' + path;
    document.getElementById('fotoFullLabel').textContent = label;
    modal.classList.add('active');
}
function fecharFotoFull() {
    document.getElementById('fotoFullModal').classList.remove('active');
}

document.addEventListener('keydown', e => {
    if (e.key === 'Escape') {
        if (document.getElementById('fotoFullModal').classList.contains('active')) fecharFotoFull();
        else fecharFotosModal();
    }
});
</script>

<!-- Modal de Fotos do Dia -->
<div id="fotosModal" class="fotos-modal" onclick="fecharFotosModal()">
    <div class="fotos-modal-panel" onclick="event.stopPropagation()">
        <div class="fotos-modal-header">
            <h4 id="fotosModalTitulo"></h4>
            <button class="fotos-modal-close" onclick="fecharFotosModal()">&times;</button>
        </div>
        <div id="fotosModalCorpo" class="fotos-modal-body"></div>
    </div>
</div>

<!-- Lightbox Full -->
<div id="fotoFullModal" class="foto-full-modal" onclick="fecharFotoFull()">
    <div onclick="event.stopPropagation()" style="position:relative;text-align:center;">
        <button class="foto-full-close" onclick="fecharFotoFull()">&times;</button>
        <img id="fotoFullImg" src="" style="max-width:90vw;max-height:85vh;border-radius:8px;box-shadow:0 4px 20px rgba(0,0,0,0.4);">
        <p id="fotoFullLabel" style="color:white;margin-top:8px;font-size:13px;"></p>
    </div>
</div>

<style>
/* Fotos Modal */
.fotos-modal {
    display:none; position:fixed; top:0; left:0; width:100%; height:100%;
    background:rgba(0,0,0,0.5); z-index:9000;
    justify-content:center; align-items:center;
}
.fotos-modal.active { display:flex; }
.fotos-modal-panel {
    background:white; border-radius:12px; width:90%; max-width:700px; max-height:85vh;
    display:flex; flex-direction:column; box-shadow:0 8px 30px rgba(0,0,0,0.25);
}
.fotos-modal-header {
    display:flex; justify-content:space-between; align-items:center;
    padding:16px 20px; border-bottom:1px solid var(--border);
}
.fotos-modal-header h4 { margin:0; font-size:16px; }
.fotos-modal-close {
    background:none; border:none; font-size:24px; cursor:pointer; color:var(--text-muted);
    padding:0 4px; line-height:1;
}
.fotos-modal-body { padding:16px 20px; overflow-y:auto; }

.monitor-fotos-grid {
    display:grid; grid-template-columns:repeat(auto-fill, minmax(140px, 1fr)); gap:10px;
}
.monitor-foto-thumb {
    position:relative; cursor:pointer; border-radius:8px; overflow:hidden;
    aspect-ratio:4/3; background:var(--gray-50); border:1px solid var(--border);
    transition:transform 0.15s, box-shadow 0.15s;
}
.monitor-foto-thumb:hover { transform:scale(1.03); box-shadow:0 3px 10px rgba(0,0,0,0.12); }
.monitor-foto-thumb img { width:100%; height:100%; object-fit:cover; }
.monitor-foto-info {
    position:absolute; bottom:0; left:0; right:0;
    background:linear-gradient(transparent, rgba(0,0,0,0.75));
    padding:16px 6px 5px; text-align:center;
}
.monitor-foto-tipo { display:block; color:white; font-size:11px; font-weight:600; }
.monitor-foto-meta { display:block; color:rgba(255,255,255,0.7); font-size:10px; }

/* Lightbox full */
.foto-full-modal {
    display:none; position:fixed; top:0; left:0; width:100%; height:100%;
    background:rgba(0,0,0,0.9); z-index:9999;
    justify-content:center; align-items:center;
}
.foto-full-modal.active { display:flex; }
.foto-full-close {
    position:absolute; top:-14px; right:-14px;
    background:white; color:#333; border:none; border-radius:50%;
    width:36px; height:36px; font-size:22px; cursor:pointer;
    box-shadow:0 2px 8px rgba(0,0,0,0.3); z-index:1;
    display:flex; align-items:center; justify-content:center;
}
</style>
