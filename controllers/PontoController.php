<?php
require_once MODELS_PATH . '/Ponto.php';
require_once MODELS_PATH . '/User.php';

class PontoController extends Controller
{
    /**
     * Admin: Lista registros de ponto dos colaboradores CLT
     */
    public function index(): void
    {
        Auth::requireRole('admin');

        $model = new Ponto();

        // Filtros
        $dataInicio = $_GET['inicio'] ?? date('Y-m-01');
        $dataFim    = $_GET['fim']    ?? date('Y-m-d');
        $userId     = !empty($_GET['promotor']) ? (int) $_GET['promotor'] : null;

        $registros   = $model->porPeriodo($dataInicio, $dataFim, $userId);
        $promotores  = $model->promotoresClt();

        $this->view('admin.ponto', [
            'pageTitle'  => 'Controle de Ponto',
            'pageIcon'   => 'fa-solid fa-clock',
            'pageSubtitle' => 'Acompanhe os horários dos colaboradores CLT',
            'registros'  => $registros,
            'promotores' => $promotores,
            'filtro'     => [
                'inicio'   => $dataInicio,
                'fim'      => $dataFim,
                'promotor' => $userId,
            ],
        ]);
    }

    /**
     * Promotor: Meu ponto de hoje
     */
    public function meuPonto(): void
    {
        Auth::requireRole('promotor');

        // Verificar se é CLT
        $userModel = new User();
        $user = $userModel->findById(Auth::id());

        if (($user['tipo_contrato'] ?? 'pj') !== 'clt') {
            $this->redirect('/promotor/dashboard');
            return;
        }

        $model = new Ponto();
        $hoje = $model->hoje(Auth::id());

        $this->view('promotor.ponto', [
            'pageTitle' => 'Meu Ponto',
            'ponto'     => $hoje,
        ]);
    }

    /**
     * Promotor: Registrar evento de ponto
     */
    public function registrar(): void
    {
        Auth::requireRole('promotor');
        csrf_validate();

        $tipo = $_POST['tipo'] ?? '';
        $model = new Ponto();

        $ok = $model->registrar(Auth::id(), $tipo);

        $labels = [
            'entrada'        => 'Entrada',
            'almoco_saida'   => 'Saída para almoço',
            'almoco_retorno' => 'Retorno do almoço',
            'saida'          => 'Saída',
        ];

        if ($ok) {
            $_SESSION['flash'] = ['msg' => ($labels[$tipo] ?? 'Ponto') . ' registrado com sucesso!', 'type' => 'success'];
        } else {
            $_SESSION['flash'] = ['msg' => 'Não foi possível registrar. Este ponto já foi marcado.', 'type' => 'danger'];
        }

        $this->redirect('/promotor/ponto');
    }

    /**
     * Admin: Ajustar ponto de um colaborador (somente dias anteriores)
     */
    public function ajustar(): void
    {
        Auth::requireRole('admin');
        csrf_validate();

        $pontoId = (int) ($_POST['ponto_id'] ?? 0);
        $campos = [
            'entrada'        => $_POST['entrada'] ?? '',
            'almoco_saida'   => $_POST['almoco_saida'] ?? '',
            'almoco_retorno' => $_POST['almoco_retorno'] ?? '',
            'saida'          => $_POST['saida'] ?? '',
        ];

        $model = new Ponto();
        $result = $model->ajustar($pontoId, $campos, Auth::id());

        if ($result === true) {
            $_SESSION['flash'] = ['msg' => 'Ponto ajustado com sucesso!', 'type' => 'success'];
        } else {
            $_SESSION['flash'] = ['msg' => $result, 'type' => 'danger'];
        }

        $this->redirect('/admin/ponto');
    }
}
