<!-- Listagem de Pontos de Venda -->
<div class="section-header" style="margin-bottom: var(--space-lg);">
    <div>
        <p class="text-muted">Cadastre e gerencie os pontos de venda visitados pela equipe.</p>
    </div>
    <a href="/admin/pdvs/criar" class="btn-primary">
        <i class="fa-solid fa-plus"></i> Novo PDV
    </a>
</div>

<div class="section-card">
    <div class="section-header">
        <h3 class="section-title">Pontos de Venda</h3>
        <input type="text" placeholder="Buscar por nome, código, cidade..." class="search-input" id="searchPdvs">
    </div>

    <div class="table-container">
        <table id="tabelaPdvs" data-paginate="true" data-per-page="10">
            <thead>
                <tr>
                    <th>Código</th>
                    <th>Nome</th>
                    <th>CNPJ</th>
                    <th>Cidade / UF</th>
                    <th>Responsável</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($pdvs)): ?>
                    <tr><td colspan="6" class="empty-state">Nenhum ponto de venda cadastrado.</td></tr>
                <?php else: ?>
                    <?php foreach ($pdvs as $pdv): ?>
                    <tr>
                        <td>
                            <?php if (!empty($pdv['codigo'])): ?>
                                <span class="status-badge status-done" style="font-size:11px;"><?= e($pdv['codigo']) ?></span>
                            <?php else: ?>
                                <span class="text-muted">—</span>
                            <?php endif; ?>
                        </td>
                        <td><strong><?= e($pdv['nome']) ?></strong></td>
                        <td><?= e($pdv['cnpj'] ?? '—') ?></td>
                        <td><?= e($pdv['cidade'] ?? '—') ?> / <?= e($pdv['uf'] ?? '—') ?></td>
                        <td><?= e($pdv['responsavel'] ?? '—') ?></td>
                        <td>
                            <div class="action-buttons">
                                <a href="/admin/pdvs/<?= $pdv['id'] ?>" class="btn-icon" title="Detalhes">
                                    <i class="fa-solid fa-eye"></i>
                                </a>
                                <a href="/admin/pdvs/<?= $pdv['id'] ?>/editar" class="btn-icon" title="Editar">
                                    <i class="fa-solid fa-pen"></i>
                                </a>
                                <form method="POST" action="/admin/pdvs/<?= $pdv['id'] ?>/excluir" data-confirm="Excluir este PDV?">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="btn-icon text-danger" title="Excluir">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
document.getElementById('searchPdvs')?.addEventListener('input', function() {
    const term = this.value.toLowerCase();
    document.querySelectorAll('#tabelaPdvs tbody tr').forEach(row => {
        row.style.display = row.textContent.toLowerCase().includes(term) ? '' : 'none';
    });
    TablePagination.refresh('tabelaPdvs');
});
</script>
