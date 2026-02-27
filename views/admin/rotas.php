<!-- Rotas da Semana — Admin -->

<!-- Barra de controle -->
<div class="section-card" style="display:flex; align-items:center; gap:var(--space-md); flex-wrap:wrap;">
    <div class="form-group" style="margin:0; flex:1; min-width:200px;">
        <select id="seletorPromotor" class="form-control" onchange="selecionarPromotor(this.value)">
            <option value="">Selecione um promotor...</option>
            <?php foreach ($promotores as $p): ?>
                <option value="<?= $p['id'] ?>" <?= $p['id'] == $promotorId ? 'selected' : '' ?>>
                    <?= e($p['nome']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <?php if ($promotorId): ?>
    <div style="display:flex; align-items:center; gap:var(--space-sm);">
        <button class="btn-icon" onclick="mudarSemana(-7)" title="Semana anterior">
            <i class="fa-solid fa-chevron-left"></i>
        </button>
        <span id="labelSemana" style="font-weight:600; white-space:nowrap;">
            <?= date('d/m', strtotime($segundaAtual)) ?> — <?= date('d/m/Y', strtotime($segundaAtual . ' +6 days')) ?>
        </span>
        <button class="btn-icon" onclick="mudarSemana(7)" title="Próxima semana">
            <i class="fa-solid fa-chevron-right"></i>
        </button>
    </div>
    <?php endif; ?>
</div>

<!-- ===== GRADE SEMANAL (visão principal) ===== -->
<?php if ($promotorId && !empty($semana)): ?>
<div class="semana-grid" id="semanaGrid">
    <?php foreach ($semana as $idx => $dia): ?>
    <div class="dia-coluna" data-rota-id="<?= $dia['rota_id'] ?>" data-data="<?= $dia['data'] ?>" data-dia="<?= $dia['dia_semana'] ?>" data-idx="<?= $idx ?>">
        <div class="dia-header">
            <strong><?= $dia['dia_nome'] ?></strong>
            <span class="text-muted text-sm"><?= date('d/m', strtotime($dia['data'])) ?></span>
        </div>

        <div class="dia-pdvs" id="pdvs-dia-<?= $idx ?>">
            <?php if (empty($dia['pdvs'])): ?>
                <div class="empty-day">
                    <i class="fa-solid fa-route" style="opacity:0.2; font-size:24px;"></i>
                    <p class="text-muted text-sm">Nenhum PDV</p>
                </div>
            <?php else: ?>
                <?php foreach ($dia['pdvs'] as $pdv): ?>
                <div class="pdv-item <?= $pdv['origem'] === 'temporario' ? 'pdv-temporario' : '' ?>"
                     data-pdv-id="<?= $pdv['id'] ?>"
                     data-origem="<?= $pdv['origem'] ?>"
                     data-excecao-id="<?= $pdv['excecao_id'] ?? '' ?>">
                    <div class="pdv-drag-handle"><i class="fa-solid fa-grip-vertical"></i></div>
                    <div class="pdv-info">
                        <span class="pdv-nome"><?= e($pdv['nome']) ?></span>
                        <span class="pdv-end text-muted text-xs"><?= e($pdv['cidade'] ?? $pdv['endereco'] ?? '') ?></span>
                    </div>
                    <div class="pdv-badges">
                        <?php if ($pdv['origem'] === 'temporario'): ?>
                            <span class="status-badge status-pending" style="font-size:9px;">TEMP</span>
                        <?php else: ?>
                            <span class="status-badge status-done" style="font-size:9px;">FIXO</span>
                        <?php endif; ?>
                    </div>
                    <button class="btn-icon btn-sm" onclick="removerPdv(<?= $dia['rota_id'] ?>, <?= $pdv['id'] ?>, '<?= $pdv['origem'] ?>', '<?= $dia['data'] ?>', <?= $pdv['excecao_id'] ?? 0 ?>)" title="Remover">
                        <i class="fa-solid fa-xmark" style="color:var(--danger);"></i>
                    </button>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- Pesquisas do dia (mini badges) -->
        <?php
        $pesquisasDoDia = $pesquisasPorDia[$dia['dia_semana']] ?? [];
        if (!empty($pesquisasDoDia)):
        ?>
        <div class="dia-pesquisas">
            <?php foreach ($pesquisasDoDia as $pesq): ?>
                <span class="pesq-badge" title="<?= e($pesq['titulo']) ?>">
                    <i class="fa-solid fa-clipboard-list"></i> <?= e(mb_substr($pesq['titulo'], 0, 15)) ?>
                </span>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <div class="dia-actions">
            <button class="btn-sm btn-outline" onclick="abrirPainel(<?= $idx ?>, <?= $dia['rota_id'] ?>, '<?= $dia['data'] ?>', '<?= $dia['dia_nome'] ?>')">
                <i class="fa-solid fa-plus"></i> Adicionar
            </button>
            <?php if (!empty($dia['pdvs'])): ?>
            <button class="btn-sm btn-icon" onclick="otimizarRota(<?= $dia['rota_id'] ?>)" title="Otimizar rota">
                <i class="fa-solid fa-route" style="color:var(--primary);"></i>
            </button>
            <?php endif; ?>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<!-- ===== PAINEL SPLIT-SCREEN (oculto por padrão) ===== -->
<div class="painel-add-overlay" id="painelAdd" style="display:none;">
    <div class="painel-add">
        <div class="painel-add-header">
            <h3>
                <i class="fa-solid fa-plus-circle" style="color:var(--primary);"></i>
                Adicionar PDVs — <span id="painelDiaNome">Segunda</span>
            </h3>
            <button class="btn-icon" onclick="fecharPainel()"><i class="fa-solid fa-xmark"></i></button>
        </div>

        <!-- Tipo de alteração -->
        <div class="painel-tipo">
            <label class="radio-card" id="radioFixo">
                <input type="radio" name="painelTipo" value="fixo" checked>
                <div class="radio-content">
                    <i class="fa-solid fa-thumbtack"></i>
                    <strong>Fixo</strong>
                    <span class="text-xs text-muted">Toda semana</span>
                </div>
            </label>
            <label class="radio-card" id="radioTemp">
                <input type="radio" name="painelTipo" value="temporario">
                <div class="radio-content">
                    <i class="fa-solid fa-clock"></i>
                    <strong>Temporário</strong>
                    <span class="text-xs text-muted">Só neste dia</span>
                </div>
            </label>
        </div>

        <!-- Split-screen -->
        <div class="split-screen">
            <!-- ESQUERDA: Selecionados -->
            <div class="split-lado selecionados">
                <div class="split-header">
                    <h4><i class="fa-solid fa-check-circle" style="color:var(--success);"></i> Selecionados</h4>
                    <span class="split-count" id="countSel">0</span>
                </div>
                <div class="split-search">
                    <input type="text" placeholder="Filtrar selecionados..." class="form-control form-control-sm"
                           oninput="filtrarLista(this, 'listaSel')">
                </div>
                <div class="split-list" id="listaSel"></div>
            </div>

            <!-- DIREITA: Disponíveis -->
            <div class="split-lado disponiveis">
                <div class="split-header">
                    <h4><i class="fa-solid fa-store" style="color:var(--primary);"></i> Disponíveis</h4>
                    <span class="split-count" id="countDisp">0</span>
                </div>
                <div class="split-search">
                    <input type="text" placeholder="Buscar PDVs..." class="form-control form-control-sm"
                           oninput="filtrarLista(this, 'listaDisp')">
                </div>
                <div class="split-list" id="listaDisp"></div>
            </div>
        </div>

        <!-- Pesquisas -->
        <div class="painel-pesquisas">
            <h4><i class="fa-solid fa-clipboard-list"></i> Pesquisas deste dia</h4>
            <div class="pesquisas-grid" id="painelPesquisas">
                <?php foreach ($todasPesquisas as $pesq): ?>
                <label class="pesquisa-check" data-pesquisa-id="<?= $pesq['id'] ?>">
                    <input type="checkbox" value="<?= $pesq['id'] ?>" onchange="marcarPesquisa(this)">
                    <div class="pesquisa-info">
                        <strong><?= e($pesq['titulo']) ?></strong>
                        <?php if (!empty($pesq['descricao'])): ?>
                            <span class="text-muted text-xs"><?= e(mb_substr($pesq['descricao'], 0, 60)) ?></span>
                        <?php endif; ?>
                    </div>
                    <i class="fa-solid fa-check pesquisa-icon"></i>
                </label>
                <?php endforeach; ?>
                <?php if (empty($todasPesquisas)): ?>
                <p class="text-muted text-sm">Nenhuma pesquisa cadastrada. <a href="/admin/pesquisas/criar">Criar pesquisa</a></p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Footer -->
        <div class="painel-footer">
            <button class="btn-outline" onclick="fecharPainel()">Fechar</button>
            <button class="btn-primary" onclick="salvarPainel()">
                <i class="fa-solid fa-check"></i> Salvar Alterações
            </button>
        </div>
    </div>
</div>

<?php elseif (!$promotorId): ?>
<div class="section-card empty-state-card">
    <i class="fa-solid fa-users"></i>
    <h4>Selecione um promotor</h4>
    <p class="text-muted">Escolha um promotor acima para ver e editar suas rotas semanais.</p>
</div>
<?php endif; ?>

<style>
/* — Painel overlay (split-screen) — */
.painel-add-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.5);
    z-index: 1000;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: var(--space-md);
}
.painel-add {
    background: var(--white);
    border-radius: var(--radius-lg);
    width: 100%;
    max-width: 1000px;
    max-height: 90vh;
    overflow-y: auto;
    box-shadow: 0 25px 50px rgba(0,0,0,0.25);
    display: flex;
    flex-direction: column;
}
.painel-add-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: var(--space-md) var(--space-lg);
    border-bottom: 1px solid var(--border);
}
.painel-add-header h3 {
    margin: 0;
    display: flex;
    align-items: center;
    gap: var(--space-sm);
}

