<?php
/**
 * Gerenciamento de autenticação e sessão
 */
class Auth
{
    /**
     * Inicia sessão se ainda não estiver ativa
     */
    public static function init(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start([
                'cookie_lifetime' => SESSION_LIFETIME,
                'cookie_httponly'  => true,
                'cookie_samesite' => 'Lax',
            ]);
        }
    }

    /**
     * Faz login do usuário — salva dados na sessão
     */
    public static function login(array $user): void
    {
        self::init();
        $_SESSION['user_id']             = $user['id'];
        $_SESSION['user_nome']           = $user['nome'];
        $_SESSION['user_role']           = $user['role'];
        $_SESSION['user_avatar']         = $user['avatar'] ?? null;
        $_SESSION['user_tipo_contrato']  = $user['tipo_contrato'] ?? 'pj';
        $_SESSION['logged_in']           = true;
        session_regenerate_id(true);
    }

    /**
     * Destrói a sessão e desloga
     */
    public static function logout(): void
    {
        self::init();
        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(), '', time() - 42000,
                $params['path'], $params['domain'],
                $params['secure'], $params['httponly']
            );
        }

        session_destroy();
    }

    /**
     * Verifica se o usuário está autenticado
     */
    public static function check(): bool
    {
        self::init();
        return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
    }

    /**
     * Retorna dados do usuário logado
     */
    public static function user(): ?array
    {
        if (!self::check()) return null;

        return [
            'id'             => $_SESSION['user_id'],
            'nome'           => $_SESSION['user_nome'],
            'role'           => $_SESSION['user_role'],
            'avatar'         => $_SESSION['user_avatar'] ?? null,
            'tipo_contrato'  => $_SESSION['user_tipo_contrato'] ?? 'pj',
        ];
    }

    /**
     * Retorna o ID do usuário logado
     */
    public static function id(): ?int
    {
        return self::check() ? (int) $_SESSION['user_id'] : null;
    }

    /**
     * Verifica se o usuário é admin
     */
    public static function isAdmin(): bool
    {
        return self::check() && $_SESSION['user_role'] === 'admin';
    }

    /**
     * Verifica se o usuário é promotor
     */
    public static function isPromotor(): bool
    {
        return self::check() && $_SESSION['user_role'] === 'promotor';
    }

    /**
     * Exige autenticação — redireciona para login se não autenticado
     */
    public static function require(): void
    {
        if (!self::check()) {
            redirect('/login');
        }
    }

    /**
     * Exige role específica
     */
    public static function requireRole(string $role): void
    {
        self::require();
        if ($_SESSION['user_role'] !== $role) {
            http_response_code(403);
            echo '<h1>403 — Acesso Negado</h1>';
            exit;
        }
    }
}
