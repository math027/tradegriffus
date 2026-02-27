<?php
/**
 * Configurações gerais da aplicação
 */

define('APP_NAME', 'TradeGriffus');
define('APP_VERSION', '2.0.0');

// Ambiente: 'development' ou 'production'
define('APP_ENV', 'production');

// Suprimir erros em produção
if (APP_ENV === 'production') {
    error_reporting(0);
    ini_set('display_errors', '0');
    ini_set('log_errors', '1');
} else {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
}

// URL base — detecta automaticamente
$_scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$_host   = $_SERVER['HTTP_HOST'] ?? 'localhost:8000';
define('BASE_URL', $_scheme . '://' . $_host);

// Caminhos absolutos
define('ROOT_PATH', dirname(__DIR__));
define('CONFIG_PATH', ROOT_PATH . '/config');
define('CORE_PATH', ROOT_PATH . '/core');
define('CONTROLLERS_PATH', ROOT_PATH . '/controllers');
define('MODELS_PATH', ROOT_PATH . '/models');
define('VIEWS_PATH', ROOT_PATH . '/views');
define('PUBLIC_PATH', ROOT_PATH . '/public');
define('UPLOAD_PATH', PUBLIC_PATH . '/uploads');

// Sessão
define('SESSION_LIFETIME', 3600 * 8); // 8 horas

// Timezone
date_default_timezone_set('America/Sao_Paulo');
