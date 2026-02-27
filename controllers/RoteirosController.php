<?php
require_once MODELS_PATH . '/Roteiro.php';
require_once MODELS_PATH . '/Visita.php';
require_once MODELS_PATH . '/User.php';
require_once MODELS_PATH . '/Pdv.php';

class RoteirosController extends Controller
{
    public function index(): void
    {
        Auth::requireRole('admin');

        $model = new Roteiro();
        $roteiros = $model->all($_GET);

        $this->view('admin.roteiros', [
            'pageTitle' => 'Roteiros e Visitas',
            'roteiros'  => $roteiros,
        ]);
    }

    public function create(): void
    {
        Auth::requireRole('admin');

        $userModel = new User();
        $pdvModel  = new Pdv();

        $this->view('admin.roteiros-form', [
            'pageTitle'   => 'Novo Roteiro',
            'promotores'  => $userModel->all('promotor'),
            'pdvs'        => $pdvModel->all(),
            'roteiro'     => null,
        ]);
    }

    public function store(): void
    {
        Auth::requireRole('admin');

        $model = new Roteiro();
        $id = $model->create([
            'titulo'      => $this->input('titulo'),
            'promotor_id' => (int) $_POST['promotor_id'],
            'data_inicio' => $_POST['data_inicio'],
            'data_fim'    => $_POST['data_fim'],
            'observacoes' => $this->input('observacoes'),
            'created_by'  => Auth::id(),
        ]);

        // Cria as visitas vinculadas
        if (!empty($_POST['pdvs']) && is_array($_POST['pdvs'])) {
            $visitaModel = new Visita();
            foreach ($_POST['pdvs'] as $pdvId) {
                $visitaModel->create([
                    'roteiro_id'   => $id,
                    'pdv_id'       => (int) $pdvId,
                    'promotor_id'  => (int) $_POST['promotor_id'],
                    'data_prevista' => $_POST['data_inicio'],
                ]);
            }
        }

        $this->redirect('/admin/roteiros');
    }

    public function show(string $id): void
    {
        Auth::requireRole('admin');

        $model = new Roteiro();
        $roteiro = $model->findById((int) $id);

        if (!$roteiro) {
            $this->redirect('/admin/roteiros');
            return;
        }

        $visitaModel = new Visita();

        $this->view('admin.roteiro-detalhe', [
            'pageTitle' => $roteiro['titulo'],
            'roteiro'   => $roteiro,
            'visitas'   => $visitaModel->porRoteiro((int) $id),
        ]);
    }

    public function edit(string $id): void
    {
        Auth::requireRole('admin');

        $model = new Roteiro();
        $roteiro = $model->findById((int) $id);

        if (!$roteiro) {
            $this->redirect('/admin/roteiros');
            return;
        }

        $userModel = new User();
        $pdvModel  = new Pdv();

        $this->view('admin.roteiros-form', [
            'pageTitle'   => 'Editar Roteiro',
            'roteiro'     => $roteiro,
            'promotores'  => $userModel->all('promotor'),
            'pdvs'        => $pdvModel->all(),
        ]);
    }

    public function update(string $id): void
    {
        Auth::requireRole('admin');

        $model = new Roteiro();
        $model->update((int) $id, [
            'titulo'      => $this->input('titulo'),
            'promotor_id' => (int) $_POST['promotor_id'],
            'data_inicio' => $_POST['data_inicio'],
            'data_fim'    => $_POST['data_fim'],
            'status'      => $_POST['status'] ?? 'pendente',
            'observacoes' => $this->input('observacoes'),
        ]);

        $this->redirect('/admin/roteiros');
    }

    public function destroy(string $id): void
    {
        Auth::requireRole('admin');

        $model = new Roteiro();
        $model->delete((int) $id);
        $this->redirect('/admin/roteiros');
    }

    /**
     * Minhas Rotas — visão do promotor
     */
    public function minhasRotas(): void
    {
        Auth::requireRole('promotor');

        $model = new Roteiro();

        $this->view('promotor.minhas-rotas', [
            'pageTitle' => 'Minhas Rotas',
            'roteiros'  => $model->doPromotor(Auth::id()),
        ]);
    }
}
