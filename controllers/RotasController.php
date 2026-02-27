<?php
require_once MODELS_PATH . '/Rota.php';
require_once MODELS_PATH . '/User.php';
require_once MODELS_PATH . '/Pdv.php';
require_once MODELS_PATH . '/Pesquisa.php';
require_once MODELS_PATH . '/Visita.php';

class RotasController extends Controller
{
    /**
     * Página principal — lista promotores, exibe grade semanal
     */
    public function index(): void
    {
        Auth::requireRole('admin');

        $userModel = new User();
        $promotores = $userModel->all('promotor');

        $promotorId = (int) ($_GET['promotor'] ?? 0);
        $semana = [];
        $promotorSelecionado = null;
        $todosPdvs = [];
        $todasPesquisas = [];
        $pesquisasPorDia = [];

        if ($promotorId) {
            $rotaModel = new Rota();
            $rotaModel->garantirSemanaCompleta($promotorId, Auth::id());

            $dataRef = $_GET['data'] ?? date('Y-m-d');
            $segunda = $this->segundaDaSemana($dataRef);
            $semana = $rotaModel->semanaEfetiva($promotorId, $segunda);

            $userModel2 = new User();
            $promotorSelecionado = $userModel2->findById($promotorId);

            // Todos os PDVs para o split-screen
            $pdvModel = new Pdv();
            $todosPdvs = $pdvModel->all();

            // Todas as pesquisas disponíveis
            $pesquisaModel = new Pesquisa();
            $todasPesquisas = $pesquisaModel->all();

            // Pesquisas vinculadas por dia
            $pesquisasPorDia = $rotaModel->pesquisasDaSemana($promotorId);
        }

        $this->view('admin.rotas', [
            'pageTitle'            => 'Rotas da Semana',
            'pageIcon'             => 'fa-solid fa-route',
            'pageSubtitle'         => 'Gerencie as rotas fixas e temporárias de cada promotor',
            'promotores'           => $promotores,
            'promotorSelecionado'  => $promotorSelecionado,
            'promotorId'           => $promotorId,
            'semana'               => $semana,
            'segundaAtual'         => $segunda ?? $this->segundaDaSemana(date('Y-m-d')),
            'todosPdvs'            => $todosPdvs,
            'todasPesquisas'       => $todasPesquisas,
            'pesquisasPorDia'      => $pesquisasPorDia,
        ]);
    }

    /**
     * API — Retorna semana em JSON (AJAX)
     */
    public function apiSemana(): void
    {
        Auth::requireRole('admin');

        $promotorId = (int) ($_GET['promotor'] ?? 0);
        if (!$promotorId) {
            $this->json(['error' => 'Promotor não informado'], 400);
            return;
        }

        $rotaModel = new Rota();
        $rotaModel->garantirSemanaCompleta($promotorId, Auth::id());

        $dataRef = $_GET['data'] ?? date('Y-m-d');
        $segunda = $this->segundaDaSemana($dataRef);

        $this->json([
            'semana'  => $rotaModel->semanaEfetiva($promotorId, $segunda),
            'segunda' => $segunda,
        ]);
    }

    /**
     * POST — Sincronizar PDVs de um dia (batch — split screen)
     */
    public function sincronizarPdvs(): void
    {
        Auth::requireRole('admin');

        $rotaId = (int) $this->input('rota_id');
        $pdvIds = $_POST['pdv_ids'] ?? [];

        if (is_string($pdvIds)) {
            $pdvIds = json_decode($pdvIds, true) ?: [];
        }

        $rotaModel = new Rota();
        $rotaModel->sincronizarPdvsFixos($rotaId, $pdvIds);

        $this->json(['success' => true]);
    }

