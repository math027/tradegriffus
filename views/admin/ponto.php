<!-- Controle de Ponto — Admin -->

<!-- Filtros -->
<div class="section-card" style="margin-bottom: var(--space-md);">
    <form method="GET" action="/admin/ponto" class="ponto-filtros">
        <div class="form-group">
            <label for="inicio">Data Início</label>
            <input type="date" id="inicio" name="inicio" class="form-control"
                   value="<?= e($filtro['inicio']) ?>">
        </div>
        <div class="form-group">
            <label for="fim">Data Fim</label>
            <input type="date" id="fim" name="fim" class="form-control"
                   value="<?= e($filtro['fim']) ?>">
        </div>
        <div class="form-group">
            <label for="promotor">Promotor</label>
            <select id="promotor" name="promotor" class="form-control">
                <option value="">Todos CLT</option>
                <?php foreach ($promotores as $p): ?>
                <option value="<?= $p['id'] ?>" <?= $filtro['promotor'] == $p['id'] ? 'selected' : '' ?>>
                    <?= e($p['nome']) ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group" style="align-self:flex-end;">
            <button type="submit" class="btn-primary">
                <i class="fa-solid fa-filter"></i> Filtrar
            </button>
        </div>
    </form>
</div>

<!-- Tabela de registros -->
<div class="section-card">
    <?php if (empty($registros)): ?>
    <div class="empty-state" style="padding:40px; text-align:center;">
        <i class="fa-regular fa-clock" style="font-size:36px; color:var(--gray-300); margin-bottom:12px;"></i>
        <p class="text-muted">Nenhum registro de ponto encontrado para o período.</p>
    </div>
    <?php else: ?>
    <div class="table-responsive">
        <table class="data-table" id="tabelaPonto" data-paginate="true" data-per-page="10">
            <thead>
                <tr>
                    <th>Colaborador</th>
                    <th>Data</th>
                    <th>Entrada</th>
                    <th>Almoço Saída</th>
                    <th>Almoço Retorno</th>
                    <th>Saída</th>
                    <th>Total</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($registros as $r): ?>
                <?php
                    $total = '—';
                    if ($r['entrada'] && $r['saida']) {
                        $seg = strtotime($r['saida']) - strtotime($r['entrada']);
                        if ($r['almoco_saida'] && $r['almoco_retorno']) {
                            $seg -= (strtotime($r['almoco_retorno']) - strtotime($r['almoco_saida']));
                        }
                        $h = floor($seg / 3600);
                        $m = floor(($seg % 3600) / 60);
                        $total = "{$h}h {$m}min";
                    }
                    $ehHoje = ($r['data'] === date('Y-m-d'));
                    $foiAjustado = !empty($r['ajustado_por']);
                ?>
                <tr>
                    <td><strong><?= e($r['user_nome']) ?></strong></td>
                    <td><?= date('d/m/Y', strtotime($r['data'])) ?></td>
                    <td>
                        <?php if ($r['entrada']): ?>
                            <span class="ponto-badge ponto-badge-ok"><?= substr($r['entrada'], 0, 5) ?></span>
                        <?php else: ?>
                            <span class="ponto-badge ponto-badge-vazio">—</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($r['almoco_saida']): ?>
                            <span class="ponto-badge ponto-badge-ok"><?= substr($r['almoco_saida'], 0, 5) ?></span>
                        <?php else: ?>
                            <span class="ponto-badge ponto-badge-vazio">—</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($r['almoco_retorno']): ?>
                            <span class="ponto-badge ponto-badge-ok"><?= substr($r['almoco_retorno'], 0, 5) ?></span>
                        <?php else: ?>
                            <span class="ponto-badge ponto-badge-vazio">—</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($r['saida']): ?>
                            <span class="ponto-badge ponto-badge-ok"><?= substr($r['saida'], 0, 5) ?></span>
                        <?php else: ?>
                            <span class="ponto-badge ponto-badge-vazio">—</span>
                        <?php endif; ?>
                    </td>
                    <td><strong><?= $total ?></strong></td>
                    <td>
                        <div class="action-buttons">
                            <?php if ($foiAjustado): ?>
                                <span class="ponto-badge ponto-badge-ajustado" title="Ajustado em <?= date('d/m/Y H:i', strtotime($r['ajustado_em'])) ?>">
                                    <i class="fa-solid fa-pen-to-square"></i> Ajustado
                                </span>
                            <?php endif; ?>
                            <?php if (!$ehHoje): ?>
                                <button type="button" class="btn-icon" title="Ajustar ponto"
                                        onclick="abrirAjustePonto(<?= $r['id'] ?>, '<?= e($r['user_nome']) ?>', '<?= date('d/m/Y', strtotime($r['data'])) ?>', '<?= substr($r['entrada'] ?? '', 0, 5) ?>', '<?= substr($r['almoco_saida'] ?? '', 0, 5) ?>', '<?= substr($r['almoco_retorno'] ?? '', 0, 5) ?>', '<?= substr($r['saida'] ?? '', 0, 5) ?>')">
                                    <i class="fa-solid fa-pen"></i>
                                </button>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>

