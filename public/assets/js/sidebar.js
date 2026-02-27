/**
 * TradeGriffus v2 — Sidebar Toggle
 */
document.addEventListener('DOMContentLoaded', () => {
    const sidebar = document.getElementById('sidebar');
    const toggleBtn = document.getElementById('sidebarToggle');
    const mobileBtn = document.getElementById('mobileMenuBtn');
    const isMobile = () => window.innerWidth <= 768;

    // Overlay para mobile
    let overlay = document.querySelector('.sidebar-overlay');
    if (!overlay) {
        overlay = document.createElement('div');
        overlay.className = 'sidebar-overlay';
        document.body.appendChild(overlay);
    }

    // Toggle desktop → collapse/expand
    if (toggleBtn) {
        toggleBtn.addEventListener('click', () => {
            if (isMobile()) {
                sidebar.classList.toggle('mobile-open');
                overlay.classList.toggle('active');
            } else {
                sidebar.classList.toggle('collapsed');
            }
        });
    }

    // Toggle mobile → abre sidebar como drawer
    if (mobileBtn) {
        mobileBtn.addEventListener('click', () => {
            sidebar.classList.add('mobile-open');
            overlay.classList.add('active');
        });
    }

    // Fecha ao clicar no overlay
    overlay.addEventListener('click', () => {
        sidebar.classList.remove('mobile-open');
        overlay.classList.remove('active');
    });

    // Fecha sidebar mobile ao navegar
    document.querySelectorAll('.sidebar-nav .nav-link').forEach(link => {
        link.addEventListener('click', () => {
            if (isMobile()) {
                sidebar.classList.remove('mobile-open');
                overlay.classList.remove('active');
            }
        });
    });

    // Ajuste de resize
    window.addEventListener('resize', () => {
        if (!isMobile()) {
            sidebar.classList.remove('mobile-open');
            overlay.classList.remove('active');
        }
    });
});
