<?php
require_once MODELS_PATH . '/Visita.php';
require_once MODELS_PATH . '/Rota.php';
require_once MODELS_PATH . '/Resposta.php';
require_once MODELS_PATH . '/Pdv.php';
require_once MODELS_PATH . '/User.php';

class DashboardController extends Controller
{
    /**
     * Dashboard do Admin/Gestor
     */
    public function admin(): void
    {
        Auth::requireRole('admin');

        $visitaModel   = new Visita();
        $respostaModel = new Resposta();
        $pdvModel      = new Pdv();
        $userModel     = new User();

        $data = [
            'pageTitle'           => 'Visão Geral',
        'pageIcon'            => 'fa-solid fa-chart-pie',
            'visitasHoje'         => $visitaModel->countHoje(),
            'pesquisasRealizadas' => $respostaModel->countTotal(),
            'totalPdvs'           => $pdvModel->count(),
            'totalPromotores'     => $userModel->countByRole('promotor'),
            'ultimosCheckins'     => $visitaModel->ultimosCheckins(5),
            'visitasRecentes'     => $visitaModel->hoje(),
        ];

        $this->view('admin.dashboard', $data);
    }

    /**
     * Dashboard do Promotor (mobile-first) — com mapa e visitas automáticas
     */
    public function promotor(): void
    {
        Auth::requireRole('promotor');

        $userId = Auth::id();
        $visitaModel = new Visita();
        $rotaModel   = new Rota();

        // Se tem visita em andamento, redireciona para workflow
        $emAndamento = $visitaModel->emAndamento($userId);
        if ($emAndamento) {
            header('Location: /promotor/visita/' . $emAndamento['id']);
            exit;
        }

        // Semana atual do promotor
        $hoje = date('Y-m-d');
        $dow  = (int) date('N'); // 1=seg
        $segunda = date('Y-m-d', strtotime("-" . ($dow - 1) . " days"));

        $semana = $rotaModel->semanaEfetiva($userId, $segunda);

        // Criar visitas automaticamente para hoje (se dia útil e não existem ainda)
        $rotaHoje = null;
        foreach ($semana as $dia) {
            if ($dia['data'] === $hoje && !empty($dia['pdvs']) && $dia['rota_id']) {
                $rotaHoje = $dia;
                break;
            }
        }

        $visitasHoje = [];
        if ($rotaHoje) {
            foreach ($rotaHoje['pdvs'] as $pdv) {
                $visita = $visitaModel->criarOuRetornar(
                    $rotaHoje['rota_id'],
                    $pdv['id'],
                    $userId,
                    $hoje
                );
                $visitasHoje[] = $visita;
            }
        } else {
            $visitasHoje = $visitaModel->hoje($userId);
        }

        $this->view('promotor.dashboard', [
            'pageTitle'    => 'Meu Painel',
            'visitasHoje'  => $visitasHoje,
            'semanaAtual'  => $semana,
        ]);
    }

    /**
     * Mapa — todos os PDVs do promotor
     */
    public function mapaPromotor(): void
    {
        Auth::requireRole('promotor');

        $userId = Auth::id();
        $rotaModel = new Rota();
        $visitaModel = new Visita();
        $pdvModel = new Pdv();

        // Busca PDVs de todas as rotas do promotor
        $hoje = date('Y-m-d');
        $dow  = (int) date('N');
        $segunda = date('Y-m-d', strtotime("-" . ($dow - 1) . " days"));
        $semana = $rotaModel->semanaEfetiva($userId, $segunda);

        // Coleta todos os PDVs únicos da semana
        $pdvIds = [];
        foreach ($semana as $dia) {
            foreach ($dia['pdvs'] as $pdv) {
                $pdvIds[$pdv['id']] = true;
            }
        }

        // Busca dados completos dos PDVs (com lat/lng)
        $pdvs = [];
        foreach (array_keys($pdvIds) as $id) {
            $pdv = $pdvModel->findById($id);
            if ($pdv) $pdvs[] = $pdv;
        }

        // Visitas de hoje para mostrar status
        $visitasHoje = $visitaModel->hoje($userId);

        $this->view('promotor.mapa', [
            'pageTitle'    => 'Mapa',
            'pdvs'         => $pdvs,
            'visitasHoje'  => $visitasHoje,
        ]);
    }

    /**
     * API — Stats para gráficos (AJAX)
     */
    public function apiStats(): void
    {
        Auth::require();

        $db = Database::getInstance();

        // Visitas por dia (últimos 7 dias)
        $stmt = $db->query(
            "SELECT DATE(data_prevista) AS dia, COUNT(*) AS total
             FROM visitas
             WHERE data_prevista >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
             GROUP BY DATE(data_prevista)
             ORDER BY dia ASC"
        );

        $this->json([
            'visitasPorDia' => $stmt->fetchAll(),
        ]);
    }
}
