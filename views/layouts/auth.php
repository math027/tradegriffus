<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= APP_NAME ?> | <?= e($pageTitle ?? 'Login') ?></title>
    
    <link rel="icon" type="image/png" href="/assets/img/icone_tradegriffus.png">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="<?= asset('css/variables.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/reset.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/typography.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/auth.css') ?>">
</head>
<body class="auth-body">
    <?= $content ?>

    <script src="<?= asset('js/app.js') ?>"></script>
</body>
</html>
