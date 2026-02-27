<?php
/**
 * Router para o PHP built-in server (php -S).
 * O .htaccess só funciona com Apache.
 * 
 * Uso: php -S localhost:8000 -t public public/router.php
 */

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Se o arquivo estático existe (CSS, JS, imagens...), serve normalmente
$staticFile = __DIR__ . $uri;
if ($uri !== '/' && file_exists($staticFile) && is_file($staticFile)) {
    return false; // Deixa o built-in server servir o arquivo
}

// Caso contrário, delega tudo ao front controller (index.php)
require_once __DIR__ . '/index.php';
