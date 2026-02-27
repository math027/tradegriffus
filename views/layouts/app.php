<?php $user = Auth::user(); ?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <title><?= APP_NAME ?> | <?= e($pageTitle ?? 'Painel') ?></title>
    
    <!-- PWA -->
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#2563eb">
    <!-- iOS PWA -->
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="TradeGriffus">
    <link rel="apple-touch-icon" href="/assets/img/icon-192.png">

    <link rel="icon" type="image/png" href="/assets/img/icone_tradegriffus.png">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="<?= asset('css/variables.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/reset.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/typography.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/components.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/layout.css') ?>">

    <!-- Offline / PWA styles -->
    <style>
        /* Barra de status offline */
        #griffus-offline-bar {
            position: fixed;
            top: 0; left: 0; right: 0;
            z-index: 10000;
            background: linear-gradient(90deg, #dc2626, #ef4444);
            color: #fff;
            padding: 0;
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s ease, padding 0.3s ease;
        }
        #griffus-offline-bar.show {
            max-height: 48px;
            padding: 10px 16px;
        }
        .offline-bar-content {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            font-size: 13px;
            font-weight: 600;
        }
        body.has-offline-bar .app-wrapper {
            margin-top: 48px;
        }

        /* Badge de pendentes no sidebar */
        .pending-sync-indicator {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 8px 16px;
            margin: 8px 12px;
            background: rgba(245, 158, 11, 0.15);
            border: 1px solid rgba(245, 158, 11, 0.3);
            border-radius: 8px;
            color: #f59e0b;
            font-size: 12px;
        }
        .pending-sync-indicator i {
            font-size: 14px;
            animation: pendingPulse 2s infinite;
        }
        @keyframes pendingPulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }

        /* Banner de instalação */
        .pwa-install-banner {
            position: fixed;
            bottom: -120px;
            left: 0; right: 0;
            z-index: 9999;
            padding: 12px 16px;
            background: var(--white, #fff);
            border-top: 1px solid var(--border, #e5e7eb);
            box-shadow: 0 -4px 20px rgba(0,0,0,0.1);
            transition: bottom 0.4s cubic-bezier(0.16, 1, 0.3, 1);
        }
        .pwa-install-banner.show {
            bottom: 0;
        }
        .pwa-install-content {
            display: flex;
            align-items: center;
            gap: 12px;
            max-width: 600px;
            margin: 0 auto;
        }
        .pwa-install-icon img {
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .pwa-install-text {
            flex: 1;
            min-width: 0;
        }
        .pwa-install-text strong {
            display: block;
            font-size: 14px;
            color: var(--text-main, #1f2937);
        }
        .pwa-install-text span {
            font-size: 12px;
            color: var(--text-muted, #6b7280);
        }
        .pwa-install-actions {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .pwa-install-btn {
            padding: 8px 20px;
            background: var(--primary, #2563eb);
            color: #fff;
            border: none;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            white-space: nowrap;
        }
        .pwa-install-btn:hover {
            background: var(--primary-hover, #1d4ed8);
        }
        .pwa-install-dismiss {
            width: 32px; height: 32px;
            border-radius: 50%;
            background: var(--gray-100, #f3f4f6);
            border: none;
            cursor: pointer;
            color: var(--text-muted, #6b7280);
            display: flex; align-items: center; justify-content: center;
            font-size: 14px;
        }

        /* Floating Sync FAB */
        .sync-fab {
            position: fixed;
            bottom: 24px;
            right: 24px;
            z-index: 9998;
            width: 56px;
            height: 56px;
            border-radius: 50%;
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
            cursor: pointer;
            box-shadow: 0 4px 16px rgba(37, 99, 235, 0.4);
            transition: all 0.3s ease;
        }
        .sync-fab:hover {
            transform: scale(1.1);
            box-shadow: 0 6px 24px rgba(37, 99, 235, 0.5);
        }
        .sync-fab:active {
            transform: scale(0.95);
        }
        .sync-fab .sync-fab-badge {
            position: absolute;
            top: -4px;
            right: -4px;
            min-width: 22px;
            height: 22px;
            border-radius: 11px;
            background: #f59e0b;
            color: #fff;
            font-size: 11px;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0 5px;
            border: 2px solid #fff;
            box-shadow: 0 2px 6px rgba(0,0,0,0.15);
        }
        .sync-fab.syncing {
            background: linear-gradient(135deg, #f59e0b, #d97706);
            box-shadow: 0 4px 16px rgba(245, 158, 11, 0.4);
        }
        .sync-fab.syncing .sync-fab-badge {
            display: none;
        }
        .sync-fab.synced {
            background: linear-gradient(135deg, #10b981, #059669);
            box-shadow: 0 4px 16px rgba(16, 185, 129, 0.4);
        }
        .sync-fab.synced .sync-fab-badge {
            display: none;
        }
    </style>
    
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <div class="app-wrapper">
        <?php require VIEWS_PATH . '/partials/sidebar.php'; ?>

        <main class="main-content">
            <?php require VIEWS_PATH . '/partials/header.php'; ?>
            
            <div class="page-content">
                <?= $content ?>
            </div>
        </main>
    </div>

    <script src="<?= asset('js/img-process.js') ?>"></script>
    <script src="<?= asset('js/app.js') ?>"></script>
    <script src="<?= asset('js/sidebar.js') ?>"></script>
    <script src="<?= asset('js/pagination.js') ?>"></script>

    <!-- PWA + Offline -->
    <script src="<?= asset('js/offline-db.js') ?>"></script>
    <script src="<?= asset('js/offline-sync.js') ?>"></script>
    <script src="<?= asset('js/offline-ui.js') ?>"></script>
    <script src="<?= asset('js/pwa-install.js') ?>"></script>

    <script>
        // Registrar Service Worker
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/sw.js', { scope: '/' })
                    .then(reg => {
                        console.log('[PWA] Service Worker registrado:', reg.scope);
                    })
                    .catch(err => {
                        console.warn('[PWA] Falha ao registrar SW:', err);
                    });
            });
        }
    </script>
</body>
</html>
