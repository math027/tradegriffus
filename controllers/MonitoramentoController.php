<?php
require_once MODELS_PATH . '/User.php';
require_once MODELS_PATH . '/Rota.php';
require_once MODELS_PATH . '/Visita.php';
require_once MODELS_PATH . '/Pdv.php';

class MonitoramentoController extends Controller
{
    /**
     * Tela de monitoramento — acompanhamento em tempo real dos promotores
     */
    public function index(): void
    {
        Auth::requireRole('admin');

        $db = Database::getInstance();
        $userModel = new User();
        $rotaModel = new Rota();
        $visitaModel = new Visita();

        $promotores = $userModel->all('promotor');
        $hoje = date('Y-m-d');
        $diaSemana = date('N'); // 1=seg ... 7=dom

        $monitores = [];
        $totais = ['total_pdvs' => 0, 'concluidas' => 0, 'em_andamento' => 0, 'pendentes' => 0];

        foreach ($promotores as $promotor) {
            // Rota do dia
            $rota = $rotaModel->porPromotorDia($promotor['id'], (int) $diaSemana);
            $pdvsDoDia = [];
            $rotaId = null;

            if ($rota) {
                $rotaId = $rota['id'];

                // PDVs fixos
                $fixos = $rotaModel->pdvsFixos($rotaId);
                foreach ($fixos as $pdv) {
                    $pdv['origem'] = 'fixo';
                    $pdvsDoDia[] = $pdv;
                }

                // Exceções do dia
                $excecoes = $rotaModel->excecoesDoDia($rotaId, $hoje);
                foreach ($excecoes as $exc) {
                    if ($exc['tipo'] === 'adicionar_pdv') {
                        $pdvsDoDia[] = [
                            'id'       => $exc['pdv_id'],
                            'nome'     => $exc['pdv_nome'],
                            'endereco' => $exc['endereco'],
                            'origem'   => 'temporario',
                        ];
                    } elseif ($exc['tipo'] === 'remover_pdv') {
                        $pdvsDoDia = array_filter($pdvsDoDia, fn($p) => $p['id'] != $exc['pdv_id']);
                        $pdvsDoDia = array_values($pdvsDoDia);
                    }
                }
            }

            // Somente promotores com rota no dia
            if (empty($pdvsDoDia)) continue;

            // Visitas de hoje para este promotor
            $visitasHoje = $visitaModel->hoje($promotor['id']);
            $visitasPorPdv = [];
            foreach ($visitasHoje as $v) {
                $visitasPorPdv[$v['pdv_id']] = $v;
            }

            // Status de cada PDV
            $pdvsComStatus = [];
            $statusCounts = ['concluida' => 0, 'em_andamento' => 0, 'pendente' => 0];
            $destinoAtual = null;

            foreach ($pdvsDoDia as $pdv) {
                $visita = $visitasPorPdv[$pdv['id']] ?? null;
                $status = 'pendente';

                if ($visita) {
                    $status = $visita['status'];
                    if ($status === 'em_andamento') {
                        $destinoAtual = $pdv;
                    }
                }

                $pdvsComStatus[] = [
                    'id'         => $pdv['id'],
                    'nome'       => $pdv['nome'],
                    'status'     => $status,
                    'observacao' => $visita['observacao'] ?? null,
                    'visita_id'  => $visita['id'] ?? null,
                ];
                $statusCounts[$status]++;
            }

            // Se não tem 'em_andamento', usar o próximo pendente como destino
            if (!$destinoAtual) {
                foreach ($pdvsComStatus as $p) {
                    if ($p['status'] === 'pendente') {
                        $destinoAtual = $p;
                        break;
                    }
                }
            }

            // Última conexão (último checkin)
            $ultimaConexao = null;
            foreach ($visitasHoje as $v) {
                if ($v['checkin_at']) {
                    if (!$ultimaConexao || $v['checkin_at'] > $ultimaConexao) {
                        $ultimaConexao = $v['checkin_at'];
                    }
                }
                if ($v['checkout_at'] && $v['checkout_at'] > ($ultimaConexao ?? '')) {
                    $ultimaConexao = $v['checkout_at'];
                }
            }

            // Pesquisas respondidas hoje
            $stmtResp = $db->prepare(
                "SELECT r.dados, r.created_at, p.titulo AS pesquisa_titulo, p.campos AS pesquisa_campos,
                        pdv.nome AS pdv_nome
                 FROM respostas r
                 JOIN pesquisas p ON r.pesquisa_id = p.id
                 JOIN pdvs pdv ON r.pdv_id = pdv.id
                 WHERE r.promotor_id = :pid AND DATE(r.created_at) = :hoje
                 ORDER BY r.created_at DESC"
            );
            $stmtResp->execute(['pid' => $promotor['id'], 'hoje' => $hoje]);
            $respostasHoje = $stmtResp->fetchAll();

            $totalPdvs = count($pdvsComStatus);
            $progresso = $totalPdvs > 0 ? round(($statusCounts['concluida'] / $totalPdvs) * 100) : 0;

            $monitores[] = [
                'promotor'        => $promotor,
                'ultima_conexao'  => $ultimaConexao,
                'destino'         => $destinoAtual,
                'pdvs'            => $pdvsComStatus,
                'status_counts'   => $statusCounts,
                'total_pdvs'      => $totalPdvs,
                'progresso'       => $progresso,
                'respostas'       => $respostasHoje,
            ];

            $totais['total_pdvs'] += $totalPdvs;
            $totais['concluidas'] += $statusCounts['concluida'];
            $totais['em_andamento'] += $statusCounts['em_andamento'];
            $totais['pendentes'] += $statusCounts['pendente'];
        }

        $totais['progresso'] = $totais['total_pdvs'] > 0
            ? round(($totais['concluidas'] / $totais['total_pdvs']) * 100)
            : 0;

        $this->view('admin.monitoramento', [
            'pageTitle'  => 'Monitoramento',
            'pageIcon'   => 'fa-solid fa-satellite-dish',
            'pageSubtitle' => 'Acompanhe o progresso dos promotores em tempo real',
            'monitores'  => $monitores,
            'totais'     => $totais,
        ]);
    }

    /**
     * API: retorna fotos do dia para um PDV específico (JSON)
     */
    public function apiFotosDia(string $id): void
    {
        Auth::requireRole('admin');

        $model = new Pdv();
        $pdv = $model->findById((int) $id);
        $hoje = date('Y-m-d');
        $visitas = $model->fotosDoDia((int) $id, $hoje);

        $fotos = [];
        foreach ($visitas as $v) {
            if ($v['foto_checkin']) {
                $fotos[] = ['path' => $v['foto_checkin'], 'tipo' => 'Fachada (Check-in)', 'promotor' => $v['promotor_nome'], 'hora' => date('H:i', strtotime($v['checkin_at']))];
            }
            $trabalho = json_decode($v['fotos_trabalho'] ?? '[]', true) ?: [];
            foreach ($trabalho as $f) {
                $fotos[] = ['path' => $f, 'tipo' => 'Foto de trabalho', 'promotor' => $v['promotor_nome'], 'hora' => date('H:i', strtotime($v['checkin_at']))];
            }
            if ($v['foto_checkout']) {
                $fotos[] = ['path' => $v['foto_checkout'], 'tipo' => 'Check-out', 'promotor' => $v['promotor_nome'], 'hora' => date('H:i', strtotime($v['checkout_at'] ?? $v['checkin_at']))];
            }
        }

        header('Content-Type: application/json');
        echo json_encode([
            'pdv_nome' => $pdv['nome'] ?? 'PDV',
            'fotos' => $fotos,
        ]);
        exit;
    }
}
