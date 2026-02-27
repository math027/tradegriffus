<!-- Listagem de Pesquisas -->
<div class="section-header" style="margin-bottom: var(--space-lg);">
    <div>
        <p class="text-muted">Crie formulários de pesquisa e acompanhe as respostas dos promotores.</p>
    </div>
    <a href="/admin/pesquisas/criar" class="btn-primary">
        <i class="fa-solid fa-plus"></i> Nova Pesquisa
    </a>
</div>

<div class="section-card">
    <div class="section-header">
        <h3 class="section-title">Pesquisas</h3>
    </div>

    <?php if (empty($pesquisas)): ?>
        <div class="empty-state-card">
            <i class="fa-regular fa-clipboard"></i>
            <p>Nenhuma pesquisa criada ainda.</p>
        </div>
    <?php else: ?>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Título</th>
                        <th>Criada por</th>
                        <th>Data</th>
                        <th>Status</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pesquisas as $p): ?>
                    <tr>
                        <td>
                            <strong><?= e($p['titulo']) ?></strong>
                            <?php if ($p['descricao']): ?>
                                <br><small class="text-muted"><?= e(mb_strimwidth($p['descricao'], 0, 60, '...')) ?></small>
                            <?php endif; ?>
                        </td>
                        <td><?= e($p['criado_por_nome']) ?></td>
                        <td><?= data_br($p['created_at']) ?></td>
                        <td>
                            <?php if ($p['ativa']): ?>
                                <span class="status-badge status-done">Ativa</span>
                            <?php else: ?>
                                <span class="status-badge status-justified">Inativa</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="action-buttons">
                                <a href="/admin/pesquisas/<?= $p['id'] ?>/respostas" class="btn-icon" title="Ver Respostas" style="color:var(--primary);">
                                    <i class="fa-solid fa-clipboard-check"></i>
                                </a>
                                <a href="/admin/pesquisas/<?= $p['id'] ?>/editar" class="btn-icon" title="Editar">
                                    <i class="fa-solid fa-pen"></i>
                                </a>
                                <form method="POST" action="/admin/pesquisas/<?= $p['id'] ?>/excluir" data-confirm="Desativar esta pesquisa?">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="btn-icon" title="Desativar" style="color:var(--warning);">
                                        <i class="fa-solid fa-ban"></i>
                                    </button>
                                </form>
                                <form method="POST" action="/admin/pesquisas/<?= $p['id'] ?>/excluir-permanente"
                                      data-confirm="⚠️ EXCLUIR PERMANENTEMENTE esta pesquisa? Esta ação não pode ser desfeita!">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="btn-icon text-danger" title="Excluir permanente">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>
