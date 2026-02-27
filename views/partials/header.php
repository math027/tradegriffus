<header class="page-header">
    <div class="header-left">
        <button id="mobileMenuBtn" class="btn-menu mobile-only">
            <i class="fa-solid fa-bars"></i>
        </button>
        <div>
            <h2>
                <?php if (!empty($pageIcon)): ?>
                    <i class="<?= e($pageIcon) ?>" style="margin-right:6px;"></i>
                <?php endif; ?>
                <?= e($pageTitle ?? 'Painel') ?>
            </h2>
            <?php if (isset($pageSubtitle)): ?>
                <p class="header-subtitle"><?= e($pageSubtitle) ?></p>
            <?php endif; ?>
        </div>
    </div>
    <div class="header-actions">
        <button class="btn-icon" title="Notificações">
            <i class="fa-regular fa-bell"></i>
        </button>
    </div>
</header>
