<!-- Respostas da Pesquisa -->
<div class="section-header" style="margin-bottom: var(--space-lg);">
    <div>
        <h2><i class="fa-solid fa-clipboard-check"></i> <?= e($pesquisa['titulo']) ?></h2>
        <p class="text-muted"><?= e($pesquisa['descricao'] ?? '') ?></p>
    </div>
    <a href="/admin/pesquisas" class="btn-outline">
        <i class="fa-solid fa-arrow-left"></i> Voltar
    </a>
</div>

<!-- KPI -->
<div class="kpi-grid">
    <div class="card kpi-card blue-border">
        <div class="card-header">
            <span class="card-title">Total de Respostas</span>
            <div class="card-icon bg-blue"><i class="fa-solid fa-clipboard-check"></i></div>
        </div>
        <div class="card-value"><?= count($respostas) ?></div>
    </div>

    <?php
    $promotores = array_unique(array_column($respostas, 'promotor_nome'));
    $pdvs = array_unique(array_column($respostas, 'pdv_nome'));
    ?>
    <div class="card kpi-card green-border">
        <div class="card-header">
            <span class="card-title">Promotores</span>
            <div class="card-icon bg-green"><i class="fa-solid fa-users"></i></div>
        </div>
        <div class="card-value"><?= count($promotores) ?></div>
    </div>

    <div class="card kpi-card orange-border">
        <div class="card-header">
            <span class="card-title">PDVs</span>
            <div class="card-icon bg-orange"><i class="fa-solid fa-store"></i></div>
        </div>
        <div class="card-value"><?= count($pdvs) ?></div>
    </div>
</div>

<!-- Filtro -->
<div class="section-card">
    <div class="section-header">
        <h3 class="section-title">Respostas</h3>
        <input type="text" placeholder="Buscar por promotor ou PDV..." class="search-input" id="searchRespostas">
    </div>

    <?php if (empty($respostas)): ?>
        <div class="empty-state-card">
            <i class="fa-regular fa-clipboard"></i>
            <p>Nenhuma resposta registrada ainda.</p>
        </div>
    <?php else: ?>
        <?php
            $campos = json_decode($pesquisa['campos'] ?? '[]', true) ?: [];
        ?>
        <div class="respostas-lista" id="respostasLista" data-paginate="true" data-per-page="10" data-paginate-selector=".resposta-card">
            <?php foreach ($respostas as $resp): ?>
            <?php
                $dados = json_decode($resp['dados'] ?? '{}', true) ?: [];
            ?>
            <div class="resposta-card" data-search="<?= e(strtolower($resp['promotor_nome'] . ' ' . $resp['pdv_nome'])) ?>">
                <div class="resposta-header">
                    <div>
                        <strong><?= e($resp['promotor_nome']) ?></strong>
                        <span class="text-muted text-sm">
                            <?= e($resp['pdv_nome']) ?> · <?= date('d/m/Y H:i', strtotime($resp['created_at'])) ?>
                        </span>
                    </div>
                </div>
                <div class="resposta-dados">
                    <?php foreach ($campos as $campo): ?>
                    <?php
                        $nome = $campo['nome'] ?? $campo['label'] ?? '';
                        $chave = $campo['name'] ?? $campo['nome'] ?? '';
                        $valor = $dados[$chave] ?? '—';
                        if (is_array($valor)) $valor = implode(', ', $valor);
                    ?>
                    <div class="resp-row">
                        <span class="resp-pergunta"><?= e($nome) ?></span>
                        <span class="resp-resposta"><?= e($valor) ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<style>
.respostas-lista {
    display: flex; flex-direction: column; gap: var(--space-md);
    padding: var(--space-md) 0;
}
.resposta-card {
    border: 1px solid var(--border);
    border-radius: var(--radius);
    overflow: hidden;
    transition: box-shadow 0.2s;
}
.resposta-card:hover { box-shadow: 0 2px 8px rgba(0,0,0,0.06); }
.resposta-header {
    padding: var(--space-sm) var(--space-md);
    background: var(--gray-50);
    display: flex; justify-content: space-between; align-items: center;
}
.resposta-header strong { display: block; margin-bottom: 2px; }
.resposta-dados { padding: 0; }
.resp-row {
    display: flex; justify-content: space-between; align-items: center;
    padding: var(--space-xs) var(--space-md);
    border-bottom: 1px solid var(--border);
    gap: var(--space-md);
}
.resp-row:last-child { border-bottom: none; }
.resp-pergunta { font-size: var(--font-sm); color: var(--text-muted); flex: 0 0 auto; max-width: 50%; }
.resp-resposta { font-size: var(--font-sm); font-weight: 600; text-align: right; word-break: break-word; }
</style>

<script>
document.getElementById('searchRespostas')?.addEventListener('input', function() {
    const term = this.value.toLowerCase();
    document.querySelectorAll('.resposta-card').forEach(card => {
        const searchData = card.dataset.search || '';
        card.style.display = searchData.includes(term) ? '' : 'none';
    });
    TablePagination.refresh('respostasLista');
});
</script>
