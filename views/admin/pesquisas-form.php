<!-- Formulário de Pesquisa (Criar/Editar) -->
<?php
$isEdit = !empty($pesquisa);
$action = $isEdit ? "/admin/pesquisas/{$pesquisa['id']}/atualizar" : '/admin/pesquisas/salvar';
$camposExistentes = $isEdit ? $pesquisa['campos'] : '[]';
?>

<div class="form-page-wrapper">
<div class="section-card form-card form-card-lg">
    <form method="POST" action="<?= $action ?>" id="pesquisaForm">
        <?= csrf_field() ?>
        <input type="hidden" name="campos" id="camposJson" value='<?= e($camposExistentes) ?>'>

        <div class="form-group">
            <label for="titulo">Título da Pesquisa</label>
            <input type="text" id="titulo" name="titulo" class="form-control"
                   value="<?= e($pesquisa['titulo'] ?? '') ?>" required
                   placeholder="Ex: Pesquisa de preço - Concorrência">
        </div>

        <div class="form-group">
            <label for="descricao">Descrição</label>
            <textarea id="descricao" name="descricao" class="form-control" rows="2"
                      placeholder="Breve descrição da pesquisa..."><?= e($pesquisa['descricao'] ?? '') ?></textarea>
        </div>

        <?php if ($isEdit): ?>
        <div class="form-group">
            <label>
                <input type="checkbox" name="ativa" <?= $pesquisa['ativa'] ? 'checked' : '' ?>> Pesquisa ativa
            </label>
        </div>
        <?php endif; ?>

        <!-- Builder de campos dinâmicos -->
        <div class="form-group">
            <label>Campos da Pesquisa</label>
            <div id="camposContainer" class="campos-builder"></div>
            <button type="button" class="btn-outline" onclick="addCampo()" style="margin-top: var(--space-sm);">
                <i class="fa-solid fa-plus"></i> Adicionar Campo
            </button>
        </div>

        <div class="form-actions">
            <a href="/admin/pesquisas" class="btn-outline">Cancelar</a>

            <?php if ($isEdit): ?>
            <form method="POST" action="/admin/pesquisas/<?= $pesquisa['id'] ?>/excluir-permanente"
                  data-confirm="⚠️ EXCLUIR PERMANENTEMENTE esta pesquisa? Esta ação não pode ser desfeita!"
                  style="margin-left:auto;">
                <?= csrf_field() ?>
                <button type="submit" class="btn-outline" style="color:var(--danger); border-color:var(--danger);">
                    <i class="fa-solid fa-trash"></i> Excluir
                </button>
            </form>
            <?php endif; ?>

            <button type="submit" class="btn-primary">
                <i class="fa-solid fa-check"></i>
                <?= $isEdit ? 'Salvar' : 'Criar Pesquisa' ?>
            </button>
        </div>
    </form>
</div>
</div>

<script>
let campos = [];

try { campos = JSON.parse(document.getElementById('camposJson').value); } catch(e) {}

function renderCampos() {
    const container = document.getElementById('camposContainer');
    container.innerHTML = '';
    campos.forEach((campo, i) => {
        container.innerHTML += `
            <div class="campo-item">
                <div class="campo-row">
                    <input type="text" class="form-control" value="${campo.label}" 
                           onchange="campos[${i}].label = this.value" placeholder="Nome do campo">
                    <select class="form-control" onchange="campos[${i}].type = this.value" style="max-width:160px;">
                        <option value="text" ${campo.type === 'text' ? 'selected' : ''}>Texto</option>
                        <option value="number" ${campo.type === 'number' ? 'selected' : ''}>Número</option>
                        <option value="select" ${campo.type === 'select' ? 'selected' : ''}>Seleção</option>
                        <option value="photo" ${campo.type === 'photo' ? 'selected' : ''}>Foto</option>
                        <option value="boolean" ${campo.type === 'boolean' ? 'selected' : ''}>Sim/Não</option>
                    </select>
                    <label style="white-space:nowrap;font-size:13px;">
                        <input type="checkbox" ${campo.required ? 'checked' : ''} 
                               onchange="campos[${i}].required = this.checked"> Obrig.
                    </label>
                    <button type="button" class="btn-icon text-danger" onclick="removeCampo(${i})">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
            </div>`;
    });
}

function addCampo() {
    campos.push({ label: '', type: 'text', required: false });
    renderCampos();
}

function removeCampo(i) {
    campos.splice(i, 1);
    renderCampos();
}

document.getElementById('pesquisaForm').addEventListener('submit', function() {
    document.getElementById('camposJson').value = JSON.stringify(campos);
});

renderCampos();
</script>
