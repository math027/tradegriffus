document.addEventListener('DOMContentLoaded', function() {
    
    // =================================================================
    // 0. Dados Simulados (Coordenadas para o Mapa)
    // =================================================================
    // Como não temos backend, mapeamos os nomes do HTML para coordenadas fixas em SP
    const pdvCoordinates = {
        'Carrefour Limão': { lat: -23.5100, lng: -46.6600 },
        'Extra Freguesia': { lat: -23.4950, lng: -46.6900 },
        'Pão de Açúcar':   { lat: -23.5600, lng: -46.6500 },
        'Walmart Morumbi': { lat: -23.6000, lng: -46.7000 },
        'Sonda Super':     { lat: -23.5500, lng: -46.6300 },
        // Ponto padrão caso adicione um novo sem cadastro
        'default':         { lat: -23.5505, lng: -46.6333 } 
    };

    // =================================================================
    // 1. Configuração de Modais (Pesquisa e Mapa)
    // =================================================================
    
    // --- Modal de Pesquisa ---
    const availableSurveys = [
        { id: 1, name: 'Pesquisa de Preço' }, { id: 2, name: 'Ruptura Visual' },
        { id: 3, name: 'Share de Gôndola' },  { id: 4, name: 'Ponto Extra' },
        { id: 5, name: 'Concorrentes' },      { id: 6, name: 'Validade' }
    ];

    const surveyModal = document.getElementById('surveyModal');
    const surveyList = document.getElementById('surveyList');
    const btnCloseSurvey = document.getElementById('closeModalBtn');
    const btnCancelSurvey = document.getElementById('cancelModalBtn');
    const btnConfirmSurvey = document.getElementById('confirmModalBtn');
    let activeFooterContainer = null;

    // --- Modal de Mapa ---
    const mapModal = document.getElementById('mapModal');
    const btnCloseMap = document.getElementById('closeMapBtn');
    let mapInstance = null; // Variável para guardar a instância do Leaflet
    let routeLayer = null;  // Camada da linha da rota
    let markersLayer = [];  // Array de marcadores

    // --- Funções de Pesquisa ---
    function renderSurveyOptions() {
        surveyList.innerHTML = '';
        availableSurveys.forEach(survey => {
            const div = document.createElement('div');
            div.className = 'checkbox-item';
            div.innerHTML = `
                <input type="checkbox" id="survey-${survey.id}" value="${survey.name}">
                <label for="survey-${survey.id}">${survey.name}</label>
            `;
            surveyList.appendChild(div);
        });
    }
    renderSurveyOptions();

    function openSurveyModal(targetContainer) {
        activeFooterContainer = targetContainer;
        surveyList.querySelectorAll('input').forEach(cb => cb.checked = false);
        surveyModal.classList.remove('hidden');
    }

    function createSurveyItem(container, name) {
        const surveyItem = document.createElement('div');
        surveyItem.className = 'survey-item';
        surveyItem.innerHTML = `<span><i class="fa-solid fa-clipboard-list"></i> ${name}</span><i class="fa-solid fa-times delete-survey"></i>`;
        const addBtn = container.querySelector('.btn-dashed-full');
        container.insertBefore(surveyItem, addBtn);
        surveyItem.querySelector('.delete-survey').addEventListener('click', () => surveyItem.remove());
    }

    // Listeners Pesquisa
    if(btnCloseSurvey) btnCloseSurvey.addEventListener('click', () => surveyModal.classList.add('hidden'));
    if(btnCancelSurvey) btnCancelSurvey.addEventListener('click', () => surveyModal.classList.add('hidden'));
    
    if (btnConfirmSurvey) {
        btnConfirmSurvey.addEventListener('click', function() {
            if (!activeFooterContainer) return;
            const selected = surveyList.querySelectorAll('input:checked');
            selected.forEach(cb => createSurveyItem(activeFooterContainer, cb.value));
            surveyModal.classList.add('hidden');
        });
    }

    const openSurveyButtons = document.querySelectorAll('.btn-dashed-full');
    openSurveyButtons.forEach(btn => btn.addEventListener('click', function() { openSurveyModal(this.parentNode); }));

    // --- Funções do Mapa (LEAFLET) ---

    function initMap() {
        // Se o mapa já existe, não recria
        if (mapInstance) return;
        
        // Inicializa mapa em SP
        mapInstance = L.map('leafletMap').setView([-23.5505, -46.6333], 12);
        
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(mapInstance);
    }

    function openMapModal(dayColumn) {
        mapModal.classList.remove('hidden');
        
        // Pequeno delay para garantir que o modal está visível antes de renderizar o mapa
        // (Isso corrige o bug do Leaflet carregar cinza em div oculta)
        setTimeout(() => {
            initMap();
            mapInstance.invalidateSize(); // Recalcula tamanho
            plotRoute(dayColumn);
        }, 100);
    }

    function plotRoute(dayColumn) {
        // 1. Limpar mapa anterior
        if (routeLayer) mapInstance.removeLayer(routeLayer);
        markersLayer.forEach(marker => mapInstance.removeLayer(marker));
        markersLayer = [];

        // 2. Pegar os cards DAQUELA coluna na ordem do DOM
        const cards = dayColumn.querySelectorAll('.kanban-cards .task-card');
        
        if (cards.length === 0) {
            // Se não tem cards, reseta a visão
            mapInstance.setView([-23.5505, -46.6333], 12);
            return;
        }

        const routePoints = [];

        // 3. Iterar cards e buscar coordenadas
        cards.forEach((card, index) => {
            const name = card.querySelector('strong').innerText.trim();
            
            // Busca no "banco" ou usa default com um pequeno random para não sobrepor se for desconhecido
            let coords = pdvCoordinates[name];
            if (!coords) {
                // Gera coordenada aleatória perto do centro se não existir
                coords = { 
                    lat: pdvCoordinates['default'].lat + (Math.random() - 0.5) * 0.05,
                    lng: pdvCoordinates['default'].lng + (Math.random() - 0.5) * 0.05
                };
            }

            const latLng = [coords.lat, coords.lng];
            routePoints.push(latLng);

            // Adiciona Marcador
            const marker = L.marker(latLng)
                .bindPopup(`<strong>${index + 1}. ${name}</strong>`)
                .addTo(mapInstance);
            
            markersLayer.push(marker);
        });

        // 4. Traçar a linha (Rota)
        if (routePoints.length > 1) {
            routeLayer = L.polyline(routePoints, { color: 'blue', weight: 4, opacity: 0.7, dashArray: '10, 10' }).addTo(mapInstance);
            // Ajusta zoom para caber tudo
            mapInstance.fitBounds(routeLayer.getBounds(), { padding: [50, 50] });
        } else if (routePoints.length === 1) {
            mapInstance.setView(routePoints[0], 14);
        }
    }

    if(btnCloseMap) btnCloseMap.addEventListener('click', () => mapModal.classList.add('hidden'));

    // =================================================================
    // 2. Drag and Drop e UI Cards
    // =================================================================

    const draggables = document.querySelectorAll('.task-card');
    const dropzones = document.querySelectorAll('.kanban-dropzone');
    let isDraggingFromBacklog = false;

    function addRemoveButton(card) {
        if (card.querySelector('.btn-remove-card')) return;
        const removeBtn = document.createElement('button');
        removeBtn.className = 'btn-remove-card';
        removeBtn.innerHTML = '<i class="fa-solid fa-times"></i>';
        removeBtn.title = "Remover PDV";
        removeBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            card.remove();
        });
        card.appendChild(removeBtn);
    }

    function addDragListeners(card) {
        card.setAttribute('draggable', 'true');
        card.addEventListener('dragstart', () => {
            isDraggingFromBacklog = card.closest('.backlog-column') !== null;
            card.classList.add('dragging');
        });
        card.addEventListener('dragend', () => {
            card.classList.remove('dragging');
            isDraggingFromBacklog = false;
            dropzones.forEach(zone => zone.classList.remove('drag-over'));
        });
    }

    draggables.forEach(card => addDragListeners(card));

    dropzones.forEach(zone => {
        zone.addEventListener('dragover', e => {
            e.preventDefault(); 
            zone.classList.add('drag-over');
            if (!isDraggingFromBacklog) {
                const after = getDragAfterElement(zone, e.clientY);
                const draggable = document.querySelector('.dragging');
                if (draggable) zone.insertBefore(draggable, after || null);
            }
        });

        zone.addEventListener('dragleave', () => zone.classList.remove('drag-over'));

        zone.addEventListener('drop', e => {
            e.preventDefault();
            zone.classList.remove('drag-over');
            const draggable = document.querySelector('.dragging');
            if (!draggable) return;

            // Validação
            const name = draggable.querySelector('strong').innerText.trim();
            const exists = [...zone.querySelectorAll('.task-card')].some(c => c !== draggable && c.querySelector('strong').innerText.trim() === name);
            if (exists) { alert('PDV já adicionado neste dia!'); return; }

            const after = getDragAfterElement(zone, e.clientY);
            
            if (isDraggingFromBacklog) {
                const clone = draggable.cloneNode(true);
                clone.classList.remove('dragging');
                addDragListeners(clone);
                addRemoveButton(clone);
                zone.insertBefore(clone, after || null);
            } else {
                addRemoveButton(draggable);
                zone.insertBefore(draggable, after || null);
            }
        });
    });

    function getDragAfterElement(container, y) {
        const els = [...container.querySelectorAll('.task-card:not(.dragging)')];
        return els.reduce((closest, child) => {
            const box = child.getBoundingClientRect();
            const offset = y - box.top - box.height / 2;
            return (offset < 0 && offset > closest.offset) ? { offset: offset, element: child } : closest;
        }, { offset: Number.NEGATIVE_INFINITY }).element;
    }

    // =================================================================
    // 3. UI: Sidebar, Datas e Header dos Dias (Com Botão Mapa)
    // =================================================================
    
    // Sidebar
    const toggleBtn = document.querySelector('.btn-toggle-promoters');
    const routesContainer = document.getElementById('routesContainer');
    if (toggleBtn) {
        toggleBtn.addEventListener('click', () => {
            routesContainer.classList.toggle('promoters-hidden');
            toggleBtn.querySelector('i').className = routesContainer.classList.contains('promoters-hidden') ? 'fa-solid fa-user-slash' : 'fa-solid fa-users';
        });
    }

    // Datas
    let currentWeekStart = new Date(2025, 0, 26); 
    const weekLabel = document.querySelector('.date-navigation .current-week');
    const btnPrev = document.querySelector('.date-navigation .btn-icon:first-child');
    const btnNext = document.querySelector('.date-navigation .btn-icon:last-child');
    const dayHeaders = document.querySelectorAll('.kanban-column.day-column .column-header');

    function formatDate(date) {
        return date.toLocaleDateString('pt-BR', { day: '2-digit', month: '2-digit' });
    }

    function updateDateDisplay() {
        const weekEnd = new Date(currentWeekStart);
        weekEnd.setDate(currentWeekStart.getDate() + 4);
        if(weekLabel) weekLabel.textContent = `Semana (${formatDate(currentWeekStart)} a ${formatDate(weekEnd)})`;

        const weekDays = ['Seg', 'Ter', 'Qua', 'Qui', 'Sex'];
        
        dayHeaders.forEach((header, index) => {
            if (index < 5) {
                const currentDay = new Date(currentWeekStart);
                currentDay.setDate(currentWeekStart.getDate() + index);
                
                // Cria o texto da data
                const dateText = `${weekDays[index]} ${formatDate(currentDay)}`;
                
                // Limpa o header
                header.innerHTML = '';
                
                // Cria span para texto
                const span = document.createElement('span');
                span.textContent = dateText;
                header.appendChild(span);

                // --- CRIAÇÃO DO BOTÃO MAPA ---
                const mapBtn = document.createElement('button');
                mapBtn.className = 'btn-map-header';
                mapBtn.title = "Ver Rota no Mapa";
                mapBtn.innerHTML = '<i class="fa-solid fa-map-location-dot"></i>';
                
                // Ao clicar, descobre qual é a coluna pai inteira (.kanban-column)
                mapBtn.addEventListener('click', function() {
                    const dayColumn = header.closest('.kanban-column');
                    openMapModal(dayColumn);
                });

                header.appendChild(mapBtn);
            }
        });
    }

    if(btnPrev) btnPrev.addEventListener('click', () => { currentWeekStart.setDate(currentWeekStart.getDate() - 7); updateDateDisplay(); });
    if(btnNext) btnNext.addEventListener('click', () => { currentWeekStart.setDate(currentWeekStart.getDate() + 7); updateDateDisplay(); });

    updateDateDisplay();
});