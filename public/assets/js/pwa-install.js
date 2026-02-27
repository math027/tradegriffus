/**
 * TradeGriffus — PWA Install Banner
 * Banner customizado para instalar o app
 */

const PWAInstall = {
    _deferredPrompt: null,
    _dismissed: false,

    init() {
        // iOS detection
        const isIOS = /iPad|iPhone|iPod/.test(navigator.userAgent) && !window.MSStream;
        const isStandalone = window.matchMedia('(display-mode: standalone)').matches
            || navigator.standalone === true;

        // Já está instalado — não mostra nada
        if (isStandalone) return;

        // Android/Chrome — intercepta o prompt nativo
        window.addEventListener('beforeinstallprompt', (e) => {
            e.preventDefault();
            this._deferredPrompt = e;
            this._showBanner();
        });

        // iOS — mostra instrução manual (só para promotores)
        if (isIOS && !this._wasDismissed()) {
            setTimeout(() => this._showIOSBanner(), 3000);
        }

        // Detectar instalação concluída
        window.addEventListener('appinstalled', () => {
            this._hideBanner();
            App.toast('✅ App instalado com sucesso!', 'success');
        });
    },

    /**
     * Mostra banner de instalação (Android/Chrome)
     */
    _showBanner() {
        if (this._wasDismissed()) return;

        // Só mostra para promotores (verifica se está na área do promotor)
        if (!location.pathname.startsWith('/promotor')) return;

        const banner = document.createElement('div');
        banner.id = 'pwa-install-banner';
        banner.className = 'pwa-install-banner';
        banner.innerHTML = `
            <div class="pwa-install-content">
                <div class="pwa-install-icon">
                    <img src="/assets/img/icone_tradegriffus.png" alt="Griffus" width="40" height="40">
                </div>
                <div class="pwa-install-text">
                    <strong>Instalar o TradeGriffus</strong>
                    <span>Acesse rápido pela tela inicial</span>
                </div>
                <div class="pwa-install-actions">
                    <button class="pwa-install-btn" id="pwaInstallBtn">
                        Instalar
                    </button>
                    <button class="pwa-install-dismiss" id="pwaDismissBtn">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
            </div>
        `;

        document.body.appendChild(banner);
        requestAnimationFrame(() => banner.classList.add('show'));

        document.getElementById('pwaInstallBtn').addEventListener('click', () => this._install());
        document.getElementById('pwaDismissBtn').addEventListener('click', () => this._dismiss());
    },

    /**
     * Mostra instrução para iOS (Safari)
     */
    _showIOSBanner() {
        if (this._wasDismissed()) return;
        if (!location.pathname.startsWith('/promotor')) return;

        const banner = document.createElement('div');
        banner.id = 'pwa-install-banner';
        banner.className = 'pwa-install-banner pwa-install-ios';
        banner.innerHTML = `
            <div class="pwa-install-content">
                <div class="pwa-install-icon">
                    <img src="/assets/img/icone_tradegriffus.png" alt="Griffus" width="40" height="40">
                </div>
                <div class="pwa-install-text">
                    <strong>Instalar o TradeGriffus</strong>
                    <span>
                        Toque em <i class="fa-solid fa-arrow-up-from-bracket"></i>
                        e depois <strong>"Tela de Início"</strong>
                    </span>
                </div>
                <div class="pwa-install-actions">
                    <button class="pwa-install-dismiss" id="pwaDismissBtn">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
            </div>
        `;

        document.body.appendChild(banner);
        requestAnimationFrame(() => banner.classList.add('show'));

        document.getElementById('pwaDismissBtn').addEventListener('click', () => this._dismiss());
    },

    /**
     * Dispara o prompt de instalação nativo
     */
    async _install() {
        if (!this._deferredPrompt) return;

        this._deferredPrompt.prompt();
        const { outcome } = await this._deferredPrompt.userChoice;

        if (outcome === 'accepted') {
            this._hideBanner();
        }
        this._deferredPrompt = null;
    },

    /**
     * Dispensa o banner por 7 dias
     */
    _dismiss() {
        this._hideBanner();
        localStorage.setItem('pwa_dismissed', Date.now().toString());
    },

    /**
     * Esconde o banner
     */
    _hideBanner() {
        const banner = document.getElementById('pwa-install-banner');
        if (banner) {
            banner.classList.remove('show');
            setTimeout(() => banner.remove(), 400);
        }
    },

    /**
     * Verifica se o banner foi dispensado nos últimos 7 dias
     */
    _wasDismissed() {
        const dismissed = localStorage.getItem('pwa_dismissed');
        if (!dismissed) return false;
        const elapsed = Date.now() - parseInt(dismissed);
        return elapsed < 7 * 24 * 60 * 60 * 1000; // 7 dias
    },
};

document.addEventListener('DOMContentLoaded', () => {
    PWAInstall.init();
});
