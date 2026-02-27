<?php
require_once MODELS_PATH . '/Rota.php';

class RelatoriosController extends Controller
{
    /**
     * Helper: executa query com parâmetros usando prepare/execute
     */
    private function dbQuery(string $sql, array $params = []): \PDOStatement
    {
        $db = Database::getInstance();
        if (empty($params)) {
            return $db->query($sql);
        }
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    public function index(): void
    {
        Auth::requireRole('admin');

        $mesAtual = date('Y-m');
        $mesLabel = date('m/Y');

        // ========================================================
        // 1. Lojas visitadas no mês (distinct PDVs com visita concluída)
        // ========================================================
        $lojasVisitadasMes = $this->dbQuery(
            "SELECT COUNT(DISTINCT v.pdv_id) AS total
             FROM visitas v
             WHERE v.status = 'concluida'
               AND DATE_FORMAT(v.data_prevista, '%Y-%m') = :mes",
            ['mes' => $mesAtual]
        )->fetchColumn();

        $totalPdvsAtivos = $this->dbQuery(
            "SELECT COUNT(*) FROM pdvs WHERE ativo = 1"
        )->fetchColumn();

        // ========================================================
        // 2. Visitas por promotor (ranking: mais e menos)
        // ========================================================
        $visitasPorPromotor = $this->dbQuery(
            "SELECT u.id, u.nome, u.avatar,
                    COUNT(v.id) AS total_visitas,
                    SUM(CASE WHEN v.status = 'concluida' THEN 1 ELSE 0 END) AS concluidas,
                    SUM(CASE WHEN v.status = 'pendente' THEN 1 ELSE 0 END) AS pendentes
             FROM users u
             LEFT JOIN visitas v ON v.promotor_id = u.id 
                AND DATE_FORMAT(v.data_prevista, '%Y-%m') = :mes
             WHERE u.role = 'promotor' AND u.ativo = 1
             GROUP BY u.id, u.nome, u.avatar
             ORDER BY concluidas DESC",
            ['mes' => $mesAtual]
        )->fetchAll();

        // ========================================================
        // 3. Visitas por status (mês atual)
        // ========================================================
        $visitasPorStatus = $this->dbQuery(
            "SELECT status, COUNT(*) AS total
             FROM visitas
             WHERE DATE_FORMAT(data_prevista, '%Y-%m') = :mes
             GROUP BY status",
            ['mes' => $mesAtual]
        )->fetchAll();

        // ========================================================
        // 4. Lojas não visitadas no mês
        // ========================================================
        $lojasNaoVisitadas = $this->dbQuery(
            "SELECT p.id, p.nome, p.codigo, p.cidade, p.uf
             FROM pdvs p
             WHERE p.ativo = 1
               AND p.id NOT IN (
                   SELECT DISTINCT v.pdv_id FROM visitas v
                   WHERE v.status = 'concluida'
                     AND DATE_FORMAT(v.data_prevista, '%Y-%m') = :mes
               )
             ORDER BY p.nome",
            ['mes' => $mesAtual]
        )->fetchAll();

        // ========================================================
        // 5. Lojas fora do roteiro (não aparecem em nenhuma rota fixa)
        // ========================================================
        $lojasForaRoteiro = $this->dbQuery(
            "SELECT p.id, p.nome, p.codigo, p.cidade, p.uf
             FROM pdvs p
             WHERE p.ativo = 1
               AND p.id NOT IN (
                   SELECT DISTINCT rpf.pdv_id FROM rota_pdvs rpf
               )
             ORDER BY p.nome"
        )->fetchAll();

        // ========================================================
        // 6. PDVs mais visitados (30 dias)
        // ========================================================
        $pdvsMaisVisitados = $this->dbQuery(
            "SELECT p.nome, COUNT(v.id) AS total_visitas
             FROM pdvs p
             JOIN visitas v ON v.pdv_id = p.id
             WHERE v.status = 'concluida'
               AND v.data_prevista >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
             GROUP BY p.id, p.nome
             ORDER BY total_visitas DESC
             LIMIT 10"
        )->fetchAll();

        // ========================================================
        // 7. Pesquisas por mês (6 meses)
        // ========================================================
        $pesquisasPorMes = $this->dbQuery(
            "SELECT DATE_FORMAT(created_at, '%Y-%m') AS mes, COUNT(*) AS total
             FROM respostas
             WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
             GROUP BY mes
             ORDER BY mes ASC"
        )->fetchAll();

        $this->view('admin.relatorios', [
            'pageTitle'           => 'Relatórios',
            'pageIcon'            => 'fa-solid fa-chart-line',
            'pageSubtitle'        => 'Visão analítica da operação',
            'mesLabel'            => $mesLabel,
            'lojasVisitadasMes'   => (int) $lojasVisitadasMes,
            'totalPdvsAtivos'     => (int) $totalPdvsAtivos,
            'visitasPorPromotor'  => $visitasPorPromotor,
            'visitasPorStatus'    => $visitasPorStatus,
            'lojasNaoVisitadas'   => $lojasNaoVisitadas,
            'lojasForaRoteiro'    => $lojasForaRoteiro,
            'pdvsMaisVisitados'   => $pdvsMaisVisitados,
            'pesquisasPorMes'     => $pesquisasPorMes,
        ]);
    }

    /**
     * Exportação CSV dos relatórios
     */
    public function exportar(): void
    {
        Auth::requireRole('admin');

        $tipo = $_GET['tipo'] ?? '';
        $mesAtual = date('Y-m');

        $filename = 'relatorio_' . $tipo . '_' . date('Y-m-d') . '.csv';

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $output = fopen('php://output', 'w');
        // BOM for Excel UTF-8
        fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

        switch ($tipo) {
            case 'lojas-visitadas':
                fputcsv($output, ['Promotor', 'Total Visitas', 'Concluídas', 'Pendentes', 'Taxa (%)'], ';');
                $rows = $this->dbQuery(
                    "SELECT u.nome,
                            COUNT(v.id) AS total_visitas,
                            SUM(CASE WHEN v.status = 'concluida' THEN 1 ELSE 0 END) AS concluidas,
                            SUM(CASE WHEN v.status = 'pendente' THEN 1 ELSE 0 END) AS pendentes
                     FROM users u
                     LEFT JOIN visitas v ON v.promotor_id = u.id 
                        AND DATE_FORMAT(v.data_prevista, '%Y-%m') = :mes
                     WHERE u.role = 'promotor' AND u.ativo = 1
                     GROUP BY u.id, u.nome
                     ORDER BY concluidas DESC",
                    ['mes' => $mesAtual]
                )->fetchAll();

                foreach ($rows as $r) {
                    $taxa = $r['total_visitas'] > 0 ? round(($r['concluidas'] / $r['total_visitas']) * 100) : 0;
                    fputcsv($output, [$r['nome'], $r['total_visitas'], $r['concluidas'], $r['pendentes'], $taxa], ';');
                }
                break;

            case 'lojas-nao-visitadas':
                fputcsv($output, ['Nome', 'Código', 'Cidade', 'UF'], ';');
                $rows = $this->dbQuery(
                    "SELECT p.nome, p.codigo, p.cidade, p.uf FROM pdvs p
                     WHERE p.ativo = 1
                       AND p.id NOT IN (
                           SELECT DISTINCT v.pdv_id FROM visitas v
                           WHERE v.status = 'concluida'
                             AND DATE_FORMAT(v.data_prevista, '%Y-%m') = :mes
                       )
                     ORDER BY p.nome",
                    ['mes' => $mesAtual]
                )->fetchAll();

                foreach ($rows as $r) {
                    fputcsv($output, [$r['nome'], $r['codigo'] ?? '', $r['cidade'] ?? '', $r['uf'] ?? ''], ';');
                }
                break;

            case 'lojas-fora-roteiro':
                fputcsv($output, ['Nome', 'Código', 'Cidade', 'UF'], ';');
                $rows = $this->dbQuery(
                    "SELECT p.nome, p.codigo, p.cidade, p.uf FROM pdvs p
                     WHERE p.ativo = 1
                       AND p.id NOT IN (SELECT DISTINCT rpf.pdv_id FROM rota_pdvs rpf)
                     ORDER BY p.nome"
                )->fetchAll();

                foreach ($rows as $r) {
                    fputcsv($output, [$r['nome'], $r['codigo'] ?? '', $r['cidade'] ?? '', $r['uf'] ?? ''], ';');
                }
                break;

            default:
                fputcsv($output, ['Nenhum relatório encontrado'], ';');
                break;
        }

        fclose($output);
        exit;
    }
}
