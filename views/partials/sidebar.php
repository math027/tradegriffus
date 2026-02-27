<?php
/**
 * Sidebar dinâmica — muda conforme role do usuário
 */
$role = $user['role'] ?? 'promotor';
$nome = $user['nome'] ?? 'Usuário';
$roleLabel = $role === 'admin' ? 'Gestor' : 'Promotor';
?>
<aside id="sidebar" class="sidebar">
    <div class="sidebar-brand">
        <button id="sidebarToggle" class="btn-menu">
            <i class="fa-solid fa-bars"></i>
        </button>
        <div class="brand-content">
            <img src="/assets/img/logo_tradegriffus.png" alt="TradeGriffus" class="brand-logo-img">
        </div>
    </div>

    <div class="sidebar-user">
        <?php $avatar = $user['avatar'] ?? null; ?>
        <?php if ($avatar): ?>
            <img src="/<?= e($avatar) ?>" alt="Avatar" class="user-avatar-img">
        <?php else: ?>
            <div class="user-avatar"><?= iniciais($nome) ?></div>
        <?php endif; ?>
        <div class="user-info">
            <h4><?= e($nome) ?></h4>
            <p><span class="dot"></span> <?= $roleLabel ?></p>
        </div>
    </div>

    <nav class="sidebar-nav">
        <?php if ($role === 'admin'): ?>
        <!-- ========== MENU ADMIN ========== -->
        <div class="nav-category">Principal</div>
        <a href="/admin/dashboard" class="nav-link <?= is_active('/admin/dashboard') ?>">
            <i class="fa-solid fa-chart-pie"></i>
            <span class="link-text">Dashboard</span>
        </a>
        <a href="/admin/monitoramento" class="nav-link <?= is_active('/admin/monitoramento') ?>">
            <i class="fa-solid fa-satellite-dish"></i>
            <span class="link-text">Monitoramento</span>
        </a>
        <a href="/admin/rotas" class="nav-link <?= is_active('/admin/rotas') ?>">
            <i class="fa-solid fa-calendar-check"></i>
            <span class="link-text">Rotas da Semana</span>
        </a>
        <a href="/admin/pdvs" class="nav-link <?= is_active('/admin/pdvs') ?>">
            <i class="fa-solid fa-map-location-dot"></i>
            <span class="link-text">Pontos de Venda</span>
        </a>

        <div class="nav-category">Equipe</div>
        <a href="/admin/colaboradores" class="nav-link <?= is_active('/admin/colaboradores') ?>">
            <i class="fa-solid fa-users"></i>
            <span class="link-text">Colaboradores</span>
        </a>
        <a href="/admin/ponto" class="nav-link <?= is_active('/admin/ponto') ?>">
            <i class="fa-solid fa-clock"></i>
            <span class="link-text">Ponto</span>
        </a>
        <a href="/admin/pesquisas" class="nav-link <?= is_active('/admin/pesquisas') ?>">
            <i class="fa-solid fa-clipboard-list"></i>
            <span class="link-text">Pesquisas</span>
        </a>

        <div class="nav-category">Gestão</div>
        <a href="/admin/galeria" class="nav-link <?= is_active('/admin/galeria') ?>">
            <i class="fa-solid fa-images"></i>
            <span class="link-text">Galeria de Fotos</span>
        </a>
        <a href="/admin/relatorios" class="nav-link <?= is_active('/admin/relatorios') ?>">
            <i class="fa-solid fa-file-invoice"></i>
            <span class="link-text">Relatórios</span>
        </a>

        <?php else: ?>
        <!-- ========== MENU PROMOTOR ========== -->
        <div class="nav-category">Meu Dia</div>
        <a href="/promotor/dashboard" class="nav-link <?= is_active('/promotor/dashboard') ?>">
            <i class="fa-solid fa-house"></i>
            <span class="link-text">Painel</span>
        </a>
        <a href="/promotor/rotas" class="nav-link <?= is_active('/promotor/rotas') ?>">
            <i class="fa-solid fa-route"></i>
            <span class="link-text">Minhas Rotas</span>
        </a>
        <a href="/promotor/mapa" class="nav-link <?= is_active('/promotor/mapa') ?>">
            <i class="fa-solid fa-map-location-dot"></i>
            <span class="link-text">Mapa</span>
        </a>
        <?php if (($user['tipo_contrato'] ?? '') === 'clt'): ?>
        <a href="/promotor/ponto" class="nav-link <?= is_active('/promotor/ponto') ?>">
            <i class="fa-solid fa-clock"></i>
            <span class="link-text">Meu Ponto</span>
        </a>
        <?php endif; ?>

        <div class="nav-category">Conta</div>
        <a href="/promotor/perfil" class="nav-link <?= is_active('/promotor/perfil') ?>">
            <i class="fa-solid fa-user"></i>
            <span class="link-text">Meu Perfil</span>
        </a>
        <?php endif; ?>
    </nav>

    <div class="sidebar-footer">
        <a href="/logout" class="nav-link logout-link">
            <i class="fa-solid fa-arrow-right-from-bracket"></i>
            <span class="link-text">Sair</span>
        </a>
    </div>
</aside>
