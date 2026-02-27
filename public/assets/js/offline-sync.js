/**
 * TradeGriffus — Offline Sync Engine
 * Sincroniza a fila de ações offline quando volta a ter internet
 */

const OfflineSync = {
    _syncing: false,
    _retryTimer: null,

    /**
     * Inicializa listeners de conectividade e mensagens do SW
     */
    init() {
        // Quando volta online, sincroniza
        window.addEventListener('online', () => {
            OfflineUI.showOnlineBar();
            this.sync();
        });

        // Quando fica offline
        window.addEventListener('offline', () => {
            OfflineUI.showOfflineBar();
        });

        // Mensagens do Service Worker
        if (navigator.serviceWorker) {
            navigator.serviceWorker.addEventListener('message', (e) => {
                if (e.data.type === 'SYNC_COMPLETE') {
                    this._onActionSynced(e.data);
                }
                if (e.data.type === 'SYNC_DONE') {
                    this._onSyncDone();
                }
            });
        }

        // Tenta sync ao carregar (pode ter ações pendentes de antes)
        if (navigator.onLine) {
            setTimeout(() => this.sync(), 2000);
        }
    },

    /**
     * Sincroniza todas as ações pendentes
     */
    async sync() {
        if (this._syncing || !navigator.onLine) return;
        this._syncing = true;

        try {
            // Tenta Background Sync API primeiro (mais confiável)
            if ('serviceWorker' in navigator && 'SyncManager' in window) {
                const reg = await navigator.serviceWorker.ready;
                await reg.sync.register('griffus-offline-sync');
                return; // SW cuida do resto
            }

            // Fallback: sync manual no main thread
            await this._manualSync();
        } catch (err) {
            console.warn('[Sync] Erro ao iniciar sync:', err);
            // Fallback para sync manual
            await this._manualSync();
        } finally {
            this._syncing = false;
        }
    },

    /**
     * Sync manual (quando Background Sync não está disponível)
     */
    async _manualSync() {
        const actions = await OfflineDB.getPendingActions();
        if (actions.length === 0) return;

        const total = actions.length;
        let synced = 0;

        App.toast(`Sincronizando ${total} ação(ões) pendente(s)...`, 'info', 3000);
        OfflineUI.showSyncing();

        for (const action of actions) {
            try {
                const formData = this._rebuildFormData(action);
                const response = await fetch(action.url, {
                    method: action.method || 'POST',
                    body: formData,
                    credentials: 'same-origin',
                    redirect: 'follow',
                });

                if (response.ok || response.status === 302 || response.redirected) {
                    await OfflineDB.removeAction(action.id);
                    synced++;
                } else {
                    console.warn(`[Sync] Falha HTTP ${response.status} para:`, action.url);
                }
            } catch (err) {
                console.warn('[Sync] Falha na ação:', action.id, err);
                // Para de tentar se sem internet
                if (!navigator.onLine) break;
            }
        }

        if (synced > 0) {
            App.toast(`✅ ${synced} ação(ões) sincronizada(s) com sucesso!`, 'success', 5000);
            OfflineUI.showSynced();
        }

        if (synced < total) {
            // Retry em 30s para as que falharam
            this._retryTimer = setTimeout(() => this.sync(), 30000);
        }

        OfflineUI.updatePendingBadge();
    },

    /**
     * Reconstrói FormData a partir dos dados salvos no IndexedDB
     */
    _rebuildFormData(action) {
        const fd = new FormData();

        // Campos de texto/hidden
        if (action.fields) {
            for (const [key, value] of Object.entries(action.fields)) {
                fd.append(key, value);
            }
        }

        // Arquivos (blobs armazenados como ArrayBuffer)
        if (action.files) {
            for (const fileData of action.files) {
                const blob = new Blob([fileData.buffer], { type: fileData.type });
                fd.append(fileData.fieldName, blob, fileData.fileName);
            }
        }

        return fd;
    },

    /**
     * Callback quando uma ação é sincronizada via SW
     */
    _onActionSynced(data) {
        const labels = {
            checkin: 'Check-in',
            checkout: 'Checkout',
            ponto: 'Ponto',
            foto: 'Foto',
            observacao: 'Observação',
            pesquisa: 'Pesquisa',
        };
        const label = labels[data.action] || 'Ação';
        App.toast(`✅ ${label} sincronizado com sucesso!`, 'success');
    },

    /**
     * Callback quando toda a fila foi processada pelo SW
     */
    _onSyncDone() {
        this._syncing = false;
        OfflineUI.updatePendingBadge();
    },

    /**
     * Verifica se está offline e enfileira form se necessário
     * Retorna true se enfileirou (offline), false se deve enviar normalmente
     */
    async interceptIfOffline(tipo, url, formElement, meta = {}) {
        if (navigator.onLine) return false;

        try {
            await OfflineDB.enqueueForm(tipo, url, formElement, meta);
            OfflineUI.showSavedLocally(tipo);
            OfflineUI.updatePendingBadge();
            return true;
        } catch (err) {
            console.error('[Sync] Erro ao enfileirar:', err);
            App.toast('Erro ao salvar offline. Tente novamente.', 'danger');
            return false;
        }
    },
};

// Inicializa ao carregar
document.addEventListener('DOMContentLoaded', () => {
    OfflineSync.init();
});