<!-- Modal de Ajuste de Ponto -->
<div id="ajustePontoModal" class="ajuste-modal" onclick="fecharAjusteModal()">
    <div class="ajuste-modal-panel" onclick="event.stopPropagation()">
        <div class="ajuste-modal-header">
            <h4><i class="fa-solid fa-pen-to-square"></i> Ajustar Ponto</h4>
            <button class="ajuste-modal-close" onclick="fecharAjusteModal()">&times;</button>
        </div>
        <form method="POST" action="/admin/ponto/ajustar">
            <?= csrf_field() ?>
            <input type="hidden" name="ponto_id" id="ajustePontoId">
            <div class="ajuste-modal-body">
                <div class="ajuste-info">
                    <strong id="ajusteNome"></strong>
                    <span class="text-muted" id="ajusteData"></span>
                </div>
                <div class="ajuste-campos">
                    <div class="form-group">
                        <label for="ajusteEntrada"><i class="fa-solid fa-right-to-bracket"></i> Entrada</label>
                        <input type="time" id="ajusteEntrada" name="entrada" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="ajusteAlmocoSaida"><i class="fa-solid fa-utensils"></i> Almoço Saída</label>
                        <input type="time" id="ajusteAlmocoSaida" name="almoco_saida" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="ajusteAlmocoRetorno"><i class="fa-solid fa-utensils"></i> Almoço Retorno</label>
                        <input type="time" id="ajusteAlmocoRetorno" name="almoco_retorno" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="ajusteSaida"><i class="fa-solid fa-right-from-bracket"></i> Saída</label>
                        <input type="time" id="ajusteSaida" name="saida" class="form-control">
                    </div>
                </div>
            </div>
            <div class="ajuste-modal-footer">
                <button type="button" class="btn-outline" onclick="fecharAjusteModal()">Cancelar</button>
                <button type="submit" class="btn-primary">
                    <i class="fa-solid fa-check"></i> Salvar Ajuste
                </button>
            </div>
        </form>
    </div>
</div>

<style>
.ponto-filtros {
    display: flex;
    gap: var(--space-md);
    align-items: flex-start;
    flex-wrap: wrap;
}

.ponto-filtros .form-group {
    margin: 0;
    min-width: 160px;
}

.ponto-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 4px 10px;
    border-radius: 6px;
    font-size: 13px;
    font-weight: 600;
}

.ponto-badge-ok {
    background: rgba(5, 150, 105, 0.1);
    color: #059669;
}

.ponto-badge-vazio {
    background: var(--gray-50);
    color: var(--gray-300);
}

.ponto-badge-ajustado {
    background: rgba(37, 99, 235, 0.1);
    color: #2563eb;
    font-size: 11px;
    gap: 4px;
}

/* Modal de Ajuste */
.ajuste-modal {
    display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%;
    background: rgba(0,0,0,0.5); z-index: 9000;
    justify-content: center; align-items: center;
}
.ajuste-modal.active { display: flex; }
.ajuste-modal-panel {
    background: white; border-radius: 12px; width: 90%; max-width: 480px;
    box-shadow: 0 8px 30px rgba(0,0,0,0.25);
    animation: modalSlideIn 0.25s ease;
}
@keyframes modalSlideIn {
    from { opacity: 0; transform: translateY(-20px); }
    to { opacity: 1; transform: translateY(0); }
}
.ajuste-modal-header {
    display: flex; justify-content: space-between; align-items: center;
    padding: 16px 20px; border-bottom: 1px solid var(--border);
}
.ajuste-modal-header h4 { margin: 0; font-size: 16px; display: flex; align-items: center; gap: 8px; }
.ajuste-modal-close {
    background: none; border: none; font-size: 24px; cursor: pointer;
    color: var(--text-muted); padding: 0 4px; line-height: 1;
}
.ajuste-modal-body { padding: 20px; }
.ajuste-info {
    display: flex; flex-direction: column; gap: 2px;
    padding: 12px 16px; background: var(--gray-50); border-radius: 8px;
    margin-bottom: 16px;
}
.ajuste-campos {
    display: grid; grid-template-columns: 1fr 1fr; gap: 12px;
}
.ajuste-campos .form-group { margin: 0; }
.ajuste-campos label {
    display: flex; align-items: center; gap: 6px;
    font-size: 13px; font-weight: 600; margin-bottom: 4px;
}
.ajuste-modal-footer {
    display: flex; justify-content: flex-end; gap: 8px;
    padding: 16px 20px; border-top: 1px solid var(--border);
}
</style>

<script>
function abrirAjustePonto(id, nome, data, entrada, almocoSaida, almocoRetorno, saida) {
    document.getElementById('ajustePontoId').value = id;
    document.getElementById('ajusteNome').textContent = nome;
    document.getElementById('ajusteData').textContent = data;
    document.getElementById('ajusteEntrada').value = entrada;
    document.getElementById('ajusteAlmocoSaida').value = almocoSaida;
    document.getElementById('ajusteAlmocoRetorno').value = almocoRetorno;
    document.getElementById('ajusteSaida').value = saida;
    document.getElementById('ajustePontoModal').classList.add('active');
    document.body.style.overflow = 'hidden';
}

function fecharAjusteModal() {
    document.getElementById('ajustePontoModal').classList.remove('active');
    document.body.style.overflow = '';
}

document.addEventListener('keydown', e => {
    if (e.key === 'Escape') fecharAjusteModal();
});
</script>
