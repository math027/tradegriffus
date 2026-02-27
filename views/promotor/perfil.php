<!-- Perfil do Promotor -->
<div class="section-card" style="max-width: 600px; margin: 0 auto;">
    <div style="text-align: center; margin-bottom: var(--space-lg);">
        <!-- Avatar com opção de alterar -->
        <div class="perfil-avatar-wrapper">
            <?php if (!empty($usuario['avatar'])): ?>
                <img src="/<?= e($usuario['avatar']) ?>" alt="Avatar" class="perfil-avatar-img">
            <?php else: ?>
                <div class="activity-avatar" style="width: 90px; height: 90px; font-size: 32px; margin: 0 auto;">
                    <?= iniciais(Auth::user()['nome']) ?>
                </div>
            <?php endif; ?>
            <label class="perfil-avatar-edit" for="avatarInput" title="Alterar foto">
                <i class="fa-solid fa-camera"></i>
            </label>
        </div>
        <h3><?= e(Auth::user()['nome']) ?></h3>
        <p class="text-muted"><?= e($usuario['email']) ?></p>
        <span class="status-badge status-pending" style="margin-top: var(--space-sm);">Promotor</span>
    </div>

    <?php if (!empty($success)): ?>
        <div class="alert alert-success">
            <i class="fa-solid fa-check"></i> <?= e($success) ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger">
            <i class="fa-solid fa-circle-exclamation"></i> <?= e($error) ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="/promotor/perfil" enctype="multipart/form-data">
        <!-- Input oculto do avatar -->
        <input type="file" id="avatarInput" name="avatar" accept="image/jpeg,image/png,image/webp"
               style="display:none;" onchange="previewAvatar(this)">

        <div class="form-group">
            <label for="nome">Nome Completo</label>
            <input type="text" id="nome" name="nome" class="form-control"
                   value="<?= e($usuario['nome']) ?>" required>
        </div>

        <div class="form-group">
            <label for="email">E-mail</label>
            <input type="email" class="form-control" value="<?= e($usuario['email']) ?>" disabled>
            <small class="text-muted">Contate o gestor para alterar o e-mail.</small>
        </div>

        <div class="form-group">
            <label for="telefone">Telefone</label>
            <input type="text" id="telefone" name="telefone" class="form-control"
                   value="<?= e($usuario['telefone'] ?? '') ?>" placeholder="(00) 00000-0000">
        </div>

        <hr style="margin: var(--space-lg) 0; border-color: var(--border);">

        <div class="form-group">
            <label for="senha_atual">Senha Atual (obrigatória para alterar senha)</label>
            <input type="password" id="senha_atual" name="senha_atual" class="form-control">
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="nova_senha">Nova Senha</label>
                <input type="password" id="nova_senha" name="nova_senha" class="form-control" minlength="6">
            </div>
            <div class="form-group">
                <label for="confirmar_senha">Confirmar Nova Senha</label>
                <input type="password" id="confirmar_senha" name="confirmar_senha" class="form-control" minlength="6">
            </div>
        </div>

        <button type="submit" class="btn-primary" style="width: 100%; margin-top: var(--space-md);">
            <i class="fa-solid fa-check"></i> Salvar Alterações
        </button>
    </form>
</div>

<style>
.perfil-avatar-wrapper {
    position: relative; display: inline-block; margin-bottom: var(--space-md);
}
.perfil-avatar-img {
    width: 90px; height: 90px; border-radius: 50%; object-fit: cover;
    border: 3px solid var(--primary); box-shadow: 0 2px 8px rgba(0,0,0,0.15);
}
.perfil-avatar-edit {
    position: absolute; bottom: 2px; right: 2px;
    width: 30px; height: 30px; border-radius: 50%;
    background: var(--primary); color: white;
    display: flex; align-items: center; justify-content: center;
    font-size: 13px; cursor: pointer;
    box-shadow: 0 2px 6px rgba(0,0,0,0.2);
    transition: background 0.2s;
}
.perfil-avatar-edit:hover { background: #1d4ed8; }
</style>

<script>
function previewAvatar(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const wrapper = document.querySelector('.perfil-avatar-wrapper');
            // Replace existing avatar (initials or image)
            const existing = wrapper.querySelector('.perfil-avatar-img') || wrapper.querySelector('.activity-avatar');
            if (existing) {
                const img = document.createElement('img');
                img.src = e.target.result;
                img.alt = 'Avatar';
                img.className = 'perfil-avatar-img';
                existing.replaceWith(img);
            }
        };
        reader.readAsDataURL(input.files[0]);
    }
}
</script>
