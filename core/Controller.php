<?php
/**
 * Classe base para todos os controllers
 */
class Controller
{
    /**
     * Renderiza uma view dentro do layout
     */
    protected function view(string $viewName, array $data = [], string $layout = 'app'): void
    {
        // Extrai variáveis para ficarem acessíveis na view
        extract($data);

        // Captura o conteúdo da view
        ob_start();
        $viewFile = VIEWS_PATH . '/' . str_replace('.', '/', $viewName) . '.php';

        if (!file_exists($viewFile)) {
            throw new RuntimeException("View não encontrada: {$viewFile}");
        }

        require $viewFile;
        $content = ob_get_clean();

        // Renderiza dentro do layout
        $layoutFile = VIEWS_PATH . '/layouts/' . $layout . '.php';
        if (file_exists($layoutFile)) {
            require $layoutFile;
        } else {
            echo $content;
        }
    }

    /**
     * Retorna resposta JSON (para API/AJAX)
     */
    protected function json(array $data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }

    /**
     * Redireciona para outra URL
     */
    protected function redirect(string $url): void
    {
        header('Location: ' . $url);
        exit;
    }

    /**
     * Pega dados do POST com sanitização
     */
    protected function input(string $key, $default = null)
    {
        return isset($_POST[$key]) ? htmlspecialchars(trim($_POST[$key]), ENT_QUOTES, 'UTF-8') : $default;
    }

    /**
     * Pega dados do GET
     */
    protected function query(string $key, $default = null)
    {
        return isset($_GET[$key]) ? htmlspecialchars(trim($_GET[$key]), ENT_QUOTES, 'UTF-8') : $default;
    }
}
