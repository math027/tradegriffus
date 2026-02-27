<!-- Formulário de Roteiro (Criar/Editar) -->
<?php
$isEdit = !empty($roteiro);
$action = $isEdit ? "/admin/roteiros/{$roteiro['id']}/atualizar" : '/admin/roteiros/salvar';
?>

<div class="form-page-wrapper">
<div class="section-card form-card form-card-lg">
    <form method="POST" action="<?= $action ?>">
        <?= csrf_field() ?>

        <div class="form-group">
            <label for="titulo">Título do Roteiro</label>
            <input type="text" id="titulo" name="titulo" class="form-control"
                   value="<?= e($roteiro['titulo'] ?? '') ?>" required
                   placeholder="Ex: Rota SP - Zona Sul">
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="promotor_id">Promotor Responsável</label>
                <select id="promotor_id" name="promotor_id" class="form-control" required>
                    <option value="">Selecione...</option>
                    <?php foreach ($promotores as $p): ?>
                        <option value="<?= $p['id'] ?>" <?= ($roteiro['promotor_id'] ?? '') == $p['id'] ? 'selected' : '' ?>>
                            <?= e($p['nome']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <?php if ($isEdit): ?>
            <div class="form-group">
                <label for="status">Status</label>
                <select id="status" name="status" class="form-control">
                    <?php foreach (['pendente','em_andamento','concluido','atrasado'] as $s): ?>
                        <option value="<?= $s ?>" <?= ($roteiro['status'] ?? '') === $s ? 'selected' : '' ?>>
                            <?= status_label($s) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php endif; ?>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="data_inicio">Data Início</label>
                <input type="date" id="data_inicio" name="data_inicio" class="form-control"
                       value="<?= $roteiro['data_inicio'] ?? '' ?>" required>
            </div>
            <div class="form-group">
                <label for="data_fim">Data Fim</label>
                <input type="date" id="data_fim" name="data_fim" class="form-control"
                       value="<?= $roteiro['data_fim'] ?? '' ?>" required>
            </div>
        </div>

        <?php if (!$isEdit): ?>
        <div class="form-group">
            <label>Pontos de Venda (PDVs desta rota)</label>
            <div class="checkbox-list">
                <?php foreach ($pdvs as $pdv): ?>
                <label class="checkbox-item">
                    <input type="checkbox" name="pdvs[]" value="<?= $pdv['id'] ?>">
                    <span><?= e($pdv['nome']) ?></span>
                    <small class="text-muted"><?= e($pdv['cidade'] ?? '') ?>/<?= e($pdv['uf'] ?? '') ?></small>
                </label>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <div class="form-group">
            <label for="observacoes">Observações</label>
            <textarea id="observacoes" name="observacoes" class="form-control" rows="3"
                      placeholder="Instruções ou notas..."><?= e($roteiro['observacoes'] ?? '') ?></textarea>
        </div>

        <div class="form-actions">
            <a href="/admin/roteiros" class="btn-outline">Cancelar</a>
            <button type="submit" class="btn-primary">
                <i class="fa-solid fa-check"></i>
                <?= $isEdit ? 'Salvar Alterações' : 'Criar Roteiro' ?>
            </button>
        </div>
    </form>
</div>
</div>
