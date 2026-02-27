/**
 * TradeGriffus v2 — JS Global
 */

const App = {
    /**
     * Fetch wrapper com tratamento de erro
     */
    async fetch(url, options = {}) {
        try {
            const res = await fetch(url, {
                headers: {
                    'Content-Type': 'application/json',
                    ...options.headers,
                },
                ...options,
            });

            if (!res.ok) {
                throw new Error(`HTTP ${res.status}`);
            }

            return await res.json();
        } catch (err) {
            console.error('Fetch error:', err);
            return null;
        }
    },

    /**
     * Toast notification — canto superior direito, estilizado
     */
    toast(message, type = 'info', duration = 4000) {
        const icons = {
            success: 'fa-circle-check',
            danger:  'fa-circle-xmark',
            warning: 'fa-triangle-exclamation',
            info:    'fa-circle-info',
        };

        const toast = document.createElement('div');
        toast.className = `tg-toast tg-toast-${type}`;
        toast.innerHTML = `
            <i class="fa-solid ${icons[type] || icons.info}"></i>
            <span class="tg-toast-msg">${message}</span>
            <button class="tg-toast-close" onclick="this.parentElement.remove()">
                <i class="fa-solid fa-xmark"></i>
            </button>
        `;

        let container = document.getElementById('tg-toast-container');
        if (!container) {
            container = document.createElement('div');
            container.id = 'tg-toast-container';
            document.body.appendChild(container);
        }

        container.appendChild(toast);
        requestAnimationFrame(() => toast.classList.add('show'));

        setTimeout(() => {
            toast.classList.remove('show');
            setTimeout(() => toast.remove(), 400);
        }, duration);
    },

    /**
     * Modal de confirmação estilizado (substitui window.confirm)
     */
    confirm(message, onConfirm) {
        return new Promise((resolve) => {
            const overlay = document.createElement('div');
            overlay.className = 'tg-modal-overlay';
            overlay.innerHTML = `
                <div class="tg-modal">
                    <div class="tg-modal-icon">
                        <i class="fa-solid fa-triangle-exclamation"></i>
                    </div>
                    <h3 class="tg-modal-title">Confirmar Ação</h3>
                    <p class="tg-modal-msg">${message}</p>
                    <div class="tg-modal-actions">
                        <button class="tg-modal-btn tg-modal-cancel">Cancelar</button>
                        <button class="tg-modal-btn tg-modal-confirm">Confirmar</button>
                    </div>
                </div>
            `;

            document.body.appendChild(overlay);
            requestAnimationFrame(() => overlay.classList.add('show'));

            const close = (result) => {
                overlay.classList.remove('show');
                setTimeout(() => overlay.remove(), 300);
                resolve(result);
            };

            overlay.querySelector('.tg-modal-cancel').addEventListener('click', () => close(false));
            overlay.querySelector('.tg-modal-confirm').addEventListener('click', () => {
                close(true);
                if (typeof onConfirm === 'function') onConfirm();
            });

            overlay.addEventListener('click', (e) => {
                if (e.target === overlay) close(false);
            });
        });
    },

    /**
     * Formata número para BR
     */
    formatNumber(num) {
        return new Intl.NumberFormat('pt-BR').format(num);
    },

    /**
     * Inicialização global
     */
    init() {
        // CSRF token para fetch
        const csrfMeta = document.querySelector('meta[name="csrf-token"]');
        if (csrfMeta) {
            this.csrfToken = csrfMeta.content;
        }

        // Confirmação estilizada em forms com data-confirm
        document.querySelectorAll('[data-confirm]').forEach(el => {
            el.addEventListener('submit', (e) => {
                e.preventDefault();
                App.confirm(el.dataset.confirm).then(confirmed => {
                    if (confirmed) {
                        // Temporarily remove the listener to allow submission
                        el.removeAttribute('data-confirm');
                        el.submit();
                    }
                });
            });
        });

        // Flash messages from server
        const flash = document.querySelector('[data-flash]');
        if (flash) {
            const type = flash.dataset.flashType || 'info';
            App.toast(flash.dataset.flash, type);
            flash.remove();
        }
    }
};

document.addEventListener('DOMContentLoaded', () => App.init());
