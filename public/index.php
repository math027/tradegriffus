<?php
/**
 * TradeGriffus v2 — Front Controller
 * Toda requisição passa por aqui via .htaccess
 */

// Carrega configurações
require_once __DIR__ . '/../config/app.php';

// Carrega core
require_once CORE_PATH . '/Database.php';
require_once CORE_PATH . '/Router.php';
require_once CORE_PATH . '/Controller.php';
require_once CORE_PATH . '/Auth.php';
require_once CORE_PATH . '/helpers.php';

// Inicia sessão
Auth::init();

// =============================================
// DEFINIÇÃO DE ROTAS
// =============================================
$router = new Router();

// --- Auth ---
$router->get('/login',  'AuthController', 'showLogin');
$router->post('/login', 'AuthController', 'login');
$router->get('/logout', 'AuthController', 'logout');

// --- Rota raiz → redireciona conforme role ---
$router->get('/', 'AuthController', 'home');

// --- Admin ---
$router->get('/admin/dashboard',     'DashboardController', 'admin');
$router->get('/admin/monitoramento', 'MonitoramentoController', 'index');
$router->get('/admin/rotas',               'RotasController', 'index');
$router->post('/admin/rotas/adicionar-pdv', 'RotasController', 'adicionarPdv');
$router->post('/admin/rotas/remover-pdv',   'RotasController', 'removerPdv');
$router->post('/admin/rotas/reordenar',     'RotasController', 'reordenar');
$router->post('/admin/rotas/otimizar',      'RotasController', 'otimizar');
$router->post('/admin/rotas/sincronizar-pdvs',      'RotasController', 'sincronizarPdvs');
$router->post('/admin/rotas/sincronizar-pesquisas',  'RotasController', 'sincronizarPesquisas');
$router->get('/api/rotas/semana',           'RotasController', 'apiSemana');


$router->get('/admin/pdvs',          'PdvsController', 'index');
$router->get('/admin/pdvs/criar',    'PdvsController', 'create');
$router->post('/admin/pdvs/salvar',  'PdvsController', 'store');
$router->get('/admin/pdvs/{id}',     'PdvsController', 'show');
$router->get('/admin/pdvs/{id}/editar', 'PdvsController', 'edit');
$router->post('/admin/pdvs/{id}/atualizar', 'PdvsController', 'update');
$router->post('/admin/pdvs/{id}/excluir', 'PdvsController', 'destroy');
$router->get('/admin/galeria',            'PdvsController', 'galeria');
$router->post('/admin/limpeza',           'PdvsController', 'limpar');

$router->get('/admin/colaboradores',         'ColaboradoresController', 'index');
$router->get('/admin/colaboradores/criar',   'ColaboradoresController', 'create');
$router->post('/admin/colaboradores/salvar', 'ColaboradoresController', 'store');
$router->get('/admin/colaboradores/{id}/editar', 'ColaboradoresController', 'edit');
$router->post('/admin/colaboradores/{id}/atualizar', 'ColaboradoresController', 'update');
$router->post('/admin/colaboradores/{id}/excluir', 'ColaboradoresController', 'destroy');
$router->post('/admin/colaboradores/{id}/reativar', 'ColaboradoresController', 'reativar');
$router->post('/admin/colaboradores/{id}/excluir-permanente', 'ColaboradoresController', 'destroyPermanente');

$router->get('/admin/pesquisas',          'PesquisasController', 'index');
$router->get('/admin/pesquisas/criar',    'PesquisasController', 'create');
$router->post('/admin/pesquisas/salvar',  'PesquisasController', 'store');
$router->get('/admin/pesquisas/{id}/editar', 'PesquisasController', 'edit');
$router->post('/admin/pesquisas/{id}/atualizar', 'PesquisasController', 'update');
$router->post('/admin/pesquisas/{id}/excluir', 'PesquisasController', 'destroy');
$router->post('/admin/pesquisas/{id}/excluir-permanente', 'PesquisasController', 'destroyPermanente');
$router->get('/admin/pesquisas/{id}/respostas', 'PesquisasController', 'respostas');

$router->get('/admin/relatorios',     'RelatoriosController', 'index');
$router->get('/admin/relatorios/exportar', 'RelatoriosController', 'exportar');
$router->get('/admin/ponto',          'PontoController', 'index');
$router->post('/admin/ponto/ajustar', 'PontoController', 'ajustar');

// --- Promotor ---
$router->get('/promotor/dashboard',   'DashboardController', 'promotor');
$router->get('/promotor/mapa',        'DashboardController', 'mapaPromotor');
$router->get('/promotor/rotas',       'RotasController', 'minhasRotas');
$router->get('/promotor/checkin/{id}', 'VisitasController', 'showCheckin');
$router->post('/promotor/checkin/{id}', 'VisitasController', 'checkin');
$router->get('/promotor/visita/{id}',  'VisitasController', 'showVisita');
$router->post('/promotor/visita/{id}/fotos', 'VisitasController', 'uploadFoto');
$router->post('/promotor/visita/{id}/observacao', 'VisitasController', 'salvarObservacao');
$router->post('/promotor/checkout/{id}', 'VisitasController', 'checkout');
$router->get('/promotor/pesquisas',    'PesquisasController', 'promotorIndex');
$router->get('/promotor/pesquisas/{id}', 'PesquisasController', 'responder');
$router->post('/promotor/pesquisas/{id}/salvar', 'PesquisasController', 'salvarResposta');
$router->get('/promotor/perfil',       'ColaboradoresController', 'perfil');
$router->post('/promotor/perfil',      'ColaboradoresController', 'atualizarPerfil');
$router->get('/promotor/ponto',        'PontoController', 'meuPonto');
$router->post('/promotor/ponto/registrar', 'PontoController', 'registrar');

// --- API (JSON) ---
$router->get('/api/dashboard/stats',   'DashboardController', 'apiStats');
$router->get('/api/visitas/recentes',  'VisitasController', 'apiRecentes');
$router->get('/api/pdvs/{id}/fotos-dia', 'MonitoramentoController', 'apiFotosDia');

// =============================================
// DESPACHA
// =============================================
$router->dispatch();
