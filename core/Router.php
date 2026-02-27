<?php
/**
 * Router simples — mapeia URL + HTTP method para controller@action
 */
class Router
{
    private array $routes = [];

    /**
     * Registra rota
     */
    public function add(string $method, string $path, string $controller, string $action): void
    {
        $this->routes[] = [
            'method'     => strtoupper($method),
            'path'       => $path,
            'controller' => $controller,
            'action'     => $action,
        ];
    }

    // Atalhos
    public function get(string $path, string $controller, string $action): void
    {
        $this->add('GET', $path, $controller, $action);
    }

    public function post(string $path, string $controller, string $action): void
    {
        $this->add('POST', $path, $controller, $action);
    }

    /**
     * Despacha a requisição atual para o controller correto
     */
    public function dispatch(): void
    {
        $requestMethod = $_SERVER['REQUEST_METHOD'];
        $requestUri    = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

        // Remove trailing slash (exceto root)
        if ($requestUri !== '/') {
            $requestUri = rtrim($requestUri, '/');
        }

        foreach ($this->routes as $route) {
            $pattern = $this->convertToRegex($route['path']);

            if ($route['method'] === $requestMethod && preg_match($pattern, $requestUri, $matches)) {
                // Extrai parâmetros nomeados
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);

                $controllerName = $route['controller'];
                $actionName     = $route['action'];

                // Carrega e instancia o controller
                $controllerFile = CONTROLLERS_PATH . '/' . $controllerName . '.php';
                if (!file_exists($controllerFile)) {
                    $this->abort(500, "Controller não encontrado: {$controllerName}");
                    return;
                }

                require_once $controllerFile;

                if (!class_exists($controllerName)) {
                    $this->abort(500, "Classe não encontrada: {$controllerName}");
                    return;
                }

                $controller = new $controllerName();

                if (!method_exists($controller, $actionName)) {
                    $this->abort(500, "Action não encontrada: {$controllerName}@{$actionName}");
                    return;
                }

                // Executa a action passando os parâmetros da URL
                call_user_func_array([$controller, $actionName], $params);
                return;
            }
        }

        // Nenhuma rota encontrada
        $this->abort(404, 'Página não encontrada');
    }

    /**
     * Converte path com {param} em regex
     * Ex: /roteiros/{id} → #^/roteiros/(?P<id>[^/]+)$#
     */
    private function convertToRegex(string $path): string
    {
        $pattern = preg_replace('/\{(\w+)\}/', '(?P<$1>[^/]+)', $path);
        return '#^' . $pattern . '$#';
    }

    /**
     * Exibe página de erro
     */
    private function abort(int $code, string $message = ''): void
    {
        http_response_code($code);
        
        $errorFile = VIEWS_PATH . "/errors/{$code}.php";
        if (file_exists($errorFile)) {
            require $errorFile;
        } else {
            echo "<h1>Erro {$code}</h1><p>{$message}</p>";
        }
    }
}
