<?php
require_once MODELS_PATH . '/Pesquisa.php';
require_once MODELS_PATH . '/Resposta.php';
require_once MODELS_PATH . '/Pdv.php';

class PesquisasController extends Controller
{
    public function index(): void
    {
        Auth::requireRole('admin');

        $model = new Pesquisa();

        $this->view('admin.pesquisas', [
            'pageTitle'  => 'Pesquisas',
        'pageIcon'   => 'fa-solid fa-clipboard-list',
            'pesquisas'  => $model->all(false),
        ]);
    }

    public function create(): void
    {
        Auth::requireRole('admin');

        $this->view('admin.pesquisas-form', [
            'pageTitle' => 'Nova Pesquisa',
            'pesquisa'  => null,
        ]);
    }

    public function store(): void
    {
        Auth::requireRole('admin');

        $model = new Pesquisa();

        // Campos vêm como JSON do frontend
        $campos = $_POST['campos'] ?? '[]';

        $model->create([
            'titulo'     => $this->input('titulo'),
            'descricao'  => $this->input('descricao'),
            'campos'     => $campos,
            'created_by' => Auth::id(),
        ]);

        $this->redirect('/admin/pesquisas');
    }

    public function edit(string $id): void
    {
        Auth::requireRole('admin');

        $model = new Pesquisa();
        $pesquisa = $model->findById((int) $id);

        if (!$pesquisa) {
            $this->redirect('/admin/pesquisas');
            return;
        }

        $this->view('admin.pesquisas-form', [
            'pageTitle' => 'Editar Pesquisa',
            'pesquisa'  => $pesquisa,
        ]);
    }

    public function update(string $id): void
    {
        Auth::requireRole('admin');

        $model = new Pesquisa();
        $model->update((int) $id, [
            'titulo'    => $this->input('titulo'),
            'descricao' => $this->input('descricao'),
            'campos'    => $_POST['campos'] ?? '[]',
            'ativa'     => isset($_POST['ativa']) ? 1 : 0,
        ]);

        $this->redirect('/admin/pesquisas');
    }

    public function destroy(string $id): void
    {
        Auth::requireRole('admin');

        $model = new Pesquisa();
        $model->delete((int) $id);
        $this->redirect('/admin/pesquisas');
    }

    public function destroyPermanente(string $id): void
    {
        Auth::requireRole('admin');

        $model = new Pesquisa();
        $model->deletePermanente((int) $id);
        $this->redirect('/admin/pesquisas');
    }

    /**
     * Exibe respostas de uma pesquisa (admin)
     */
    public function respostas(string $id): void
    {
        Auth::requireRole('admin');

        $model = new Pesquisa();
        $pesquisa = $model->findById((int) $id);

        if (!$pesquisa) {
            $this->redirect('/admin/pesquisas');
            return;
        }

        $respostaModel = new Resposta();
        $respostas = $respostaModel->porPesquisa((int) $id);

        $this->view('admin.pesquisas-respostas', [
            'pageTitle'  => 'Respostas — ' . $pesquisa['titulo'],
            'pesquisa'   => $pesquisa,
            'respostas'  => $respostas,
        ]);
    }

    /**
     * Lista pesquisas ativas para o promotor
     */
    public function promotorIndex(): void
    {
        Auth::requireRole('promotor');

        $model = new Pesquisa();

        require_once MODELS_PATH . '/Visita.php';
        $visitaModel = new Visita();

        $this->view('promotor.pesquisas', [
            'pageTitle'      => 'Pesquisas',
            'pesquisas'      => $model->all(true),
            'visitasAtivas'  => $visitaModel->hoje(Auth::id()),
        ]);
    }

    /**
     * Formulário de resposta da pesquisa (promotor)
     */
    public function responder(string $id): void
    {
        Auth::requireRole('promotor');

        $model = new Pesquisa();
        $pesquisa = $model->findById((int) $id);

        if (!$pesquisa) {
            $this->redirect('/promotor/dashboard');
            return;
        }

        $pdvModel = new Pdv();

        // PDV e visita vindos do workflow
        $pdvId    = (int) ($_GET['pdv_id'] ?? 0);
        $visitaId = (int) ($_GET['visita_id'] ?? 0);
        $pdvAtual = $pdvId ? $pdvModel->findById($pdvId) : null;

        $this->view('promotor.pesquisa-responder', [
            'pageTitle'  => $pesquisa['titulo'],
            'pesquisa'   => $pesquisa,
            'pdvs'       => $pdvModel->all(),
            'pdvId'      => $pdvId,
            'pdvAtual'   => $pdvAtual,
            'visitaId'   => $visitaId,
        ]);
    }

    /**
     * Salva resposta da pesquisa (promotor)
     */
    public function salvarResposta(string $id): void
    {
        Auth::requireRole('promotor');

        $respostaModel = new Resposta();
        $visitaId = !empty($_POST['visita_id']) ? (int) $_POST['visita_id'] : null;

        $respostaModel->create([
            'pesquisa_id' => (int) $id,
            'visita_id'   => $visitaId,
            'promotor_id' => Auth::id(),
            'pdv_id'      => (int) $_POST['pdv_id'],
            'dados'       => $_POST['dados'] ?? '{}',
        ]);

        // Volta para o workflow da visita se veio de lá
        if ($visitaId) {
            $this->redirect('/promotor/visita/' . $visitaId);
        } else {
            $this->redirect('/promotor/dashboard');
        }
    }
}
