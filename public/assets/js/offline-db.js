/**
 * TradeGriffus — IndexedDB Offline Queue
 * Gerencia a fila de ações offline (check-in, checkout, ponto, fotos, observações)
 */

const OfflineDB = {
    DB_NAME: 'griffus_offline',
    DB_VERSION: 1,
    STORE_NAME: 'sync_queue',
    _db: null,

    /**
     * Abre/cria o banco IndexedDB
     */
    async open() {
        if (this._db) return this._db;

        return new Promise((resolve, reject) => {
            const req = indexedDB.open(this.DB_NAME, this.DB_VERSION);

            req.onupgradeneeded = (e) => {
                const db = e.target.result;
                if (!db.objectStoreNames.contains(this.STORE_NAME)) {
                    const store = db.createObjectStore(this.STORE_NAME, {
                        keyPath: 'id',
                        autoIncrement: true,
                    });
                    store.createIndex('tipo', 'tipo', { unique: false });
                    store.createIndex('timestamp', 'timestamp', { unique: false });
                }
            };

            req.onsuccess = (e) => {
                this._db = e.target.result;
                resolve(this._db);
            };

            req.onerror = (e) => {
                console.error('[OfflineDB] Erro ao abrir:', e.target.error);
                reject(e.target.error);
            };
        });
    },

    /**
     * Salva uma ação na fila offline
     * @param {Object} action - { tipo, url, method, fields, files, meta }
     *   - tipo: 'checkin' | 'checkout' | 'ponto' | 'foto' | 'observacao'
     *   - url: endpoint de destino
     *   - method: 'POST' (default)
     *   - fields: {} campos de texto/hidden
     *   - files: [{ fieldName, fileName, buffer, type }] — blobs convertidos em ArrayBuffer
     *   - meta: {} dados extras para exibição (ex: pdv_nome, hora)
     */
    async saveAction(action) {
        const db = await this.open();
        return new Promise((resolve, reject) => {
            const tx = db.transaction(this.STORE_NAME, 'readwrite');
            const store = tx.objectStore(this.STORE_NAME);

            const record = {
                ...action,
                timestamp: new Date().toISOString(),
                synced: false,
            };

            const req = store.add(record);
            req.onsuccess = () => {
                resolve(req.result); // id
                // Dispara evento customizado para atualizar UI
                window.dispatchEvent(new CustomEvent('offlineQueueChanged'));
            };
            req.onerror = () => reject(req.error);
        });
    },

    /**
     * Retorna todas as ações pendentes (em ordem cronológica)
     */
    async getPendingActions() {
        const db = await this.open();
        return new Promise((resolve, reject) => {
            const tx = db.transaction(this.STORE_NAME, 'readonly');
            const store = tx.objectStore(this.STORE_NAME);
            const req = store.getAll();
            req.onsuccess = () => resolve(req.result.filter(a => !a.synced));
            req.onerror = () => reject(req.error);
        });
    },

    /**
     * Remove uma ação da fila (após sync bem-sucedido)
     */
    async removeAction(id) {
        const db = await this.open();
        return new Promise((resolve, reject) => {
            const tx = db.transaction(this.STORE_NAME, 'readwrite');
            const store = tx.objectStore(this.STORE_NAME);
            const req = store.delete(id);
            req.onsuccess = () => {
                resolve();
                window.dispatchEvent(new CustomEvent('offlineQueueChanged'));
            };
            req.onerror = () => reject(req.error);
        });
    },

    /**
     * Conta ações pendentes
     */
    async countPending() {
        const db = await this.open();
        return new Promise((resolve, reject) => {
            const tx = db.transaction(this.STORE_NAME, 'readonly');
            const store = tx.objectStore(this.STORE_NAME);
            const req = store.count();
            req.onsuccess = () => resolve(req.result);
            req.onerror = () => reject(req.error);
        });
    },

    /**
     * Limpa toda a fila
     */
    async clearAll() {
        const db = await this.open();
        return new Promise((resolve, reject) => {
            const tx = db.transaction(this.STORE_NAME, 'readwrite');
            const store = tx.objectStore(this.STORE_NAME);
            const req = store.clear();
            req.onsuccess = () => {
                resolve();
                window.dispatchEvent(new CustomEvent('offlineQueueChanged'));
            };
            req.onerror = () => reject(req.error);
        });
    },

    /**
     * Converte um File/Blob em ArrayBuffer para armazenar no IndexedDB
     */
    async fileToStorable(file, fieldName) {
        return new Promise((resolve, reject) => {
            const reader = new FileReader();
            reader.onload = () => {
                resolve({
                    fieldName: fieldName,
                    fileName: file.name || 'arquivo.webp',
                    buffer: reader.result,   // ArrayBuffer
                    type: file.type || 'image/webp',
                });
            };
            reader.onerror = () => reject(reader.error);
            reader.readAsArrayBuffer(file);
        });
    },

    /**
     * Enfileira um formulário completo para envio offline
     * Extrai campos e arquivos do FormData e salva no IndexedDB
     */
    async enqueueForm(tipo, url, formElement, meta = {}) {
        const formData = new FormData(formElement);
        const fields = {};
        const files = [];

        for (const [key, value] of formData.entries()) {
            if (value instanceof File && value.size > 0) {
                const storable = await this.fileToStorable(value, key);
                files.push(storable);
            } else if (!(value instanceof File)) {
                fields[key] = value;
            }
        }

        return this.saveAction({
            tipo,
            url,
            method: 'POST',
            fields,
            files,
            meta,
        });
    },
};

// Inicializa o DB ao carregar
document.addEventListener('DOMContentLoaded', () => {
    OfflineDB.open().catch(() => { });
});
