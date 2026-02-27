document.addEventListener('DOMContentLoaded', function() {
    // --- Elementos de Controle de Tela ---
    const listView = document.getElementById('listView');
    const formView = document.getElementById('formView');
    const pageTitle = document.getElementById('pageTitle');
    const pageSubtitle = document.getElementById('pageSubtitle');
    const mainHeaderActions = document.getElementById('mainHeaderActions');

    // --- Botões Principais ---
    const btnCreate = document.getElementById('btnCreate');
    const btnEdit = document.getElementById('btnEdit');
    const btnDelete = document.getElementById('btnDelete');
    
    // --- Elementos do Formulário ---
    const btnCancelForm = document.getElementById('btnCancelForm');
    const btnSaveForm = document.getElementById('btnSaveForm');
    const pdvForm = document.getElementById('pdvForm');
    
    // Toggle Promotor
    const addPromoterToggle = document.getElementById('addPromoterToggle');
    const promoterFields = document.getElementById('promoterFields');
    const promoterAllMsg = document.getElementById('promoterAllMsg');

    // --- Elementos da Tabela ---
    const selectAllCheckbox = document.getElementById('selectAll');
    const rowCheckboxes = document.querySelectorAll('.row-checkbox');

    // ==========================================
    // LÓGICA DE NAVEGAÇÃO (List <-> Form)
    // ==========================================

    function showForm(mode) {
        // Esconde Lista, Mostra Form
        listView.style.display = 'none';
        formView.style.display = 'block';
        
        // Esconde botões do header principal (Novo, Editar...)
        mainHeaderActions.style.display = 'none';

        if (mode === 'create') {
            pageTitle.innerText = "Novo Ponto de Venda";
            pageSubtitle.innerText = "Preencha os dados abaixo para cadastrar.";
            pdvForm.reset(); // Limpa campos
            handlePromoterToggle(); // Reseta estado do toggle
        } else if (mode === 'edit') {
            pageTitle.innerText = "Editar Ponto de Venda";
            pageSubtitle.innerText = "Altere os dados necessários.";
            fillFormWithMockData(); // Preenche com dados falsos
        }
    }

    function showList() {
        // Mostra Lista, Esconde Form
        listView.style.display = 'block';
        formView.style.display = 'none';
        
        // Restaura cabeçalho
        mainHeaderActions.style.display = 'flex';
        pageTitle.innerText = "Pontos de Venda";
        pageSubtitle.innerText = "Gerencie sua base de lojas e clientes.";
    }

    // ==========================================
    // EVENTOS DE BOTÕES
    // ==========================================

    // Botão NOVO
    btnCreate.addEventListener('click', () => {
        showForm('create');
    });

    // Botão EDITAR
    btnEdit.addEventListener('click', () => {
        showForm('edit');
    });

    // Botão CANCELAR (dentro do form)
    btnCancelForm.addEventListener('click', () => {
        showList();
    });

    // Botão SALVAR (dentro do form)
    btnSaveForm.addEventListener('click', () => {
        // Aqui entraria a validação
        alert("PDV Salvo com sucesso!");
        showList();
    });

    // Botão EXCLUIR
    btnDelete.addEventListener('click', confirmDelete);

    // ==========================================
    // LÓGICA DO FORMULÁRIO (Promotor)
    // ==========================================

    function handlePromoterToggle() {
        if (addPromoterToggle.checked) {
            // Se SIM: Mostra campos, esconde mensagem "Todos"
            promoterFields.style.display = 'block';
            promoterAllMsg.style.display = 'none';
        } else {
            // Se NÃO: Esconde campos, mostra mensagem "Todos"
            promoterFields.style.display = 'none';
            promoterAllMsg.style.display = 'flex';
        }
    }

    // Escuta mudanças no checkbox
    addPromoterToggle.addEventListener('change', handlePromoterToggle);

    // Inicializa o estado correto ao carregar
    handlePromoterToggle();

    // Função auxiliar para simular dados no "Editar"
    function fillFormWithMockData() {
        document.getElementById('cliente').value = "Carrefour - Loja 102";
        document.getElementById('codigo').value = "CARR-102";
        document.getElementById('uf').value = "SP";
        document.getElementById('municipio').value = "São Paulo";
        document.getElementById('bairro').value = "Jardim Paulista";
        document.getElementById('endereco').value = "Av. das Nações Unidas, 1200";
        
        // Simula que tem promotor
        addPromoterToggle.checked = true;
        handlePromoterToggle();
        document.getElementById('nomePromotor').value = "Andreza Silva";
        document.getElementById('nomeRCA').value = "Carlos RCA";
    }

    // ==========================================
    // LÓGICA DA TABELA (Checkboxes) - Mantida da versão anterior
    // ==========================================
    
    // Inicialização
    updateButtons();

    // Evento no Checkbox "Selecionar Todos"
    selectAllCheckbox.addEventListener('change', function() {
        const isChecked = this.checked;
        rowCheckboxes.forEach(checkbox => {
            checkbox.checked = isChecked;
            toggleRowHighlight(checkbox);
        });
        updateButtons();
    });

    // Eventos nos Checkboxes individuais
    rowCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            toggleRowHighlight(this);
            updateButtons();
            updateMasterCheckboxState();
        });
    });

    function updateButtons() {
        const checkedCount = document.querySelectorAll('.row-checkbox:checked').length;

        // Regra Editar: Apenas 1 selecionado
        btnEdit.disabled = (checkedCount !== 1);

        // Regra Excluir: 1 ou mais selecionados
        btnDelete.disabled = (checkedCount < 1);
    }

    function toggleRowHighlight(checkbox) {
        const row = checkbox.closest('tr');
        if (checkbox.checked) {
            row.classList.add('selected-row');
        } else {
            row.classList.remove('selected-row');
        }
    }

    function updateMasterCheckboxState() {
        const totalRows = rowCheckboxes.length;
        const checkedCount = document.querySelectorAll('.row-checkbox:checked').length;

        if (checkedCount === 0) {
            selectAllCheckbox.checked = false;
            selectAllCheckbox.indeterminate = false;
        } else if (checkedCount === totalRows) {
            selectAllCheckbox.checked = true;
            selectAllCheckbox.indeterminate = false;
        } else {
            selectAllCheckbox.checked = false;
            selectAllCheckbox.indeterminate = true;
        }
    }

    function confirmDelete() {
        const checkedCount = document.querySelectorAll('.row-checkbox:checked').length;
        const message = checkedCount > 1 
            ? `Tem certeza que deseja excluir os ${checkedCount} PDVs selecionados?` 
            : 'Tem certeza que deseja excluir este PDV?';

        if (confirm(message)) {
            alert('Operação realizada com sucesso!');
            // Reset visual simulation
            rowCheckboxes.forEach(cb => { 
                cb.checked = false; 
                toggleRowHighlight(cb);
            });
            updateButtons();
            updateMasterCheckboxState();
        }
    }
});