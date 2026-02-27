<!-- Detalhe do Roteiro -->
<div class="section-header" style="margin-bottom: var(--space-lg);">
    <div>
        <span class="status-badge <?= status_class($roteiro['status']) ?>"><?= status_label($roteiro['status']) ?></span>
        <p class="text-muted" style="margin-top: var(--space-sm);">
            Promotor: <strong><?= e($roteiro['promotor_nome']) ?></strong> &bull;
            <?= data_br($roteiro['data_inicio']) ?> a <?= data_br($roteiro['data_fim']) ?>
        </p>
    </div>
    <div class="header-actions">
        <a href="/admin/roteiros/<?= $roteiro['id'] ?>/editar" class="btn-outline">
            <i class="fa-solid fa-pen"></i> Editar
        </a>
        <a href="/admin/roteiros" class="btn-icon"><i class="fa-solid fa-arrow-left"></i></a>
    </div>
</div>

<?php if (!empty($roteiro['observacoes'])): ?>
<div class="section-card">
    <h4 style="margin-bottom: var(--space-sm);">Observações</h4>
    <p><?= nl2br(e($roteiro['observacoes'])) ?></p>
</div>
<?php endif; ?>

<div class="section-card">
    <div class="section-header">
        <h3 class="section-title">Visitas do Roteiro</h3>
        <span class="text-muted text-sm"><?= count($visitas) ?> visita(s)</span>
    </div>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>PDV</th>
                    <th>Data Prevista</th>
                    <th>Check-in</th>
                    <th>Check-out</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($visitas)): ?>
                    <tr><td colspan="5" class="empty-state">Nenhuma visita vinculada.</td></tr>
                <?php else: ?>
                    <?php foreach ($visitas as $v): ?>
                    <tr>
                        <td>
                            <strong><?= e($v['pdv_nome']) ?></strong>
                            <br><small class="text-muted"><?= e($v['pdv_endereco'] ?? '') ?></small>
                        </td>
                        <td><?= data_br($v['data_prevista']) ?></td>
                        <td><?= hora_br($v['checkin_at'] ?? '') ?></td>
                        <td><?= hora_br($v['checkout_at'] ?? '') ?></td>
                        <td>
                            <span class="status-badge <?= status_class($v['status']) ?>">
                                <?= status_label($v['status']) ?>
                            </span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
