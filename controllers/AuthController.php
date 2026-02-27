<?php
require_once MODELS_PATH . '/User.php';

class AuthController extends Controller
{
    /**
     * Rota raiz — redireciona conforme role
     */
    public function home(): void
    {
        if (!Auth::check()) {
            $this->redirect('/login');
            return;
        }

        if (Auth::isAdmin()) {
            $this->redirect('/admin/dashboard');
        } else {
            $this->redirect('/promotor/dashboard');
        }
    }

    /**
     * Exibe a tela de login
     */
    public function showLogin(): void
    {
        // Se já está logado, redireciona
        if (Auth::check()) {
            $this->home();
            return;
        }

        $this->view('auth.login', [], 'auth');
    }

    /**
     * Processa o login
     */
    public function login(): void
    {
        $email = $this->input('email');
        $senha = $_POST['password'] ?? '';

        // Validação básica
        if (empty($email) || empty($senha)) {
            $this->view('auth.login', [
                'error' => 'Preencha todos os campos.',
                'email' => $email,
            ], 'auth');
            return;
        }

        // Busca usuário
        $userModel = new User();
        $user = $userModel->findByEmail($email);

        if (!$user || !password_verify($senha, $user['senha'])) {
            $this->view('auth.login', [
                'error' => 'E-mail ou senha incorretos.',
                'email' => $email,
            ], 'auth');
            return;
        }

        // Login bem-sucedido
        Auth::login($user);

        if ($user['role'] === 'admin') {
            $this->redirect('/admin/dashboard');
        } else {
            $this->redirect('/promotor/dashboard');
        }
    }

    /**
     * Logout
     */
    public function logout(): void
    {
        Auth::logout();
        $this->redirect('/login');
    }
}
