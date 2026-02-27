-- =============================================
-- TradeForce v2 — Migration: Rotas Fixas Semanais
-- =============================================
USE griffu80_trade;

-- Remove tabelas antigas (ordem por FK)
DROP TABLE IF EXISTS respostas;
DROP TABLE IF EXISTS visitas;
DROP TABLE IF EXISTS roteiros;

-- -----------------------------------------------
-- Rotas fixas (template semanal por promotor)
-- 1 registro = 1 dia da semana de 1 promotor
-- -----------------------------------------------
CREATE TABLE rotas (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    promotor_id INT NOT NULL,
    dia_semana  TINYINT NOT NULL COMMENT '1=seg, 2=ter, 3=qua, 4=qui, 5=sex, 6=sab, 7=dom',
    ativo       TINYINT(1) DEFAULT 1,
    created_by  INT NOT NULL,
    created_at  DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at  DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    UNIQUE KEY uk_promotor_dia (promotor_id, dia_semana),
    INDEX idx_promotor (promotor_id),
    FOREIGN KEY (promotor_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by)  REFERENCES users(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

-- -----------------------------------------------
-- PDVs fixos de cada rota (template)
-- -----------------------------------------------
CREATE TABLE rota_pdvs (
    id       INT AUTO_INCREMENT PRIMARY KEY,
    rota_id  INT NOT NULL,
    pdv_id   INT NOT NULL,
    ordem    INT DEFAULT 0,

    UNIQUE KEY uk_rota_pdv (rota_id, pdv_id),
    FOREIGN KEY (rota_id) REFERENCES rotas(id) ON DELETE CASCADE,
    FOREIGN KEY (pdv_id)  REFERENCES pdvs(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- -----------------------------------------------
-- Exceções temporárias (adições/remoções em data específica)
-- -----------------------------------------------
CREATE TABLE rota_excecoes (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    rota_id     INT NOT NULL,
    data        DATE NOT NULL,
    tipo        ENUM('adicionar_pdv','remover_pdv') NOT NULL,
    pdv_id      INT NOT NULL,
    ordem       INT DEFAULT 99,
    created_by  INT NOT NULL,
    created_at  DATETIME DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_rota_data (rota_id, data),
    FOREIGN KEY (rota_id)    REFERENCES rotas(id) ON DELETE CASCADE,
    FOREIGN KEY (pdv_id)     REFERENCES pdvs(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

-- -----------------------------------------------
-- Visitas (check-in/check-out reais) — reformulada
-- Inclui colunas de workflow (observacao, fotos_trabalho, foto_checkout)
-- -----------------------------------------------
CREATE TABLE visitas (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    rota_id         INT NOT NULL,
    pdv_id          INT NOT NULL,
    promotor_id     INT NOT NULL,
    data_prevista   DATE NOT NULL,
    checkin_at      DATETIME,
    checkout_at     DATETIME,
    latitude_in     DECIMAL(10,8),
    longitude_in    DECIMAL(11,8),
    latitude_out    DECIMAL(10,8),
    longitude_out   DECIMAL(11,8),
    foto_checkin    VARCHAR(255),
    status          ENUM('pendente','em_andamento','concluida','justificada') DEFAULT 'pendente',
    justificativa   TEXT,
    observacao      TEXT,
    fotos_trabalho  JSON,
    foto_checkout   VARCHAR(255),
    created_at      DATETIME DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_rota (rota_id),
    INDEX idx_promotor (promotor_id),
    INDEX idx_pdv (pdv_id),
    INDEX idx_data (data_prevista),
    FOREIGN KEY (rota_id)     REFERENCES rotas(id) ON DELETE CASCADE,
    FOREIGN KEY (pdv_id)      REFERENCES pdvs(id) ON DELETE RESTRICT,
    FOREIGN KEY (promotor_id) REFERENCES users(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

-- -----------------------------------------------
-- Recria respostas (FK visitas foi dropada)
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
-- Seed: Rotas fixas de exemplo para Maria (promotor id=2)
-- Seg a Sex, cada dia com alguns PDVs
-- -----------------------------------------------
-- (Será populado pelo gestor via interface)
