<!-- Pesquisas do Promotor -->
<div class="promotor-welcome">
    <h3>Pesquisas</h3>
    <p>Preencha as pesquisas disponíveis durante suas visitas.</p>
</div>

<?php if (empty($pesquisas)): ?>
    <div class="section-card">
        <div class="empty-state-card">
            <i class="fa-regular fa-clipboard"></i>
            <p>Nenhuma pesquisa disponível no momento.</p>
        </div>
    </div>
<?php else: ?>
    <?php foreach ($pesquisas as $p): ?>
    <div class="section-card">
        <div class="section-header">
            <h3 class="section-title"><?= e($p['titulo']) ?></h3>
        </div>
        <?php if ($p['descricao']): ?>
            <p class="text-muted text-sm" style="margin-bottom: var(--space-md);"><?= e($p['descricao']) ?></p>
        <?php endif; ?>

        <form method="POST" action="/promotor/pesquisas/<?= $p['id'] ?>/responder">
            <?= csrf_field() ?>

            <div class="form-group">
                <label for="visita_<?= $p['id'] ?>">Selecione a visita</label>
                <select name="visita_id" id="visita_<?= $p['id'] ?>" class="form-control" required>
                    <option value="">Selecione uma visita em andamento...</option>
                    <?php if (!empty($visitasAtivas)): ?>
                        <?php foreach ($visitasAtivas as $v): ?>
                            <option value="<?= $v['id'] ?>"><?= e($v['pdv_nome']) ?> — <?= data_br($v['data_prevista']) ?></option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>

            <?php
            $campos = json_decode($p['campos'], true) ?: [];
            foreach ($campos as $i => $campo):
            ?>
            <div class="form-group">
                <label for="campo_<?= $p['id'] ?>_<?= $i ?>"><?= e($campo['label']) ?></label>

                <?php if ($campo['type'] === 'text'): ?>
                    <input type="text" id="campo_<?= $p['id'] ?>_<?= $i ?>" name="respostas[<?= $i ?>]" 
                           class="form-control" <?= $campo['required'] ? 'required' : '' ?>>

                <?php elseif ($campo['type'] === 'number'): ?>
                    <input type="number" id="campo_<?= $p['id'] ?>_<?= $i ?>" name="respostas[<?= $i ?>]" 
                           class="form-control" step="any" <?= $campo['required'] ? 'required' : '' ?>>

                <?php elseif ($campo['type'] === 'boolean'): ?>
                    <select id="campo_<?= $p['id'] ?>_<?= $i ?>" name="respostas[<?= $i ?>]" 
                            class="form-control" <?= $campo['required'] ? 'required' : '' ?>>
                        <option value="">Selecione</option>
                        <option value="sim">Sim</option>
                        <option value="nao">Não</option>
                    </select>

                <?php elseif ($campo['type'] === 'select'): ?>
                    <select id="campo_<?= $p['id'] ?>_<?= $i ?>" name="respostas[<?= $i ?>]" 
                            class="form-control" <?= $campo['required'] ? 'required' : '' ?>>
                        <option value="">Selecione</option>
                        <?php if (!empty($campo['options'])): ?>
                            <?php foreach ($campo['options'] as $opt): ?>
                                <option value="<?= e($opt) ?>"><?= e($opt) ?></option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>

                <?php elseif ($campo['type'] === 'photo'): ?>
                    <input type="file" id="campo_<?= $p['id'] ?>_<?= $i ?>" name="respostas[<?= $i ?>]" 
                           class="form-control" accept="image/*" capture="environment">
                <?php endif; ?>
            </div>
            <?php endforeach; ?>

            <button type="submit" class="btn-primary" style="width: 100%;">
                <i class="fa-solid fa-paper-plane"></i> Enviar Respostas
            </button>
        </form>
    </div>
    <?php endforeach; ?>
<?php endif; ?>
