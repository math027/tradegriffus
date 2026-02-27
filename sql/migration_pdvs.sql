-- =============================================
-- TradeForce v2 — Migration: Novos campos PDVs
-- =============================================

USE tradeforce;

ALTER TABLE pdvs
    ADD COLUMN codigo  VARCHAR(50) AFTER id,
    ADD COLUMN cnpj    VARCHAR(20) AFTER nome,
    ADD COLUMN bairro  VARCHAR(100) AFTER endereco,
    ADD COLUMN rua     VARCHAR(200) AFTER bairro,
    ADD COLUMN numero  VARCHAR(20) AFTER rua;

-- O campo 'endereco' existente fica como campo livre / complemento
-- Os novos campos 'rua', 'numero', 'bairro' detalham o endereço

-- Index no código para busca rápida
ALTER TABLE pdvs ADD INDEX idx_codigo (codigo);