    /**
     * POST — Sincronizar pesquisas de um dia
     */
    public function sincronizarPesquisas(): void
    {
        Auth::requireRole('admin');

        $rotaId = (int) $this->input('rota_id');
        $pesquisaIds = $_POST['pesquisa_ids'] ?? [];

        if (is_string($pesquisaIds)) {
            $pesquisaIds = json_decode($pesquisaIds, true) ?: [];
        }

        $rotaModel = new Rota();
        $rotaModel->sincronizarPesquisas($rotaId, $pesquisaIds);

        $this->json(['success' => true]);
    }

    /**
     * POST — Adicionar PDV (fixo ou temporário) — mantém compat
     */
    public function adicionarPdv(): void
    {
        Auth::requireRole('admin');

        $rotaId     = (int) $this->input('rota_id');
        $pdvId      = (int) $this->input('pdv_id');
        $tipo       = $this->input('tipo');
        $data       = $this->input('data');

        $rotaModel = new Rota();

        if ($tipo === 'fixo') {
            $rotaModel->adicionarPdvFixo($rotaId, $pdvId);
        } else {
            $rotaModel->adicionarExcecao($rotaId, $data, 'adicionar_pdv', $pdvId, Auth::id());
        }

        $this->json(['success' => true]);
    }

    /**
     * POST — Remover PDV (fixo ou temporário)
     */
    public function removerPdv(): void
    {
        Auth::requireRole('admin');

        $rotaId     = (int) $this->input('rota_id');
        $pdvId      = (int) $this->input('pdv_id');
        $tipo       = $this->input('tipo');
        $data       = $this->input('data');
        $excecaoId  = (int) $this->input('excecao_id');

        $rotaModel = new Rota();

        if ($tipo === 'fixo') {
            $rotaModel->removerPdvFixo($rotaId, $pdvId);
        } elseif ($excecaoId) {
            $rotaModel->removerExcecao($excecaoId);
        } else {
            $rotaModel->adicionarExcecao($rotaId, $data, 'remover_pdv', $pdvId, Auth::id());
        }

        $this->json(['success' => true]);
    }

    /**
     * POST — Reordenar PDVs (drag and drop)
     */
    public function reordenar(): void
    {
        Auth::requireRole('admin');

        $rotaId = (int) $this->input('rota_id');
        $pdvIds = $_POST['pdv_ids'] ?? [];

        if (!is_array($pdvIds)) {
            $pdvIds = json_decode($pdvIds, true) ?: [];
        }

        $rotaModel = new Rota();
        $rotaModel->reordenarPdvs($rotaId, $pdvIds);

        $this->json(['success' => true]);
    }

    /**
     * POST — Otimizar rota por proximidade geográfica
     */
    public function otimizar(): void
    {
        Auth::requireRole('admin');

        $rotaId = (int) $this->input('rota_id');

        $rotaModel = new Rota();
        $rotaModel->otimizarOrdem($rotaId);

        $this->json(['success' => true]);
    }

    /**
     * Minhas Rotas — visão do promotor
     */
    public function minhasRotas(): void
    {
        Auth::requireRole('promotor');

        $rotaModel = new Rota();
        $visitaModel = new Visita();
        $dataRef = $_GET['data'] ?? date('Y-m-d');
        $segunda = $this->segundaDaSemana($dataRef);

        // Busca visitas de hoje para cruzar com PDVs na view
        $visitasHoje = $visitaModel->hoje(Auth::id());
        $visitasPorPdv = [];
        foreach ($visitasHoje as $v) {
            $visitasPorPdv[$v['pdv_id']] = $v;
        }

        $this->view('promotor.minhas-rotas', [
            'pageTitle'      => 'Minhas Rotas',
            'semana'         => $rotaModel->semanaEfetiva(Auth::id(), $segunda),
            'segunda'        => $segunda,
            'visitasPorPdv'  => $visitasPorPdv,
        ]);
    }

    private function segundaDaSemana(string $data): string
    {
        $ts = strtotime($data);
        $dow = date('N', $ts);
        $diff = $dow - 1;
        return date('Y-m-d', strtotime("-{$diff} days", $ts));
    }
}
