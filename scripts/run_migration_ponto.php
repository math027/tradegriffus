<?php
require_once __DIR__ . '/../config/app.php';
require_once CORE_PATH . '/Database.php';

$db = Database::getInstance();

try {
    // 1. Adicionar tipo_contrato
    $db->exec("ALTER TABLE users ADD COLUMN tipo_contrato ENUM('clt','pj') NOT NULL DEFAULT 'pj' AFTER role");
    echo "OK: Added tipo_contrato column\n";
} catch (Exception $e) {
    echo "SKIP tipo_contrato: " . $e->getMessage() . "\n";
}

try {
    // 2. Criar tabela ponto
    $db->exec("
        CREATE TABLE IF NOT EXISTS ponto (
            id              INT AUTO_INCREMENT PRIMARY KEY,
            user_id         INT NOT NULL,
            data            DATE NOT NULL,
            entrada         TIME DEFAULT NULL,
            almoco_saida    TIME DEFAULT NULL,
            almoco_retorno  TIME DEFAULT NULL,
            saida           TIME DEFAULT NULL,
            created_at      DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at      DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY uk_user_data (user_id, data),
            INDEX idx_data (data),
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB
    ");
    echo "OK: Created ponto table\n";
} catch (Exception $e) {
    echo "ERROR ponto table: " . $e->getMessage() . "\n";
}

echo "Migration complete!\n";
