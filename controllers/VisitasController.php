<?php
require_once MODELS_PATH . '/Visita.php';
require_once MODELS_PATH . '/Pesquisa.php';
require_once MODELS_PATH . '/Rota.php';

class VisitasController extends Controller
{
    /**
     * Exibe tela de check-in (promotor)
     */
    public function showCheckin(string $id): void
    {
        Auth::requireRole('promotor');

        $model = new Visita();
        $visita = $model->findById((int) $id);

        if (!$visita || $visita['promotor_id'] != Auth::id()) {
            $this->redirect('/promotor/dashboard');
            return;
        }

        // Se já fez check-in, vai para workflow
        if ($visita['status'] === 'em_andamento') {
            $this->redirect('/promotor/visita/' . $id);
            return;
        }

        $this->view('promotor.check-in', [
            'pageTitle' => 'Check-in',
            'visita'    => $visita,
        ]);
    }

    /**
     * Processa check-in — redireciona para workflow
     */
    public function checkin(string $id): void
    {
        Auth::requireRole('promotor');

        $model = new Visita();
        $model->checkin((int) $id, [
            'latitude'  => $_POST['latitude'] ?? null,
            'longitude' => $_POST['longitude'] ?? null,
            'foto'      => $this->handleUpload('foto'),
        ]);

        // Redireciona para a tela de workflow (em vez de voltar ao dashboard)
        $this->redirect('/promotor/visita/' . $id);
    }

    /**
     * Tela de workflow — entre check-in e check-out
     */
    public function showVisita(string $id): void
    {
        Auth::requireRole('promotor');

        $model = new Visita();
        $visita = $model->findById((int) $id);

        if (!$visita || $visita['promotor_id'] != Auth::id()) {
            $this->redirect('/promotor/dashboard');
            return;
        }

        // Se pendente, redirecionar para check-in
        if ($visita['status'] === 'pendente') {
            $this->redirect('/promotor/checkin/' . $id);
            return;
        }

        // Se concluída, voltar ao dashboard
        if ($visita['status'] === 'concluida') {
            $this->redirect('/promotor/dashboard');
            return;
        }

        // Pesquisas do dia
        $rotaModel = new Rota();
        $diaSemana = date('N', strtotime($visita['data_prevista']));
        $pesquisasPorDia = $rotaModel->pesquisasDaSemana((int) $visita['promotor_id']);
        $pesquisasDoDia = $pesquisasPorDia[$diaSemana] ?? [];

        // Fotos de trabalho existentes
        $fotos = json_decode($visita['fotos_trabalho'] ?? '[]', true) ?: [];

        $this->view('promotor.visita-workflow', [
            'pageTitle'  => 'Visita — ' . $visita['pdv_nome'],
            'visita'     => $visita,
            'fotos'      => $fotos,
            'pesquisas'  => $pesquisasDoDia,
        ]);
    }

    /**
     * AJAX — Upload de foto de trabalho
     */
    public function uploadFoto(string $id): void
    {
        Auth::requireRole('promotor');

        $model = new Visita();
        $visita = $model->findById((int) $id);

        if (!$visita || $visita['promotor_id'] != Auth::id()) {
            $this->json(['error' => 'Acesso negado'], 403);
            return;
        }

        $path = $this->handleUpload('foto');
        if (!$path) {
            $this->json(['error' => 'Nenhuma foto enviada'], 400);
            return;
        }

        $model->adicionarFoto((int) $id, $path);
        $this->json(['success' => true, 'path' => $path]);
    }

    /**
     * AJAX — Salvar observação
     */
    public function salvarObservacao(string $id): void
    {
        Auth::requireRole('promotor');

        $model = new Visita();
        $visita = $model->findById((int) $id);

        if (!$visita || $visita['promotor_id'] != Auth::id()) {
            $this->json(['error' => 'Acesso negado'], 403);
            return;
        }

        $model->salvarObservacao((int) $id, $_POST['observacao'] ?? '');
        $this->json(['success' => true]);
    }

