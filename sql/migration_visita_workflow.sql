-- =============================================
-- TradeForce v2 — Migration: Workflow de Visita
-- Adiciona campos para fotos de trabalho, observações e foto de checkout
-- =============================================

USE tradeforce;

ALTER TABLE visitas
    ADD COLUMN observacao TEXT AFTER justificativa,
    ADD COLUMN fotos_trabalho JSON AFTER observacao,
    ADD COLUMN foto_checkout VARCHAR(255) AFTER fotos_trabalho;
