document.addEventListener('DOMContentLoaded', function() {
    // --- CONFIGURAÇÕES ---
    const API_URL = '/api/users';
    let currentUserId = null; 
    let currentUserData = null;
    let usersList = [];
    let allPdvs = [];
    let currentStatusFilter = 'all';

    // Variáveis para o Dual List (Carteira)
    let availablePdvs = [];
    let userPortfolio = [];

    // --- SELETORES GERAIS ---
    const modalCollaborator = document.getElementById('modalCollaborator');
    const modalPortfolio = document.getElementById('modalPortfolio');
    const modalDelete = document.getElementById('modalDelete');
    const modalReset = document.getElementById('modalResetPassword');
    
    const tableBody = document.querySelector('tbody');
    const selectAllCheckbox = document.querySelector('thead .custom-checkbox');

    // Inputs Formulario
    const inputRole = document.getElementById('inputRole');
    // REMOVIDO SELETORES RCA

    // Cards (KPIs)
    const cardTotal = document.querySelector('.card.blue-border');
    const cardActive = document.querySelector('.card.green-border');
    const cardInactive = document.querySelector('.card.orange-border');

    // Botões
    const btnNew = document.querySelector('.btn-primary');
    const btnDeleteIcon = document.querySelector('.header-actions-group .fa-trash');
    const btnDeleteHeader = btnDeleteIcon ? btnDeleteIcon.closest('button') : null;
    
    const btnEditIcon = document.querySelector('.header-actions-group .fa-pencil');
    if(btnEditIcon) {
        const btnEditHeader = btnEditIcon.closest('button');
        if(btnEditHeader) btnEditHeader.remove(); 
    }

    const btnSaveData = document.getElementById('saveBtn');
    const btnSavePortfolio = document.getElementById('btnSavePortfolio');
    const btnConfirmDelete = document.getElementById('confirmDelete');
    const btnConfirmReset = document.getElementById('confirmResetBtn');
    const filterInput = document.getElementById('portfolioSearch');

    // --- INICIALIZAÇÃO ---
    
    if(selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function() {
            const isChecked = this.checked;
            const rowCheckboxes = document.querySelectorAll('tbody .custom-checkbox');
            rowCheckboxes.forEach(cb => cb.checked = isChecked);
            updateDeleteButtonState();
        });
    }

    // REMOVIDO LISTENER DE TOGGLE RCA

    // Filtros por Card
    cardTotal.addEventListener('click', () => applyCardFilter('all'));
    cardActive.addEventListener('click', () => applyCardFilter('ativo'));
    cardInactive.addEventListener('click', () => applyCardFilter('inativo'));
    [cardTotal, cardActive, cardInactive].forEach(c => c.style.cursor = 'pointer');

    document.addEventListener('click', (e) => {
        if(!e.target.closest('.action-cell')) {
            document.querySelectorAll('.action-menu').forEach(m => m.classList.remove('active'));
        }
    });

    init();

    async function init() {
        await Promise.all([loadPdvsFromDb(), loadCollaborators()]);
    }

    // --- LÓGICA DE FILTRO ---

    function applyCardFilter(status) {
        currentStatusFilter = status;
        
        // [cardTotal, cardActive, cardInactive].forEach(c => c.style.opacity = '0.5');
        
        if(status === 'all') cardTotal.style.opacity = '1';
        if(status === 'ativo') cardActive.style.opacity = '1';
        if(status === 'inativo') cardInactive.style.opacity = '1';

        renderTable(usersList);
    }

    // --- API: CARREGAMENTO ---

    async function loadCollaborators() {
        const token = localStorage.getItem('tradeToken');
        try {
            const response = await fetch(API_URL, { headers: { 'Authorization': `Bearer ${token}` } });
            
            if(!response.ok) throw new Error("Erro ao buscar dados");

            usersList = await response.json();
            
            applyCardFilter(currentStatusFilter);
            updateCards(usersList);

        } catch (error) {
            console.error(error);
            tableBody.innerHTML = `<tr><td colspan="7" style="text-align:center; color: red;">Erro ao carregar equipe.</td></tr>`;
        }
    }

    async function loadPdvsFromDb() {
        const token = localStorage.getItem('tradeToken');
        try {
            const response = await fetch(`${API_URL}/pdvs/all`, { headers: { 'Authorization': `Bearer ${token}` } });
            if(response.ok) {
                allPdvs = await response.json();
            }
        } catch (error) { console.error("Erro PDVs", error); }
    }

    function updateCards(users) {
        const activeCount = users.filter(u => u.status === 'ativo').length;
        const totalCount = users.length;
        const inactiveCount = totalCount - activeCount;

        document.getElementById('totalCollaborators').innerText = totalCount;
        document.getElementById('activeCollaborators').innerText = activeCount;
        document.getElementById('inactiveCollaborators').innerText = inactiveCount;
    }

    // --- AÇÕES: SALVAR ---

    async function saveCollaboratorData() {
        const token = localStorage.getItem('tradeToken');
        
        const name = document.getElementById('inputName').value;
        const email = document.getElementById('inputEmail').value;
        const phone = document.getElementById('inputPhone').value;
        const role = document.getElementById('inputRole').value;
        const password = document.getElementById('inputPassword').value;
        // REMOVIDO RCA

        if(!name || !email) return alert("Preencha Nome e Email.");
        if(!currentUserId && !password) return alert("Senha obrigatória para novos usuários.");

        const btn = btnSaveData;
        const originalText = btn.innerText;
        btn.innerText = "Salvando...";
        btn.disabled = true;

        try {
            const url = currentUserId ? `${API_URL}/${currentUserId}` : API_URL;
            const method = currentUserId ? 'PUT' : 'POST';

            const body = { name, email, phone, role };
            
            if(!currentUserId) {
                body.password = password;
                body.pdvs = []; 
            }

            const response = await fetch(url, {
                method: method,
                headers: { 'Content-Type': 'application/json', 'Authorization': `Bearer ${token}` },
                body: JSON.stringify(body)
            });

            if(response.ok) {
                alert("Dados salvos com sucesso!");
                modalCollaborator.classList.add('hidden');
                loadCollaborators();
            } else {
                const data = await response.json();
                alert(data.message || "Erro ao salvar.");
            }
        } catch(e) { alert("Erro de conexão."); }
        finally { btn.innerText = originalText; btn.disabled = false; }
    }

    async function savePortfolio() {
        const token = localStorage.getItem('tradeToken');
        const btn = btnSavePortfolio;
        btn.innerText = "Salvando...";
        btn.disabled = true;

        try {
            const pdvIds = userPortfolio.map(p => p.id);
            const body = {
                name: currentUserData.name,
                email: currentUserData.email,
                phone: currentUserData.phone,
                role: currentUserData.role,
                // REMOVIDO RCA
                pdvs: pdvIds 
            };

            const response = await fetch(`${API_URL}/${currentUserId}`, {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json', 'Authorization': `Bearer ${token}` },
                body: JSON.stringify(body)
            });

            if(response.ok) {
                alert("Carteira atualizada!");
                modalPortfolio.classList.add('hidden');
            } else {
                alert("Erro ao salvar carteira.");
            }
        } catch(e) { console.error(e); alert("Erro ao salvar."); }
        finally { btn.innerText = "Salvar Carteira"; btn.disabled = false; }
    }

    async function resetPassword() {
        const newPass = document.getElementById('newPasswordInput').value;
        if(!newPass) return alert("Digite a nova senha.");
        
        const token = localStorage.getItem('tradeToken');
        try {
            const response = await fetch(`${API_URL}/${currentUserId}/password`, {
                method: 'PATCH',
                headers: { 'Content-Type': 'application/json', 'Authorization': `Bearer ${token}` },
                body: JSON.stringify({ password: newPass })
            });

            if(response.ok) {
                alert("Senha redefinida!");
                modalReset.classList.add('hidden');
            } else {
                alert("Erro ao redefinir.");
            }
        } catch(e) { alert("Erro de conexão."); }
    }

    async function deleteCollaborator() {
        const token = localStorage.getItem('tradeToken');
        
        let idsToDelete = [];
        if (currentUserId) {
            idsToDelete.push(currentUserId);
        } else {
            const checked = document.querySelectorAll('tbody .custom-checkbox:checked');
            checked.forEach(cb => idsToDelete.push(cb.value));
        }

        if (idsToDelete.length === 0) return;

        try {
            for (const id of idsToDelete) {
                await fetch(`${API_URL}/${id}`, {
                    method: 'DELETE',
                    headers: { 'Authorization': `Bearer ${token}` }
                });
            }
            alert("Excluído com sucesso!");
            modalDelete.classList.add('hidden');
            loadCollaborators();
            
            currentUserId = null;
            updateDeleteButtonState();

        } catch(e) { alert("Erro ao excluir."); }
    }

    // --- UI: RENDERIZAÇÃO ---

    function renderTable(allUsers) {
        tableBody.innerHTML = '';
        
        let filteredUsers = allUsers;
        if (currentStatusFilter !== 'all') {
            filteredUsers = allUsers.filter(u => u.status === currentStatusFilter);
        }

        if(filteredUsers.length === 0) {
            tableBody.innerHTML = `<tr><td colspan="7" style="text-align:center;">Nenhum colaborador encontrado com status: ${currentStatusFilter}.</td></tr>`;
            return;
        }

        filteredUsers.forEach(user => {
            const tr = document.createElement('tr');
            const initials = user.name.slice(0,2).toUpperCase();
            
            // REMOVIDA LÓGICA DE EXIBIÇÃO RCA
            let extraInfo = user.phone || '-';

            tr.innerHTML = `
                <td><input type="checkbox" class="custom-checkbox user-checkbox" value="${user.id}"></td>
                <td>
                    <div class="collab-info">
                        <div class="collab-avatar" style="background:#e0e7ff; color:#3730a3;">${initials}</div>
                        <div><span class="collab-name">${user.name}</span><br><span class="collab-email">${user.email}</span></div>
                    </div>
                </td>
                <td><span class="role-badge ${user.role === 'gestor' ? 'role-manager' : 'role-promoter'}">${user.role}</span></td>
                <td>${extraInfo}</td>
                <td>${new Date(user.created_at).toLocaleDateString()}</td>
                <td><span class="status-badge status-done">${user.status || 'Ativo'}</span></td>
                <td class="action-cell">
                    <button class="btn-icon action-btn" onclick="toggleMenu(${user.id}, event)">
                        <i class="fa-solid fa-ellipsis-vertical"></i>
                    </button>
                    
                    <div id="menu-${user.id}" class="action-menu">
                        <button class="action-item" onclick="openReset(${user.id})">
                            <i class="fa-solid fa-key"></i> Resetar Senha
                        </button>
                        <button class="action-item" onclick="openEditData(${user.id})">
                            <i class="fa-solid fa-pencil"></i> Editar Dados Cadastrais
                        </button>
                        <button class="action-item" onclick="openPortfolio(${user.id})">
                            <i class="fa-solid fa-map-location-dot"></i> Editar Carteira (PDVs)
                        </button>
                        <div style="border-top:1px solid #eee; margin: 4px 0;"></div>
                        <button class="action-item danger" onclick="openDelete(${user.id})">
                            <i class="fa-solid fa-trash"></i> Excluir
                        </button>
                    </div>
                </td>
            `;
            tableBody.appendChild(tr);
        });

        attachRowCheckboxEvents();
    }

    function attachRowCheckboxEvents() {
        const rowCheckboxes = document.querySelectorAll('tbody .custom-checkbox');
        
        rowCheckboxes.forEach(cb => {
            cb.addEventListener('change', function() {
                const allChecked = Array.from(rowCheckboxes).every(c => c.checked);
                if(selectAllCheckbox) selectAllCheckbox.checked = allChecked;
                updateDeleteButtonState();
            });
        });
        
        if(selectAllCheckbox) selectAllCheckbox.checked = false;
        updateDeleteButtonState();
    }

    function updateDeleteButtonState() {
        if(!btnDeleteHeader) return;
        const checkedCount = document.querySelectorAll('tbody .custom-checkbox:checked').length;
        if (checkedCount > 0) {
            btnDeleteHeader.removeAttribute('disabled');
        } else {
            btnDeleteHeader.setAttribute('disabled', 'disabled');
        }
    }

    // --- FUNÇÕES GLOBAIS ---

    let activeMenuId = null;

    window.toggleMenu = function(id, event) {
        if(event) event.stopPropagation();

        const menu = document.getElementById(`menu-${id}`);
        const button = event.currentTarget; 

        document.querySelectorAll('.action-menu.active').forEach(m => {
            if(m.id !== `menu-${id}`) m.classList.remove('active');
        });

        if (menu.classList.contains('active')) {
            menu.classList.remove('active');
            activeMenuId = null;
            return;
        }

        const rect = button.getBoundingClientRect();
        menu.style.top = `${rect.bottom + 5}px`;
        menu.style.left = `${rect.right - 220}px`; 

        menu.classList.add('active');
        activeMenuId = id;
    };

    window.addEventListener('scroll', () => {
        if(activeMenuId) {
            document.querySelectorAll('.action-menu.active').forEach(m => m.classList.remove('active'));
            activeMenuId = null;
        }
    }, true);

    document.addEventListener('click', (e) => {
        if(!e.target.closest('.action-menu') && !e.target.closest('.action-btn')) {
            document.querySelectorAll('.action-menu.active').forEach(m => m.classList.remove('active'));
            activeMenuId = null;
        }
    });

    window.openEditData = function(id) {
        currentUserId = id;
        currentUserData = usersList.find(u => u.id == id);
        
        document.getElementById('modalTitle').innerText = 'Editar Dados Cadastrais';
        document.getElementById('inputName').value = currentUserData.name;
        document.getElementById('inputEmail').value = currentUserData.email;
        document.getElementById('inputPhone').value = currentUserData.phone || '';
        
        // Configura Role (SEM RCA)
        const role = currentUserData.role.toLowerCase();
        document.getElementById('inputRole').value = role;
        
        document.querySelector('label[for="inputPassword"]').parentElement.style.display = 'none';
        const oldPdvSection = document.querySelector('.pdv-section');
        if(oldPdvSection) oldPdvSection.style.display = 'none';

        modalCollaborator.classList.remove('hidden');
        document.getElementById(`menu-${id}`).classList.remove('active');
    };

    window.openPortfolio = async function(id) {
        currentUserId = id;
        currentUserData = usersList.find(u => u.id == id);

        const token = localStorage.getItem('tradeToken');
        const res = await fetch(`${API_URL}/${id}/pdvs`, { headers: { 'Authorization': `Bearer ${token}` } });
        const userPdvIds = await res.json(); 

        userPortfolio = allPdvs.filter(pdv => userPdvIds.includes(pdv.id));
        availablePdvs = allPdvs.filter(pdv => !userPdvIds.includes(pdv.id));

        renderDualLists();
        
        modalPortfolio.classList.remove('hidden');
        document.getElementById(`menu-${id}`).classList.remove('active');
    };

    window.openReset = function(id) {
        currentUserId = id;
        document.getElementById('newPasswordInput').value = '';
        modalReset.classList.remove('hidden');
        document.getElementById(`menu-${id}`).classList.remove('active');
    }

    window.openDelete = function(id) {
        currentUserId = id;
        modalDelete.classList.remove('hidden');
        document.getElementById(`menu-${id}`).classList.remove('active');
    }

    // --- DUAL LIST ---

    function renderDualLists() {
        const leftList = document.getElementById('listAvailable');
        const rightList = document.getElementById('listSelected');
        const filter = filterInput.value.toLowerCase();

        leftList.innerHTML = '';
        rightList.innerHTML = '';

        availablePdvs.forEach(pdv => {
            const str = `${pdv.name} ${pdv.company_name} ${pdv.address}`.toLowerCase();
            if(str.includes(filter)) {
                const card = createPdvCard(pdv, 'available');
                card.onclick = () => movePdv(pdv, 'toRight');
                leftList.appendChild(card);
            }
        });

        userPortfolio.forEach(pdv => {
            const str = `${pdv.name} ${pdv.company_name} ${pdv.address}`.toLowerCase();
            if(str.includes(filter)) {
                const card = createPdvCard(pdv, 'selected');
                card.onclick = () => movePdv(pdv, 'toLeft');
                rightList.appendChild(card);
            }
        });

        document.getElementById('countAvailable').innerText = availablePdvs.length;
        document.getElementById('countSelected').innerText = userPortfolio.length;
    }

    function createPdvCard(pdv, type) {
        const div = document.createElement('div');
        div.className = `pdv-card-item ${type}`;
        const iconClass = type === 'available' ? 'fa-arrow-right' : 'fa-xmark';
        
        div.innerHTML = `
            <div class="pdv-card-name">${pdv.name}</div>
            <div class="pdv-card-sub">${pdv.company_name || 'Empresa'} - ${pdv.city}</div>
            <i class="fa-solid ${iconClass} action-icon"></i>
        `;
        return div;
    }

    function movePdv(pdv, direction) {
        if(direction === 'toRight') {
            availablePdvs = availablePdvs.filter(p => p.id !== pdv.id);
            userPortfolio.push(pdv);
        } else {
            userPortfolio = userPortfolio.filter(p => p.id !== pdv.id);
            availablePdvs.push(pdv);
        }
        availablePdvs.sort((a,b) => a.name.localeCompare(b.name));
        userPortfolio.sort((a,b) => a.name.localeCompare(b.name));
        renderDualLists();
    }

    // --- EVENT LISTENERS GERAIS ---

    btnNew.addEventListener('click', () => {
        currentUserId = null;
        document.getElementById('collaboratorForm').reset();
        document.getElementById('modalTitle').innerText = 'Novo Colaborador';
        document.querySelector('label[for="inputPassword"]').parentElement.style.display = 'block';
        
        // Reset role padrão (Promotor)
        document.getElementById('inputRole').value = 'promotor';

        const oldPdvSection = document.querySelector('.pdv-section');
        if(oldPdvSection) oldPdvSection.style.display = 'none';

        modalCollaborator.classList.remove('hidden');
    });

    if(btnDeleteHeader) {
        btnDeleteHeader.addEventListener('click', () => {
            currentUserId = null;
            modalDelete.classList.remove('hidden');
        });
    }

    btnSaveData.addEventListener('click', (e) => { e.preventDefault(); saveCollaboratorData(); });
    btnSavePortfolio.addEventListener('click', (e) => { e.preventDefault(); savePortfolio(); });
    btnConfirmReset.addEventListener('click', resetPassword);
    btnConfirmDelete.addEventListener('click', deleteCollaborator);
    
    filterInput.addEventListener('input', () => renderDualLists());

    [document.getElementById('modalPortfolio'), modalCollaborator, modalDelete, modalReset].forEach(m => {
        if(!m) return;
        const closes = m.querySelectorAll('.btn-icon, .btn-secondary'); 
        closes.forEach(btn => {
            if(btn.innerHTML.includes('Cancelar') || btn.classList.contains('btn-icon')) {
                btn.addEventListener('click', (e) => { 
                    e.preventDefault(); 
                    m.classList.add('hidden'); 
                });
            }
        })
    });
});