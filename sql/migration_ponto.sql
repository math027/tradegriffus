-- =============================================
-- Migration: Ponto (Time Clock) + CLT/PJ flag
-- =============================================

USE griffu80_trade;

-- Adicionar tipo de contrato ao usuário
ALTER TABLE users ADD COLUMN tipo_contrato ENUM('clt','pj') NOT NULL DEFAULT 'pj' AFTER role;

-- Tabela de registros de ponto
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
) ENGINE=InnoDB;
