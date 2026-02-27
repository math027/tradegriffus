<div class="auth-wrapper">
    <div class="auth-brand">
        <div class="bg-pattern"></div>
        <div class="brand-content">
            <div class="logo-area">
                <img src="/assets/img/logo_tradegriffus.png" alt="TradeGriffus" class="login-logo-img">
            </div>
            <p class="brand-desc">Plataforma inteligente para gestão de Trade Marketing.</p>
            <p class="brand-subdesc">Acompanhe visitas, roteiros e performance em tempo real.</p>
        </div>
    </div>

    <div class="auth-form-side">
        <div class="auth-box">
            <div class="auth-mobile-logo" id="mobileLogo">
                <img src="/assets/img/logo_tradegriffus.png" alt="TradeGriffus" style="height:40px;">
            </div>

            <div class="auth-header">
                <h2>Bem-vindo de volta</h2>
                <p>Insira suas credenciais para acessar o painel.</p>
            </div>

            <?php if (!empty($error)): ?>
                <div class="alert alert-danger">
                    <i class="fa-solid fa-circle-exclamation"></i>
                    <?= e($error) ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="/login">
                <?= csrf_field() ?>
                
                <div class="input-group">
                    <label for="email">E-mail Corporativo</label>
                    <input type="email" id="email" name="email" 
                           placeholder="nome@empresa.com.br" 
                           value="<?= e($email ?? '') ?>" required>
                </div>

                <div class="input-group">
                    <label for="password">Senha</label>
                    <input type="password" id="password" name="password" 
                           placeholder="••••••••" required>
                </div>

                <button type="submit" class="btn-login" id="loginBtn">
                    <i class="fa-solid fa-right-to-bracket"></i> Acessar Plataforma
                </button>
            </form>

            <a href="#" class="forgot-link">Esqueceu sua senha?</a>
        </div>
    </div>
</div>
