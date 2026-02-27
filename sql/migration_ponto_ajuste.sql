-- =============================================
-- Migration: Campos de auditoria para ajuste de ponto
-- =============================================

USE griffu80_trade;

ALTER TABLE ponto
    ADD COLUMN ajustado_por INT DEFAULT NULL AFTER saida,
    ADD COLUMN ajustado_em  DATETIME DEFAULT NULL AFTER ajustado_por;
