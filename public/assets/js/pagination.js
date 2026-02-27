/**
 * Paginação client-side reutilizável para tabelas e listas de cards
 * 
 * Uso em tabelas:
 *   <table id="myTable" data-paginate="true" data-per-page="10">
 *
 * Uso com lista de cards (ex: .resposta-card):
 *   <div id="myList" data-paginate="true" data-per-page="10" data-paginate-selector=".resposta-card">
 *
 * O componente insere automaticamente o seletor de itens por página e controles de navegação.
 * Compatível com filtro de busca: chame TablePagination.refresh('tableId') após filtrar.
 */
const TablePagination = (() => {
    const instances = {};

    function init(containerId, options = {}) {
        const container = document.getElementById(containerId);
        if (!container) return;

        const isTable = container.tagName === 'TABLE';
        const selector = container.dataset.paginateSelector || null;
        const perPage = parseInt(container.dataset.perPage || options.perPage || 10);

        const instance = {
            containerId,
            container,
            isTable,
            selector,
            currentPage: 1,
            perPage: perPage,
            controlsId: containerId + '_pgControls',
        };

        instances[containerId] = instance;

        // Inserir controles
        createControls(instance);
        applyPagination(instance);
    }

    function getItems(instance) {
        if (instance.isTable) {
            return Array.from(instance.container.querySelectorAll('tbody tr'));
        }
        if (instance.selector) {
            return Array.from(instance.container.querySelectorAll(instance.selector));
        }
        return Array.from(instance.container.children);
    }

    function getVisibleItems(instance) {
        return getItems(instance).filter(item => {
            // Respeitar filtros de busca existentes (display:none por JS)
            return item.dataset.pgHidden !== 'true';
        });
    }

    function createControls(instance) {
        // Remover controles antigos se existirem
        const existing = document.getElementById(instance.controlsId);
        if (existing) existing.remove();

        const controlsDiv = document.createElement('div');
        controlsDiv.id = instance.controlsId;
        controlsDiv.className = 'pg-controls';
        controlsDiv.innerHTML = `
            <div class="pg-left">
                <span class="pg-info" id="${instance.controlsId}_info"></span>
            </div>
            <div class="pg-center">
                <button class="pg-btn pg-prev" title="Anterior" onclick="TablePagination.prev('${instance.containerId}')">
                    <i class="fa-solid fa-chevron-left"></i>
                </button>
                <span class="pg-pages" id="${instance.controlsId}_pages"></span>
                <button class="pg-btn pg-next" title="Próxima" onclick="TablePagination.next('${instance.containerId}')">
                    <i class="fa-solid fa-chevron-right"></i>
                </button>
            </div>
            <div class="pg-right">
                <label class="pg-per-page-label">
                    Exibir
                    <select class="pg-per-page" onchange="TablePagination.changePerPage('${instance.containerId}', this.value)">
                        <option value="10" ${instance.perPage === 10 ? 'selected' : ''}>10</option>
                        <option value="25" ${instance.perPage === 25 ? 'selected' : ''}>25</option>
                        <option value="50" ${instance.perPage === 50 ? 'selected' : ''}>50</option>
                        <option value="100" ${instance.perPage === 100 ? 'selected' : ''}>100</option>
                    </select>
                    por página
                </label>
            </div>
        `;

        // Inserir após o container (ou após .table-container / .table-responsive pai)
        const parent = instance.container.closest('.table-container, .table-responsive') || instance.container;
        parent.parentNode.insertBefore(controlsDiv, parent.nextSibling);
    }

    function applyPagination(instance) {
        const allItems = getItems(instance);
        const visibleItems = allItems.filter(item => {
            // Itens escondidos por filtro de busca mantê-los escondidos
            const hiddenBySearch = item.style.display === 'none' && item.dataset.pgHidden !== 'true';
            if (hiddenBySearch) {
                item.dataset.pgSearchHidden = 'true';
            }
            return item.dataset.pgSearchHidden !== 'true';
        });

        const totalItems = visibleItems.length;
        const totalPages = Math.max(1, Math.ceil(totalItems / instance.perPage));

        if (instance.currentPage > totalPages) {
            instance.currentPage = totalPages;
        }

        const start = (instance.currentPage - 1) * instance.perPage;
        const end = start + instance.perPage;

        // Esconder todos, depois mostrar apenas os da p\u00e1gina
        allItems.forEach(item => {
            if (item.dataset.pgSearchHidden === 'true') {
                item.style.display = 'none';
                return;
            }
        });

        visibleItems.forEach((item, idx) => {
            if (idx >= start && idx < end) {
                item.style.display = '';
                item.dataset.pgHidden = 'false';
            } else {
                item.style.display = 'none';
                item.dataset.pgHidden = 'true';
            }
        });

        // Atualizar info
        const infoEl = document.getElementById(instance.controlsId + '_info');
        if (infoEl) {
            if (totalItems === 0) {
                infoEl.textContent = 'Nenhum registro';
            } else {
                infoEl.textContent = `Exibindo ${start + 1} a ${Math.min(end, totalItems)} de ${totalItems}`;
            }
        }

        // Atualizar botões de página
        const pagesEl = document.getElementById(instance.controlsId + '_pages');
        if (pagesEl) {
            let html = '';
            const maxVisible = 5;
            let startPage = Math.max(1, instance.currentPage - Math.floor(maxVisible / 2));
            let endPage = Math.min(totalPages, startPage + maxVisible - 1);
            if (endPage - startPage < maxVisible - 1) {
                startPage = Math.max(1, endPage - maxVisible + 1);
            }

            if (startPage > 1) {
                html += `<button class="pg-btn pg-num" onclick="TablePagination.goTo('${instance.containerId}', 1)">1</button>`;
                if (startPage > 2) html += `<span class="pg-ellipsis">...</span>`;
            }

            for (let i = startPage; i <= endPage; i++) {
                html += `<button class="pg-btn pg-num ${i === instance.currentPage ? 'active' : ''}" onclick="TablePagination.goTo('${instance.containerId}', ${i})">${i}</button>`;
            }

            if (endPage < totalPages) {
                if (endPage < totalPages - 1) html += `<span class="pg-ellipsis">...</span>`;
                html += `<button class="pg-btn pg-num" onclick="TablePagination.goTo('${instance.containerId}', ${totalPages})">${totalPages}</button>`;
            }

            pagesEl.innerHTML = html;
        }

        // Disable/enable prev/next
        const controls = document.getElementById(instance.controlsId);
        if (controls) {
            const prevBtn = controls.querySelector('.pg-prev');
            const nextBtn = controls.querySelector('.pg-next');
            if (prevBtn) prevBtn.disabled = instance.currentPage <= 1;
            if (nextBtn) nextBtn.disabled = instance.currentPage >= totalPages;
        }

        // Esconder controles se poucos itens
        const controlsEl = document.getElementById(instance.controlsId);
        if (controlsEl) {
            controlsEl.style.display = totalItems <= 0 ? 'none' : '';
        }
    }

    function goTo(containerId, page) {
        const instance = instances[containerId];
        if (!instance) return;
        instance.currentPage = page;
        applyPagination(instance);
    }

    function prev(containerId) {
        const instance = instances[containerId];
        if (!instance || instance.currentPage <= 1) return;
        instance.currentPage--;
        applyPagination(instance);
    }

    function next(containerId) {
        const instance = instances[containerId];
        if (!instance) return;
        instance.currentPage++;
        applyPagination(instance);
    }

    function changePerPage(containerId, value) {
        const instance = instances[containerId];
        if (!instance) return;
        instance.perPage = parseInt(value);
        instance.currentPage = 1;
        applyPagination(instance);
    }

    /**
     * Chamar após um filtro de busca externo para reprocessar a paginação
     */
    function refresh(containerId) {
        const instance = instances[containerId];
        if (!instance) return;

        const allItems = getItems(instance);
        // Resetar flags de paginação e detectar quais estão filtrados
        allItems.forEach(item => {
            delete item.dataset.pgHidden;
            // Detectar se o item está escondido por busca
            if (item.style.display === 'none') {
                item.dataset.pgSearchHidden = 'true';
            } else {
                delete item.dataset.pgSearchHidden;
            }
        });

        instance.currentPage = 1;
        applyPagination(instance);
    }

    // Auto-init ao carregar
    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('[data-paginate="true"]').forEach(el => {
            if (el.id) init(el.id);
        });
    });

    return { init, goTo, prev, next, changePerPage, refresh };
})();
