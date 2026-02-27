<?php
/**
 * Funções utilitárias globais
 */

/**
 * Redireciona para uma URL
 */
function redirect(string $url): void
{
    header('Location: ' . $url);
    exit;
}

/**
 * Gera URL absoluta a partir de path relativo
 */
function url(string $path = ''): string
{
    return rtrim(BASE_URL, '/') . '/' . ltrim($path, '/');
}

/**
 * Gera URL para asset estático (css, js, img)
 */
function asset(string $path): string
{
    return url('assets/' . ltrim($path, '/'));
}

/**
 * Escapa HTML para prevenir XSS
 */
function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

/**
 * Gera token CSRF e armazena na sessão
 */
function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Gera campo hidden com CSRF token
 */
function csrf_field(): string
{
    return '<input type="hidden" name="_token" value="' . csrf_token() . '">';
}

/**
 * Valida CSRF token da requisição
 */
function csrf_validate(): bool
{
    $token = $_POST['_token'] ?? '';
    return hash_equals($_SESSION['csrf_token'] ?? '', $token);
}

/**
 * Formata data para exibição BR
 */
function data_br(string $date): string
{
    if (empty($date)) return '--';
    return date('d/m/Y', strtotime($date));
}

/**
 * Formata data+hora para exibição BR
 */
function datahora_br(string $datetime): string
{
    if (empty($datetime)) return '--:--';
    return date('d/m/Y H:i', strtotime($datetime));
}

/**
 * Formata hora
 */
function hora_br(string $datetime): string
{
    if (empty($datetime)) return '--:--';
    return date('H:i', strtotime($datetime));
}

/**
 * Retorna as iniciais do nome (para avatares)
 */
function iniciais(string $nome): string
{
    $partes = explode(' ', trim($nome));
    $iniciais = strtoupper(mb_substr($partes[0], 0, 1));
    if (count($partes) > 1) {
        $iniciais .= strtoupper(mb_substr(end($partes), 0, 1));
    }
    return $iniciais;
}

/**
 * Renderiza avatar (foto ou iniciais) como HTML
 */
function avatar_html(?string $avatar, string $nome, bool $small = false): string
{
    $cls = $small ? 'activity-avatar small' : 'activity-avatar';
    if (!empty($avatar)) {
        $size = $small ? '28px' : '38px';
        return '<img src="/' . e($avatar) . '" alt="' . e($nome) . '" class="avatar-img' . ($small ? ' avatar-sm' : '') . '" style="width:' . $size . ';height:' . $size . ';border-radius:50%;object-fit:cover;">';
    }
    return '<div class="' . $cls . '">' . iniciais($nome) . '</div>';
}

/**
 * Retorna classe CSS para status
 */
function status_class(string $status): string
{
    $map = [
        'pendente'      => 'status-pending',
        'em_andamento'  => 'status-progress',
        'concluido'     => 'status-done',
        'concluida'     => 'status-done',
        'atrasado'      => 'status-late',
        'justificada'   => 'status-justified',
    ];
    return $map[$status] ?? 'status-default';
}

/**
 * Retorna label legível para status
 */
function status_label(string $status): string
{
    $map = [
        'pendente'      => 'Pendente',
        'em_andamento'  => 'Em Andamento',
        'concluido'     => 'Concluído',
        'concluida'     => 'Concluída',
        'atrasado'      => 'Atrasado',
        'justificada'   => 'Justificada',
    ];
    return $map[$status] ?? ucfirst($status);
}

/**
 * Verifica se a rota atual corresponde ao path informado
 */
function is_active(string $path): string
{
    $current = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    return ($current === $path) ? 'active' : '';
}
