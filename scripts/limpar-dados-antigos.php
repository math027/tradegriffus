<?php
/**
 * TradeGriffus v2 — Script de limpeza automática
 * Remove fotos e respostas de pesquisas com mais de 45 dias.
 * 
 * Uso: php scripts/limpar-dados-antigos.php
 * Agende via cron/Agendador de Tarefas do Windows a cada 24h.
 */

require_once __DIR__ . '/../config/app.php';
require_once CORE_PATH . '/Database.php';
require_once MODELS_PATH . '/Pdv.php';

echo "=== TradeGriffus — Limpeza de dados antigos ===\n";
echo "Data: " . date('Y-m-d H:i:s') . "\n\n";

$model = new Pdv();
$resultado = $model->limparDadosAntigos(45);

echo "Visitas com fotos limpas: {$resultado['fotos_removidas']}\n";
echo "Arquivos deletados do disco: {$resultado['arquivos_deletados']}\n";
echo "Respostas de pesquisas removidas: {$resultado['respostas_removidas']}\n";
echo "\n✓ Limpeza concluída.\n";
