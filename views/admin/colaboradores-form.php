<!-- Formulário de Colaborador (Criar/Editar) -->
<?php
$isEdit = !empty($colaborador);
$action = $isEdit ? "/admin/colaboradores/{$colaborador['id']}/atualizar" : '/admin/colaboradores/salvar';
?>

<div class="form-page-wrapper">
<div class="section-card form-card">
    <?php if (!empty($error)): ?>
        <div class="alert alert-danger">
            <i class="fa-solid fa-circle-exclamation"></i> <?= e($error) ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="<?= $action ?>">
        <?= csrf_field() ?>

        <div class="form-group">
            <label for="nome">Nome Completo</label>
            <input type="text" id="nome" name="nome" class="form-control"
                   value="<?= e($colaborador['nome'] ?? '') ?>" required>
        </div>

        <div class="form-group">
            <label for="email">E-mail</label>
            <input type="email" id="email" name="email" class="form-control"
                   value="<?= e($colaborador['email'] ?? '') ?>" required>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="senha"><?= $isEdit ? 'Nova Senha (deixe em branco para manter)' : 'Senha' ?></label>
                <input type="password" id="senha" name="senha" class="form-control"
                       <?= $isEdit ? '' : 'required' ?> minlength="6">
            </div>
            <div class="form-group">
                <label for="role">Perfil</label>
                <select id="role" name="role" class="form-control" required>
                    <option value="promotor" <?= ($colaborador['role'] ?? 'promotor') === 'promotor' ? 'selected' : '' ?>>Promotor</option>
                    <option value="admin" <?= ($colaborador['role'] ?? '') === 'admin' ? 'selected' : '' ?>>Gestor (Admin)</option>
                </select>
            </div>
            <div class="form-group">
                <label for="tipo_contrato">Tipo de Contrato</label>
                <select id="tipo_contrato" name="tipo_contrato" class="form-control" required>
                    <option value="pj" <?= ($colaborador['tipo_contrato'] ?? 'pj') === 'pj' ? 'selected' : '' ?>>PJ</option>
                    <option value="clt" <?= ($colaborador['tipo_contrato'] ?? '') === 'clt' ? 'selected' : '' ?>>CLT</option>
                </select>
            </div>
        </div>

        <div class="form-group">
            <label for="telefone">Telefone</label>
            <input type="text" id="telefone" name="telefone" class="form-control"
                   value="<?= e($colaborador['telefone'] ?? '') ?>" placeholder="(00) 00000-0000">
        </div>

        <div class="form-actions">
            <a href="/admin/colaboradores" class="btn-outline">Cancelar</a>

            <?php if ($isEdit): ?>
            <form method="POST" action="/admin/colaboradores/<?= $colaborador['id'] ?>/excluir-permanente"
                  data-confirm="⚠️ EXCLUIR PERMANENTEMENTE este colaborador? Esta ação não pode ser desfeita!"
                  style="margin-left:auto;">
                <?= csrf_field() ?>
                <button type="submit" class="btn-outline" style="color:var(--danger); border-color:var(--danger);">
                    <i class="fa-solid fa-trash"></i> Excluir
                </button>
            </form>
            <?php endif; ?>

            <button type="submit" class="btn-primary">
                <i class="fa-solid fa-check"></i>
                <?= $isEdit ? 'Salvar' : 'Cadastrar' ?>
            </button>
        </div>
    </form>
</div>
</div>
