<!-- Responder Pesquisa (promotor) — PDV pré-preenchido quando vindo do workflow -->
<?php
$temPdvPreenchido = !empty($pdvId) && !empty($pdvAtual);
$temVisita = !empty($visitaId);
?>

<div class="section-card" style="max-width: 700px; margin: 0 auto;">
    <!-- Header -->
    <div style="text-align: center; margin-bottom: var(--space-lg);">
        <h3><?= e($pesquisa['titulo']) ?></h3>
        <?php if ($pesquisa['descricao']): ?>
            <p class="text-muted" style="margin-top: var(--space-sm);"><?= e($pesquisa['descricao']) ?></p>
        <?php endif; ?>
    </div>

    <!-- Info do PDV (quando vindo do workflow) -->
    <?php if ($temPdvPreenchido): ?>
    <div style="background:var(--gray-50); border-radius:var(--radius); padding:var(--space-sm) var(--space-md); margin-bottom:var(--space-md); display:flex; align-items:center; gap:var(--space-sm);">
        <i class="fa-solid fa-store" style="color:var(--primary);"></i>
        <div>
            <strong style="font-size:var(--font-sm);"><?= e($pdvAtual['nome']) ?></strong>
            <p class="text-xs text-muted" style="margin:0;"><?= e($pdvAtual['cidade'] ?? $pdvAtual['endereco'] ?? '') ?></p>
        </div>
    </div>
    <?php endif; ?>

    <form method="POST" action="/promotor/pesquisas/<?= $pesquisa['id'] ?>/salvar" enctype="multipart/form-data">
        <?= csrf_field() ?>

        <!-- Campos ocultos do contexto -->
        <?php if ($temVisita): ?>
            <input type="hidden" name="visita_id" value="<?= $visitaId ?>">
        <?php endif; ?>

        <?php if ($temPdvPreenchido): ?>
            <!-- PDV já preenchido -->
            <input type="hidden" name="pdv_id" value="<?= $pdvId ?>">
        <?php else: ?>
            <!-- Selector de PDV (quando não vem de workflow) -->
            <div class="form-group">
                <label for="pdv_id">Ponto de Venda</label>
                <select name="pdv_id" id="pdv_id" class="form-control" required>
                    <option value="">Selecione o PDV...</option>
                    <?php foreach ($pdvs as $pdv): ?>
                        <option value="<?= $pdv['id'] ?>"><?= e($pdv['nome']) ?> — <?= e($pdv['cidade'] ?? '') ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        <?php endif; ?>

        <!-- Campos da pesquisa (todos opcionais) -->
        <?php
        $campos = json_decode($pesquisa['campos'], true) ?: [];
        foreach ($campos as $i => $campo):
        ?>
        <div class="form-group">
            <label for="campo_<?= $i ?>"><?= e($campo['label']) ?></label>

            <?php if ($campo['type'] === 'text'): ?>
                <input type="text" id="campo_<?= $i ?>" name="dados[<?= $i ?>]"
                       class="form-control" placeholder="(opcional)">

            <?php elseif ($campo['type'] === 'number'): ?>
                <input type="number" id="campo_<?= $i ?>" name="dados[<?= $i ?>]"
                       class="form-control" step="any" placeholder="(opcional)">

            <?php elseif ($campo['type'] === 'boolean'): ?>
                <select id="campo_<?= $i ?>" name="dados[<?= $i ?>]" class="form-control">
                    <option value="">Selecione (opcional)</option>
                    <option value="sim">Sim</option>
                    <option value="nao">Não</option>
                </select>

            <?php elseif ($campo['type'] === 'select'): ?>
                <select id="campo_<?= $i ?>" name="dados[<?= $i ?>]" class="form-control">
                    <option value="">Selecione (opcional)</option>
                    <?php if (!empty($campo['options'])): ?>
                        <?php foreach ($campo['options'] as $opt): ?>
                            <option value="<?= e($opt) ?>"><?= e($opt) ?></option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>

            <?php elseif ($campo['type'] === 'photo'): ?>
                <input type="file" id="campo_<?= $i ?>" name="dados_foto_<?= $i ?>"
                       class="form-control" accept="image/*" capture="environment">
            <?php endif; ?>
        </div>
        <?php endforeach; ?>

        <div class="form-actions">
            <?php if ($temVisita): ?>
                <a href="/promotor/visita/<?= $visitaId ?>" class="btn-outline">Voltar à visita</a>
            <?php else: ?>
                <a href="/promotor/dashboard" class="btn-outline">Voltar</a>
            <?php endif; ?>
            <button type="submit" class="btn-primary">
                <i class="fa-solid fa-paper-plane"></i> Enviar Respostas
            </button>
        </div>
    </form>
</div>
