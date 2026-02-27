<?php
require_once MODELS_PATH . '/Pdv.php';

class PdvsController extends Controller
{
    public function index(): void
    {
        Auth::requireRole('admin');

        $model = new Pdv();

        $this->view('admin.pdvs', [
            'pageTitle' => 'Pontos de Venda',
        'pageIcon'  => 'fa-solid fa-store',
            'pdvs'      => $model->all(),
        ]);
    }

    public function create(): void
    {
        Auth::requireRole('admin');

        $this->view('admin.pdvs-form', [
            'pageTitle' => 'Novo Ponto de Venda',
            'pdv'       => null,
        ]);
    }

    public function store(): void
    {
        Auth::requireRole('admin');

        $model = new Pdv();
        $model->create([
            'codigo'      => $this->input('codigo'),
            'nome'        => $this->input('nome'),
            'cnpj'        => $this->input('cnpj'),
            'rua'         => $this->input('rua'),
            'numero'      => $this->input('numero'),
            'bairro'      => $this->input('bairro'),
            'cidade'      => $this->input('cidade'),
            'uf'          => $this->input('uf'),
            'cep'         => $this->input('cep'),
            'endereco'    => $this->input('endereco'),
            'latitude'    => !empty($_POST['latitude']) ? $_POST['latitude'] : null,
            'longitude'   => !empty($_POST['longitude']) ? $_POST['longitude'] : null,
            'responsavel' => $this->input('responsavel'),
            'telefone'    => $this->input('telefone'),
        ]);

        $this->redirect('/admin/pdvs');
    }

    public function show(string $id): void
    {
        Auth::requireRole('admin');

        $model = new Pdv();
        $pdv = $model->findById((int) $id);

        if (!$pdv) {
            $this->redirect('/admin/pdvs');
            return;
        }

        $this->view('admin.pdv-detalhe', [
            'pageTitle'       => $pdv['nome'],
            'pdv'             => $pdv,
            'ultimosCheckins' => $model->ultimosCheckinsComCoordenadas((int) $id, 3),
            'visitas'         => $model->visitasPorPdv((int) $id, 20),
            'respostas'       => $model->respostasPorPdv((int) $id, 20),
        ]);
    }

    public function edit(string $id): void
    {
        Auth::requireRole('admin');

        $model = new Pdv();
        $pdv = $model->findById((int) $id);

        if (!$pdv) {
            $this->redirect('/admin/pdvs');
            return;
        }

        $this->view('admin.pdvs-form', [
            'pageTitle'       => 'Editar PDV',
            'pdv'             => $pdv,
            'ultimosCheckins' => $model->ultimosCheckinsComCoordenadas((int) $id, 3),
        ]);
    }

    public function update(string $id): void
    {
        Auth::requireRole('admin');

        $model = new Pdv();
        $model->update((int) $id, [
            'codigo'      => $this->input('codigo'),
            'nome'        => $this->input('nome'),
            'cnpj'        => $this->input('cnpj'),
            'rua'         => $this->input('rua'),
            'numero'      => $this->input('numero'),
            'bairro'      => $this->input('bairro'),
            'cidade'      => $this->input('cidade'),
            'uf'          => $this->input('uf'),
            'cep'         => $this->input('cep'),
            'endereco'    => $this->input('endereco'),
            'latitude'    => !empty($_POST['latitude']) ? $_POST['latitude'] : null,
            'longitude'   => !empty($_POST['longitude']) ? $_POST['longitude'] : null,
            'responsavel' => $this->input('responsavel'),
            'telefone'    => $this->input('telefone'),
        ]);

        $this->redirect('/admin/pdvs');
    }

    public function destroy(string $id): void
    {
        Auth::requireRole('admin');

        $model = new Pdv();
        $model->delete((int) $id);
        $this->redirect('/admin/pdvs');
    }

    /**
     * Galeria geral de fotos de todas as visitas
     */
    public function galeria(): void
    {
        Auth::requireRole('admin');

        $model = new Pdv();

        $this->view('admin.galeria', [
            'pageTitle' => 'Galeria de Fotos',
            'pageIcon'  => 'fa-solid fa-images',
            'pageSubtitle' => 'Todas as fotos registradas pelos promotores',
            'visitas'   => $model->todasFotosVisitas(50),
        ]);
    }

    /**
     * Limpeza de dados antigos (fotos + respostas > 45 dias)
     */
    public function limpar(): void
    {
        Auth::requireRole('admin');

        $model = new Pdv();
        $resultado = $model->limparDadosAntigos(45);

        header('Content-Type: application/json');
        echo json_encode([
            'sucesso' => true,
            'mensagem' => "Limpeza concluída: {$resultado['arquivos_deletados']} arquivos deletados, {$resultado['fotos_removidas']} visitas limpas, {$resultado['respostas_removidas']} respostas removidas.",
            'detalhes' => $resultado,
        ]);
        exit;
    }
}