.painel-tipo {
    display: flex;
    gap: var(--space-md);
    padding: var(--space-md) var(--space-lg);
    border-bottom: 1px solid var(--border);
}

/* — Split-screen — */
.split-screen {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 0;
    flex: 1;
    min-height: 300px;
}
.split-lado {
    display: flex;
    flex-direction: column;
    border-right: 1px solid var(--border);
}
.split-lado:last-child { border-right: none; }

.split-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: var(--space-sm) var(--space-md);
    background: var(--gray-50);
    border-bottom: 1px solid var(--border);
}
.split-header h4 {
    margin: 0;
    font-size: var(--font-sm);
    display: flex;
    align-items: center;
    gap: 6px;
}
.split-count {
    background: var(--primary);
    color: white;
    padding: 2px 10px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
}

.split-search {
    padding: var(--space-xs) var(--space-sm);
    border-bottom: 1px solid var(--border);
}
.split-search .form-control-sm { padding: 6px 10px; font-size: 13px; }

.split-list {
    flex: 1;
    overflow-y: auto;
    max-height: 320px;
}

.split-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 10px var(--space-md);
    cursor: pointer;
    border-bottom: 1px solid var(--gray-50);
    transition: background 0.15s;
}
.split-item:hover { background: var(--gray-50); }
.split-item-info {
    display: flex;
    flex-direction: column;
    gap: 1px;
    min-width: 0;
}
.split-item-info strong { font-size: 13px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.split-item-icon {
    width: 28px; height: 28px;
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0; font-size: 12px;
    transition: all 0.2s;
}
.split-item-icon.add { background: var(--blue-50, #eff6ff); color: var(--primary); }
.split-item-icon.remove { background: var(--red-50, #fef2f2); color: var(--danger); }
.split-item:hover .split-item-icon.add { background: var(--primary); color: white; }
.split-item:hover .split-item-icon.remove { background: var(--danger); color: white; }

/* — Pesquisas — */
.painel-pesquisas {
    padding: var(--space-md) var(--space-lg);
    border-top: 1px solid var(--border);
}
.painel-pesquisas h4 {
    margin-bottom: var(--space-sm);
    font-size: var(--font-sm);
    color: var(--text-muted);
}
.pesquisas-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
    gap: var(--space-sm);
}
.pesquisa-check {
    display: flex; align-items: center; gap: var(--space-sm);
    padding: var(--space-sm) var(--space-md);
    border: 2px solid var(--border); border-radius: var(--radius);
    cursor: pointer; transition: all 0.2s;
}
.pesquisa-check:hover { border-color: var(--primary); }
.pesquisa-check.checked { border-color: var(--success); background: rgba(16,185,129,0.05); }
.pesquisa-check input[type="checkbox"] { display: none; }
.pesquisa-info { flex: 1; display: flex; flex-direction: column; gap: 2px; }
.pesquisa-info strong { font-size: 13px; }
.pesquisa-icon {
    width: 24px; height: 24px; border-radius: 50%;
    border: 2px solid var(--border);
    display: flex; align-items: center; justify-content: center;
    font-size: 11px; color: transparent; transition: all 0.2s; flex-shrink: 0;
}
.pesquisa-check.checked .pesquisa-icon { background: var(--success); border-color: var(--success); color: white; }

/* — Footer do painel — */
.painel-footer {
    display: flex; justify-content: flex-end; gap: var(--space-sm);
    padding: var(--space-md) var(--space-lg);
    border-top: 1px solid var(--border);
}

/* — Mini badges de pesquisa na grade — */
.dia-pesquisas {
    padding: 4px var(--space-sm);
    display: flex; flex-wrap: wrap; gap: 3px;
    border-top: 1px dashed var(--border);
}
.pesq-badge {
    font-size: 10px;
    background: rgba(99,102,241,0.1);
    color: var(--primary);
    padding: 2px 6px;
    border-radius: 4px;
    display: flex; align-items: center; gap: 3px;
}

/* — Responsive — */
@media (max-width: 768px) {
    .split-screen { grid-template-columns: 1fr; }
    .split-list { max-height: 200px; }
    .painel-add { max-height: 95vh; }
    .painel-tipo { flex-direction: column; }
    .pesquisas-grid { grid-template-columns: 1fr; }
}
</style>

<!-- SortableJS -->
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>

<script>
const PROMOTOR_ID = <?= $promotorId ?: 0 ?>;
let SEGUNDA_ATUAL = '<?= $segundaAtual ?>';

// Dados para AJAX
const ROTAS = <?= json_encode(array_map(fn($d) => [
    'rota_id' => $d['rota_id'],
    'data'    => $d['data'],
    'dia_semana' => $d['dia_semana'],
    'dia_nome' => $d['dia_nome'],
    'pdv_ids' => array_column($d['pdvs'], 'id'),
], $semana)) ?>;

const TODOS_PDVS = <?= json_encode(array_map(fn($p) => [
    'id'     => $p['id'],
    'nome'   => $p['nome'],
    'codigo' => $p['codigo'] ?? '',
    'cidade' => $p['cidade'] ?? '',
], $todosPdvs)) ?>;

const PESQUISAS_POR_DIA = <?= json_encode($pesquisasPorDia) ?>;

let painelDiaIdx = null;

// ---- Navegação ----
function selecionarPromotor(id) {
    if (id) window.location.href = '/admin/rotas?promotor=' + id;
}
function mudarSemana(dias) {
    const d = new Date(SEGUNDA_ATUAL + 'T00:00:00');
    d.setDate(d.getDate() + dias);
    window.location.href = '/admin/rotas?promotor=' + PROMOTOR_ID + '&data=' + d.toISOString().slice(0, 10);
}

// ---- AJAX ----
async function apiPost(url, data) {
    const form = new FormData();
    for (const [k, v] of Object.entries(data)) form.append(k, v);
    return fetch(url, { method: 'POST', body: form }).then(r => r.json());
}

// ---- Abrir Painel Split-Screen ----
function abrirPainel(diaIdx, rotaId, data, diaNome) {
    painelDiaIdx = diaIdx;
    const rota = ROTAS[diaIdx];
    const pdvIdsSelecionados = [...rota.pdv_ids]; // IDs dos PDVs já no dia

    document.getElementById('painelDiaNome').textContent = diaNome + ' — ' + data.split('-').reverse().join('/');

    // Reset tipo
    document.querySelector('input[name="painelTipo"][value="fixo"]').checked = true;

    // Monta lista selecionados
    const listaSel = document.getElementById('listaSel');
    const listaDisp = document.getElementById('listaDisp');
    listaSel.innerHTML = '';
    listaDisp.innerHTML = '';

    TODOS_PDVS.forEach(pdv => {
        const isSel = pdvIdsSelecionados.includes(pdv.id);
        const el = criarItemPdv(pdv, isSel);
        if (isSel) {
            listaSel.appendChild(el);
        } else {
            listaDisp.appendChild(el);
        }
    });

    atualizarContadores();

    // Pesquisas: marcar as ativas deste dia
    const pesqDoDia = PESQUISAS_POR_DIA[rota.dia_semana] || [];
    const pesqIds = pesqDoDia.map(p => p.id);
    document.querySelectorAll('#painelPesquisas .pesquisa-check').forEach(label => {
        const cb = label.querySelector('input');
        const isChecked = pesqIds.includes(parseInt(cb.value));
        cb.checked = isChecked;
        label.classList.toggle('checked', isChecked);
    });

    document.getElementById('painelAdd').style.display = 'flex';
}

function criarItemPdv(pdv, isSel) {
    const el = document.createElement('div');
    el.className = 'split-item' + (isSel ? ' selected' : '');
    el.dataset.pdvId = pdv.id;
    el.onclick = function() { togglePdv(this); };

    const sub = [pdv.codigo, pdv.cidade].filter(Boolean).join(' • ') || '—';
    el.innerHTML = `
        <div class="split-item-info">
            <strong>${escHtml(pdv.nome)}</strong>
            <span class="text-muted text-xs">${escHtml(sub)}</span>
        </div>
        <i class="fa-solid ${isSel ? 'fa-xmark' : 'fa-plus'} split-item-icon ${isSel ? 'remove' : 'add'}"></i>
    `;
    return el;
}

function escHtml(s) {
    const d = document.createElement('div');
    d.textContent = s;
    return d.innerHTML;
}

// ---- Toggle PDV ----
function togglePdv(el) {
    const isSel = el.classList.contains('selected');
    const listaSel = document.getElementById('listaSel');
    const listaDisp = document.getElementById('listaDisp');
    const icon = el.querySelector('.split-item-icon');

    if (isSel) {
        el.classList.remove('selected');
        icon.className = 'fa-solid fa-plus split-item-icon add';
        listaDisp.appendChild(el);
    } else {
        el.classList.add('selected');
        icon.className = 'fa-solid fa-xmark split-item-icon remove';
        listaSel.appendChild(el);
    }
    atualizarContadores();
}

function atualizarContadores() {
    const sel = document.getElementById('listaSel').querySelectorAll('.split-item').length;
    const disp = document.getElementById('listaDisp').querySelectorAll('.split-item').length;
    document.getElementById('countSel').textContent = sel;
    document.getElementById('countDisp').textContent = disp;
}

// ---- Filtrar ----
function filtrarLista(input, listId) {
    const term = input.value.toLowerCase();
    document.getElementById(listId).querySelectorAll('.split-item').forEach(item => {
        item.style.display = item.textContent.toLowerCase().includes(term) ? '' : 'none';
    });
}

// ---- Fechar Painel ----
function fecharPainel() {
    document.getElementById('painelAdd').style.display = 'none';
    painelDiaIdx = null;
}

// ---- Pesquisas ----
function marcarPesquisa(cb) {
    const label = cb.closest('.pesquisa-check');
    label.classList.toggle('checked', cb.checked);
}

// ---- Salvar ----
async function salvarPainel() {
    if (painelDiaIdx === null) return;
    const rota = ROTAS[painelDiaIdx];
    const tipo = document.querySelector('input[name="painelTipo"]:checked').value;

    // PDVs selecionados
    const selItems = document.getElementById('listaSel').querySelectorAll('.split-item');
    const pdvIds = Array.from(selItems).map(el => parseInt(el.dataset.pdvId));

    // Pesquisas
    const pesqChecks = document.querySelectorAll('#painelPesquisas input[type="checkbox"]:checked');
    const pesquisaIds = Array.from(pesqChecks).map(cb => parseInt(cb.value));

    // Determinar o que mudou — adicionar e remover
    const originalIds = rota.pdv_ids.map(Number);
    const novosIds = pdvIds;

    const adicionados = novosIds.filter(id => !originalIds.includes(id));
    const removidos = originalIds.filter(id => !novosIds.includes(id));

    if (tipo === 'fixo') {
        // Sincronizar toda a lista via batch
        await apiPost('/admin/rotas/sincronizar-pdvs', {
            rota_id: rota.rota_id,
            pdv_ids: JSON.stringify(novosIds)
        });
    } else {
        // Temporário: adicionar e remover individualmente
        for (const id of adicionados) {
            await apiPost('/admin/rotas/adicionar-pdv', {
                rota_id: rota.rota_id,
                pdv_id: id,
                tipo: 'temporario',
                data: rota.data,
                promotor_id: PROMOTOR_ID
            });
        }
        for (const id of removidos) {
            await apiPost('/admin/rotas/remover-pdv', {
                rota_id: rota.rota_id,
                pdv_id: id,
                tipo: 'temporario',
                data: rota.data
            });
        }
    }

    // Salvar pesquisas sempre como fixas
    await apiPost('/admin/rotas/sincronizar-pesquisas', {
        rota_id: rota.rota_id,
        pesquisa_ids: JSON.stringify(pesquisaIds)
    });

    fecharPainel();
    location.reload();
}

// ---- Remover PDV da grade ----
async function removerPdv(rotaId, pdvId, origem, data, excecaoId) {
    const msg = origem === 'fixo'
        ? 'Remover este PDV fixo?\n\n(Removido de todas as semanas)'
        : 'Remover este PDV temporário?';
    
    const confirmed = await App.confirm(msg);
    if (!confirmed) return;

    await apiPost('/admin/rotas/remover-pdv', {
        rota_id: rotaId,
        pdv_id: pdvId,
        tipo: origem,
        data: data,
        excecao_id: excecaoId || '',
    });
    location.reload();
}

// ---- Otimizar ----
async function otimizarRota(rotaId) {
    const confirmed = await App.confirm('Otimizar ordem dos PDVs por proximidade geográfica?');
    if (!confirmed) return;
    await apiPost('/admin/rotas/otimizar', { rota_id: rotaId });
    location.reload();
}

// ---- SortableJS drag-and-drop ----
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.dia-pdvs').forEach(container => {
        new Sortable(container, {
            handle: '.pdv-drag-handle',
            animation: 200,
            ghostClass: 'pdv-ghost',
            onEnd: function(evt) {
                const col = evt.target.closest('.dia-coluna');
                const rotaId = col.dataset.rotaId;
                const items = Array.from(container.querySelectorAll('.pdv-item'));
                const pdvIds = items.map(el => el.dataset.pdvId);

                apiPost('/admin/rotas/reordenar', {
                    rota_id: rotaId,
                    pdv_ids: JSON.stringify(pdvIds),
                });
            }
        });
    });
});

// Fechar com ESC
document.addEventListener('keydown', e => {
    if (e.key === 'Escape') fecharPainel();
});
</script>
