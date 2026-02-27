<?php
require_once MODELS_PATH . '/User.php';

class ColaboradoresController extends Controller
{
    public function index(): void
    {
        Auth::requireRole('admin');

        $model = new User();

        $this->view('admin.colaboradores', [
            'pageTitle'     => 'Colaboradores',
        'pageIcon'      => 'fa-solid fa-users',
            'colaboradores' => $model->all(),
        ]);
    }

    public function create(): void
    {
        Auth::requireRole('admin');

        $this->view('admin.colaboradores-form', [
            'pageTitle'    => 'Novo Colaborador',
            'colaborador'  => null,
        ]);
    }

    public function store(): void
    {
        Auth::requireRole('admin');

        $model = new User();

        // Verifica se email já existe
        $existing = $model->findByEmail($this->input('email'));
        if ($existing) {
            $this->view('admin.colaboradores-form', [
                'pageTitle'   => 'Novo Colaborador',
                'colaborador' => null,
                'error'       => 'Este e-mail já está cadastrado.',
            ]);
            return;
        }

        $model->create([
            'nome'           => $this->input('nome'),
            'email'          => $this->input('email'),
            'senha'          => $_POST['senha'],
            'role'           => $_POST['role'] ?? 'promotor',
            'tipo_contrato'  => $_POST['tipo_contrato'] ?? 'pj',
            'telefone'       => $this->input('telefone'),
        ]);

        $this->redirect('/admin/colaboradores');
    }

    public function edit(string $id): void
    {
        Auth::requireRole('admin');

        $model = new User();
        $colaborador = $model->findById((int) $id);

        if (!$colaborador) {
            $this->redirect('/admin/colaboradores');
            return;
        }

        $this->view('admin.colaboradores-form', [
            'pageTitle'   => 'Editar Colaborador',
            'colaborador' => $colaborador,
        ]);
    }

    public function update(string $id): void
    {
        Auth::requireRole('admin');

        $model = new User();
        $data = [
            'nome'          => $this->input('nome'),
            'email'         => $this->input('email'),
            'role'          => $_POST['role'] ?? 'promotor',
            'tipo_contrato' => $_POST['tipo_contrato'] ?? 'pj',
            'telefone'      => $this->input('telefone'),
        ];

        // Só atualiza senha se preenchida
        if (!empty($_POST['senha'])) {
            $data['senha'] = $_POST['senha'];
        }

        $model->update((int) $id, $data);
        $this->redirect('/admin/colaboradores');
    }

    public function destroy(string $id): void
    {
        Auth::requireRole('admin');

        $model = new User();
        $model->delete((int) $id);
        $this->redirect('/admin/colaboradores');
    }

    /**
     * Reativar colaborador (soft-deleted)
     */
    public function reativar(string $id): void
    {
        Auth::requireRole('admin');

        $model = new User();
        $model->reativar((int) $id);
        $this->redirect('/admin/colaboradores');
    }

    /**
     * Exclusão permanente
     */
    public function destroyPermanente(string $id): void
    {
        Auth::requireRole('admin');

        $model = new User();
        $model->deletePermanente((int) $id);
        $this->redirect('/admin/colaboradores');
    }

    /**
     * Perfil do promotor (próprio usuário)
     */
    public function perfil(): void
    {
        Auth::requireRole('promotor');

        $model = new User();
        $user = $model->findById(Auth::id());

        $this->view('promotor.perfil', [
            'pageTitle' => 'Meu Perfil',
            'usuario'   => $user,
        ]);
    }

    /**
     * Atualiza perfil do promotor
     */
    public function atualizarPerfil(): void
    {
        Auth::requireRole('promotor');

        $model = new User();
        $data = [
            'nome'     => $this->input('nome'),
            'telefone' => $this->input('telefone'),
        ];

        if (!empty($_POST['senha'])) {
            $data['senha'] = $_POST['senha'];
        }

        // Avatar upload
        if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['avatar'];
            $allowed = ['image/jpeg', 'image/png', 'image/webp'];

            if (in_array($file['type'], $allowed) && $file['size'] <= 2 * 1024 * 1024) {
                $ext  = pathinfo($file['name'], PATHINFO_EXTENSION);
                $name = 'avatar_' . Auth::id() . '_' . time() . '.' . $ext;
                $dir  = UPLOAD_PATH . '/avatars';

                if (!is_dir($dir)) mkdir($dir, 0755, true);

                // Remove avatar anterior
                $current = $model->findById(Auth::id());
                if (!empty($current['avatar']) && file_exists(PUBLIC_PATH . '/' . $current['avatar'])) {
                    unlink(PUBLIC_PATH . '/' . $current['avatar']);
                }

                move_uploaded_file($file['tmp_name'], $dir . '/' . $name);
                $data['avatar'] = 'uploads/avatars/' . $name;

                // Atualiza sessão para refletir imediatamente
                $_SESSION['user_avatar'] = $data['avatar'];
            }
        }

        $model->update(Auth::id(), $data);
        $this->redirect('/promotor/perfil');
    }
}
