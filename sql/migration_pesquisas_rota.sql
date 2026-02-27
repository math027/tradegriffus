-- =============================================
-- TradeForce v2 — Migration: Pesquisas por Rota
-- =============================================

USE tradeforce;

CREATE TABLE IF NOT EXISTS rota_pesquisas (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    rota_id     INT NOT NULL,
    pesquisa_id INT NOT NULL,

    UNIQUE KEY uk_rota_pesquisa (rota_id, pesquisa_id),
    FOREIGN KEY (rota_id)     REFERENCES rotas(id) ON DELETE CASCADE,
    FOREIGN KEY (pesquisa_id) REFERENCES pesquisas(id) ON DELETE CASCADE
) ENGINE=InnoDB;
