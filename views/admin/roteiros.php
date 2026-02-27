<!-- Listagem de Roteiros -->
<div class="section-header" style="margin-bottom: var(--space-lg);">
    <div>
        <p class="text-muted">Gerencie os roteiros e acompanhe as visitas dos promotores.</p>
    </div>
    <a href="/admin/roteiros/criar" class="btn-primary">
        <i class="fa-solid fa-plus"></i> Novo Roteiro
    </a>
</div>

<div class="section-card">
    <div class="section-header">
        <h3 class="section-title">Todos os Roteiros</h3>
        <input type="text" placeholder="Buscar roteiro..." class="search-input" id="searchRoteiros">
    </div>

    <div class="table-container">
        <table id="tabelaRoteiros">
            <thead>
                <tr>
                    <th>Título</th>
                    <th>Promotor</th>
                    <th>Período</th>
                    <th>Status</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($roteiros)): ?>
                    <tr><td colspan="5" class="empty-state">Nenhum roteiro cadastrado.</td></tr>
                <?php else: ?>
                    <?php foreach ($roteiros as $r): ?>
                    <tr>
                        <td><strong><?= e($r['titulo']) ?></strong></td>
                        <td class="promoter-cell">
                            <?= avatar_html($r['promotor_avatar'] ?? null, $r['promotor_nome'], true) ?>
                            <?= e($r['promotor_nome']) ?>
                        </td>
                        <td><?= data_br($r['data_inicio']) ?> — <?= data_br($r['data_fim']) ?></td>
                        <td>
                            <span class="status-badge <?= status_class($r['status']) ?>">
                                <?= status_label($r['status']) ?>
                            </span>
                        </td>
                        <td>
                            <div class="action-buttons">
                                <a href="/admin/roteiros/<?= $r['id'] ?>" class="btn-icon" title="Ver detalhes">
                                    <i class="fa-solid fa-eye"></i>
                                </a>
                                <a href="/admin/roteiros/<?= $r['id'] ?>/editar" class="btn-icon" title="Editar">
                                    <i class="fa-solid fa-pen"></i>
                                </a>
                                <form method="POST" action="/admin/roteiros/<?= $r['id'] ?>/excluir" data-confirm="Tem certeza que deseja excluir este roteiro?">
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
document.getElementById('searchRoteiros')?.addEventListener('input', function() {
    const term = this.value.toLowerCase();
    document.querySelectorAll('#tabelaRoteiros tbody tr').forEach(row => {
        row.style.display = row.textContent.toLowerCase().includes(term) ? '' : 'none';
    });
});
</script>
