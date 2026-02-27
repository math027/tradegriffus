-- =============================================
-- TradeGriffus v2 — Schema MySQL
-- =============================================

CREATE DATABASE IF NOT EXISTS griffu80_trade
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE griffu80_trade;

-- -----------------------------------------------
-- Usuários (admin e promotor)
-- -----------------------------------------------
CREATE TABLE users (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    nome        VARCHAR(100) NOT NULL,
    email       VARCHAR(150) NOT NULL UNIQUE,
    senha       VARCHAR(255) NOT NULL,
    role        ENUM('admin','promotor') NOT NULL DEFAULT 'promotor',
    telefone    VARCHAR(20),
    avatar      VARCHAR(255),
    ativo       TINYINT(1) DEFAULT 1,
    created_at  DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at  DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- -----------------------------------------------
-- Pontos de Venda (PDVs)
-- -----------------------------------------------
CREATE TABLE pdvs (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    nome        VARCHAR(150) NOT NULL,
    endereco    VARCHAR(255),
    cidade      VARCHAR(100),
    uf          CHAR(2),
    cep         VARCHAR(10),
    latitude    DECIMAL(10,8),
    longitude   DECIMAL(11,8),
    responsavel VARCHAR(100),
    telefone    VARCHAR(20),
    ativo       TINYINT(1) DEFAULT 1,
    created_at  DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at  DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- -----------------------------------------------
-- Roteiros
-- -----------------------------------------------
CREATE TABLE roteiros (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    titulo          VARCHAR(150) NOT NULL,
    promotor_id     INT NOT NULL,
    data_inicio     DATE NOT NULL,
    data_fim        DATE NOT NULL,
    status          ENUM('pendente','em_andamento','concluido','atrasado') DEFAULT 'pendente',
    observacoes     TEXT,
    created_by      INT NOT NULL,
    created_at      DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at      DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_promotor (promotor_id),
    INDEX idx_status (status),
    INDEX idx_datas (data_inicio, data_fim),
    FOREIGN KEY (promotor_id) REFERENCES users(id) ON DELETE RESTRICT,
    FOREIGN KEY (created_by)  REFERENCES users(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

-- -----------------------------------------------
-- Visitas (cada parada do roteiro)
-- -----------------------------------------------
CREATE TABLE visitas (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    roteiro_id      INT NOT NULL,
    pdv_id          INT NOT NULL,
    promotor_id     INT NOT NULL,
    data_prevista   DATE,
    checkin_at      DATETIME,
    checkout_at     DATETIME,
    latitude_in     DECIMAL(10,8),
    longitude_in    DECIMAL(11,8),
    latitude_out    DECIMAL(10,8),
    longitude_out   DECIMAL(11,8),
    foto_checkin    VARCHAR(255),
    status          ENUM('pendente','em_andamento','concluida','justificada') DEFAULT 'pendente',
    justificativa   TEXT,
    created_at      DATETIME DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_roteiro (roteiro_id),
    INDEX idx_promotor (promotor_id),
    INDEX idx_pdv (pdv_id),
    INDEX idx_data (data_prevista),
    FOREIGN KEY (roteiro_id)  REFERENCES roteiros(id) ON DELETE CASCADE,
    FOREIGN KEY (pdv_id)      REFERENCES pdvs(id) ON DELETE RESTRICT,
    FOREIGN KEY (promotor_id) REFERENCES users(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

-- -----------------------------------------------
-- Pesquisas (templates criados pelo admin)
-- -----------------------------------------------
CREATE TABLE pesquisas (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    titulo      VARCHAR(200) NOT NULL,
    descricao   TEXT,
    campos      JSON NOT NULL,
    ativa       TINYINT(1) DEFAULT 1,
    created_by  INT NOT NULL,
    created_at  DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at  DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

-- -----------------------------------------------
-- Respostas das pesquisas
-- -----------------------------------------------
CREATE TABLE respostas (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    pesquisa_id     INT NOT NULL,
    visita_id       INT,
    promotor_id     INT NOT NULL,
    pdv_id          INT NOT NULL,
    dados           JSON NOT NULL,
    created_at      DATETIME DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_pesquisa (pesquisa_id),
    INDEX idx_promotor (promotor_id),
    FOREIGN KEY (pesquisa_id) REFERENCES pesquisas(id) ON DELETE CASCADE,
    FOREIGN KEY (visita_id)   REFERENCES visitas(id) ON DELETE SET NULL,
    FOREIGN KEY (promotor_id) REFERENCES users(id) ON DELETE RESTRICT,
    FOREIGN KEY (pdv_id)      REFERENCES pdvs(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

-- -----------------------------------------------
-- Usuário admin padrão (senha: admin123)
-- -----------------------------------------------
INSERT INTO users (nome, email, senha, role) VALUES
('Administrador', 'admin@tradeforce.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

-- Promotor de teste (senha: promo123)
INSERT INTO users (nome, email, senha, role, telefone) VALUES
('Maria Santos', 'maria@tradeforce.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'promotor', '(11) 99999-0001');
