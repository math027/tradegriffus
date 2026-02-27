/**
 * TradeGriffus — Offline UI Components
 * Barra de status offline, badge de pendentes, indicadores visuais, FAB de sync
 */

const OfflineUI = {
    /**
     * Inicializa os componentes de UI offline
     */
    init() {
        this._createOfflineBar();
        this._createPendingBadge();
        this._createSyncFAB();
        this.updatePendingBadge();

        // Escuta mudanças na fila
        window.addEventListener('offlineQueueChanged', () => {
            this.updatePendingBadge();
        });

        // Estado inicial
        if (!navigator.onLine) {
            this.showOfflineBar();
        }
    },

    /**
     * Cria a barra de status offline (topo da tela)
     */
    _createOfflineBar() {
        if (document.getElementById('griffus-offline-bar')) return;

        const bar = document.createElement('div');
        bar.id = 'griffus-offline-bar';
        bar.innerHTML = `
            <div class="offline-bar-content">
                <i class="fa-solid fa-wifi-slash"></i>
                <span>Sem conexão — Seus dados serão salvos localmente</span>
            </div>
        `;
        document.body.prepend(bar);
    },

    /**
     * Cria o badge de ações pendentes no sidebar
     */
    _createPendingBadge() {
        const sidebarNav = document.querySelector('.sidebar-nav');
        if (sidebarNav && !document.getElementById('griffus-pending-indicator')) {
            const indicator = document.createElement('div');
            indicator.id = 'griffus-pending-indicator';
            indicator.className = 'pending-sync-indicator';
            indicator.style.display = 'none';
            indicator.innerHTML = `
                <i class="fa-solid fa-cloud-arrow-up"></i>
                <span class="pending-sync-text">
                    <strong id="griffus-pending-count">0</strong> pendente(s)
                </span>
            `;
            sidebarNav.prepend(indicator);
        }
    },

    /**
     * Cria o FAB (Floating Action Button) de sincronização no canto inferior direito
     */
    _createSyncFAB() {
        if (document.getElementById('griffus-sync-fab')) return;

        const fab = document.createElement('div');
        fab.id = 'griffus-sync-fab';
        fab.className = 'sync-fab';
        fab.innerHTML = `
            <div class="sync-fab-badge" id="sync-fab-badge">0</div>
            <i class="fa-solid fa-cloud-arrow-up" id="sync-fab-icon"></i>
        `;
        fab.style.display = 'none';
        document.body.appendChild(fab);

        // Click to trigger manual sync
        fab.addEventListener('click', () => {
            if (navigator.onLine && typeof OfflineSync !== 'undefined') {
                this.showSyncing();
                OfflineSync.sync();
            } else {
                App.toast('📱 Aguardando conexão para sincronizar...', 'info', 3000);
            }
        });
    },

    /**
     * Mostra a barra "Sem Conexão"
     */
    showOfflineBar() {
        const bar = document.getElementById('griffus-offline-bar');
        if (bar) {
            bar.classList.add('show');
            document.body.classList.add('has-offline-bar');
        }
    },

    /**
     * Esconde a barra "Sem Conexão" e mostra "De Volta Online"
     */
    showOnlineBar() {
        const bar = document.getElementById('griffus-offline-bar');
        if (bar) {
            bar.classList.remove('show');
            document.body.classList.remove('has-offline-bar');
        }
        App.toast('🌐 Conexão restabelecida!', 'success', 3000);
    },

    /**
     * Atualiza badge de ações pendentes + FAB
     */
    async updatePendingBadge() {
        try {
            const count = await OfflineDB.countPending();

            // Sidebar badge
            const indicator = document.getElementById('griffus-pending-indicator');
            const countEl = document.getElementById('griffus-pending-count');
            if (indicator && countEl) {
                if (count > 0) {
                    countEl.textContent = count;
                    indicator.style.display = 'flex';
                } else {
                    indicator.style.display = 'none';
                }
            }

            // FAB
            const fab = document.getElementById('griffus-sync-fab');
            const fabBadge = document.getElementById('sync-fab-badge');
            const fabIcon = document.getElementById('sync-fab-icon');
            if (fab) {
                if (count > 0) {
                    fabBadge.textContent = count;
                    fab.style.display = 'flex';
                    fab.classList.remove('syncing', 'synced');
                    fabIcon.className = 'fa-solid fa-cloud-arrow-up';
                } else {
                    fab.style.display = 'none';
                }
            }
        } catch (err) {
            // IndexedDB pode não estar disponível
        }
    },

    /**
     * Mostra estado "sincronizando" no FAB
     */
    showSyncing() {
        const fab = document.getElementById('griffus-sync-fab');
        const fabIcon = document.getElementById('sync-fab-icon');
        if (fab) {
            fab.classList.add('syncing');
            fab.classList.remove('synced');
            fabIcon.className = 'fa-solid fa-rotate fa-spin';
        }
    },

    /**
     * Mostra estado "sincronizado" no FAB (temporário)
     */
    showSynced() {
        const fab = document.getElementById('griffus-sync-fab');
        const fabIcon = document.getElementById('sync-fab-icon');
        if (fab) {
            fab.classList.remove('syncing');
            fab.classList.add('synced');
            fabIcon.className = 'fa-solid fa-check';

            setTimeout(() => {
                this.updatePendingBadge();
            }, 2500);
        }
    },

    /**
     * Mostra feedback "Salvo Localmente" após enfileirar ação offline
     */
    showSavedLocally(tipo) {
        const labels = {
            checkin: 'Check-in salvo localmente',
            checkout: 'Checkout salvo localmente',
            ponto: 'Ponto registrado localmente',
            foto: 'Foto salva localmente',
            observacao: 'Observação salva localmente',
            pesquisa: 'Pesquisa salva localmente',
        };
        const msg = labels[tipo] || 'Ação salva localmente';
        App.toast(`📱 ${msg}. Será sincronizado quando voltar a ter internet.`, 'info', 5000);
    },
};

// Inicializa ao carregar
document.addEventListener('DOMContentLoaded', () => {
    OfflineUI.init();
});
