<!-- Listagem de Colaboradores -->
<div class="section-header" style="margin-bottom: var(--space-lg);">
    <div>
        <p class="text-muted">Gerencie gestores e promotores do sistema.</p>
    </div>
    <a href="/admin/colaboradores/criar" class="btn-primary">
        <i class="fa-solid fa-plus"></i> Novo Colaborador
    </a>
</div>

<div class="section-card">
    <div class="section-header">
        <h3 class="section-title">Equipe</h3>
        <input type="text" placeholder="Buscar colaborador..." class="search-input" id="searchColab">
    </div>

    <div class="table-container">
        <table id="tabelaColab" data-paginate="true" data-per-page="10">
            <thead>
                <tr>
                    <th>Nome</th>
                    <th>E-mail</th>
                    <th>Perfil</th>
                    <th>Telefone</th>
                    <th>Status</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($colaboradores)): ?>
                    <tr><td colspan="6" class="empty-state">Nenhum colaborador cadastrado.</td></tr>
                <?php else: ?>
                    <?php foreach ($colaboradores as $c): ?>
                    <tr class="<?= !$c['ativo'] ? 'row-inactive' : '' ?>">
                        <td class="promoter-cell">
                            <?= avatar_html($c['avatar'] ?? null, $c['nome'], true) ?>
                            <strong><?= e($c['nome']) ?></strong>
                        </td>
                        <td><?= e($c['email']) ?></td>
                        <td>
                            <span class="status-badge <?= $c['role'] === 'admin' ? 'status-progress' : 'status-pending' ?>">
                                <?= $c['role'] === 'admin' ? 'Gestor' : 'Promotor' ?>
                            </span>
                        </td>
                        <td><?= e($c['telefone'] ?? '-') ?></td>
                        <td>
                            <?php if ($c['ativo']): ?>
                                <span class="dot" style="display:inline-block;"></span> Ativo
                            <?php else: ?>
                                <span class="status-badge" style="background:var(--gray-100); color:var(--text-light);">
                                    <i class="fa-solid fa-ban"></i> Inativo
                                </span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="action-buttons">
                                <!-- Editar -->
                                <a href="/admin/colaboradores/<?= $c['id'] ?>/editar" class="btn-icon" title="Editar">
                                    <i class="fa-solid fa-pen"></i>
                                </a>

                                <?php if ($c['ativo']): ?>
                                    <!-- Desativar (soft delete) -->
                                    <form method="POST" action="/admin/colaboradores/<?= $c['id'] ?>/excluir"
                                          data-confirm="Desativar este colaborador? Ele ficará inativo mas poderá ser reativado.">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="btn-icon" title="Desativar" style="color:var(--warning);">
                                            <i class="fa-solid fa-user-slash"></i>
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <!-- Reativar -->
                                    <form method="POST" action="/admin/colaboradores/<?= $c['id'] ?>/reativar"
                                          data-confirm="Reativar este colaborador?">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="btn-icon" title="Reativar" style="color:var(--success);">
                                            <i class="fa-solid fa-user-check"></i>
                                        </button>
                                    </form>
                                <?php endif; ?>

                                <!-- Excluir permanente (sempre visível) -->
                                <form method="POST" action="/admin/colaboradores/<?= $c['id'] ?>/excluir-permanente"
                                      data-confirm="⚠️ EXCLUIR PERMANENTEMENTE este colaborador? Esta ação não pode ser desfeita!">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="btn-icon text-danger" title="Excluir permanente">
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

<style>
.row-inactive { opacity: 0.6; background: var(--gray-50); }
.row-inactive:hover { opacity: 0.85; }
</style>

<script>
document.getElementById('searchColab')?.addEventListener('input', function() {
    const term = this.value.toLowerCase();
    document.querySelectorAll('#tabelaColab tbody tr').forEach(row => {
        row.style.display = row.textContent.toLowerCase().includes(term) ? '' : 'none';
    });
    TablePagination.refresh('tabelaColab');
});
</script>