    /**
     * Processa check-out — finaliza visita (com foto obrigatória)
     */
    public function checkout(string $id): void
    {
        Auth::requireRole('promotor');

        $model  = new Visita();
        $visita = $model->findById((int) $id);

        // Processa fotos de trabalho enviadas no checkout
        $fotosNovas = [];
        if (!empty($_FILES['fotos_trabalho']['name'])) {
            $total = count($_FILES['fotos_trabalho']['name']);
            for ($i = 0; $i < $total; $i++) {
                if ($_FILES['fotos_trabalho']['error'][$i] === UPLOAD_ERR_OK) {
                    // Normaliza para o formato esperado por handleUpload
                    $singleFile = [
                        'name'     => $_FILES['fotos_trabalho']['name'][$i],
                        'type'     => $_FILES['fotos_trabalho']['type'][$i],
                        'tmp_name' => $_FILES['fotos_trabalho']['tmp_name'][$i],
                        'error'    => $_FILES['fotos_trabalho']['error'][$i],
                        'size'     => $_FILES['fotos_trabalho']['size'][$i],
                    ];
                    $path = $this->handleUploadFromArray($singleFile);
                    if ($path) {
                        $fotosNovas[] = $path;
                    }
                }
            }
        }

        // Adiciona as novas fotos de trabalho
        foreach ($fotosNovas as $path) {
            $model->adicionarFoto((int) $id, $path);
        }

        // Remove fotos de trabalho marcadas para remoção
        if (!empty($_POST['fotos_remover']) && is_array($_POST['fotos_remover'])) {
            $fotos = json_decode($visita['fotos_trabalho'] ?? '[]', true) ?: [];
            foreach ($_POST['fotos_remover'] as $remover) {
                $fotos = array_filter($fotos, fn($f) => $f !== $remover);
                // Remove arquivo físico se existir
                $filePath = rtrim($_SERVER['DOCUMENT_ROOT'] ?? '', '/') . '/' . ltrim($remover, '/');
                if (file_exists($filePath)) {
                    @unlink($filePath);
                }
            }
            $model->salvarFotos((int) $id, array_values($fotos));
        }

        $model->checkout((int) $id, [
            'latitude'   => $_POST['latitude'] ?? null,
            'longitude'  => $_POST['longitude'] ?? null,
            'observacao' => $_POST['observacao'] ?? null,
            'foto'       => $this->handleUpload('foto_checkout'),
        ]);

        $this->redirect('/promotor/dashboard');
    }

    /**
     * API — Visitas recentes (AJAX)
     */
    public function apiRecentes(): void
    {
        Auth::require();

        $model = new Visita();
        $this->json([
            'visitas' => $model->ultimosCheckins(10),
        ]);
    }

    /**
     * Upload a partir de um array de arquivo já normalizado
     */
    private function handleUploadFromArray(array $file): ?string
    {
        $_FILES['_tmp_upload_'] = $file;
        $path = $this->handleUpload('_tmp_upload_');
        unset($_FILES['_tmp_upload_']);
        return $path;
    }

    /**
     * Salva a imagem recebida — já processada pelo navegador (WebP, redimensionada, sem metadados)
     */
    private function handleUpload(string $fieldName): ?string
    {
        if (!isset($_FILES[$fieldName]) || $_FILES[$fieldName]['error'] !== UPLOAD_ERR_OK) {
            return null;
        }

        $file = $_FILES[$fieldName];
        $name = uniqid('foto_') . '.webp';
        $dest = UPLOAD_PATH . '/' . $name;

        if (!is_dir(UPLOAD_PATH)) {
            mkdir(UPLOAD_PATH, 0755, true);
        }

        move_uploaded_file($file['tmp_name'], $dest);
        return 'uploads/' . $name;
    }
}
