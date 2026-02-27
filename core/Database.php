<?php
/**
 * Singleton de conexão PDO com MySQL
 */
class Database
{
    private static ?PDO $instance = null;

    public static function getInstance(): PDO
    {
        if (self::$instance === null) {
            $config = require CONFIG_PATH . '/database.php';

            $port = !empty($config['port']) ? ';port=' . (int)$config['port'] : '';
            $dsn = sprintf(
                'mysql:host=%s%s;dbname=%s;charset=%s',
                $config['host'],
                $port,
                $config['database'],
                $config['charset']
            );

            self::$instance = new PDO($dsn, $config['username'], $config['password'], $config['options']);
        }

        return self::$instance;
    }

    // Impede instanciação direta
    private function __construct() {}
    private function __clone() {}
}
