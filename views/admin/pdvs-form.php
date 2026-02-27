<?php
$isEdit = !empty($pdv);
$action = $isEdit ? "/admin/pdvs/{$pdv['id']}/atualizar" : '/admin/pdvs/salvar';
?>

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

<style>
    #map { height: 380px; width: 100%; border-radius: 4px; border: 1px solid var(--border); margin-top: 10px; z-index: 1; }
    .coords-display { background-color: #f8f9fa; cursor: not-allowed; }
    .reverse-geo-status { font-size: 11px; color: var(--text-muted); margin-top: 4px; min-height: 16px; }
    .reverse-geo-status.loading { color: var(--primary); }
    .reverse-geo-status.success { color: var(--success); }
    .map-legend { display: flex; flex-wrap: wrap; gap: 12px; font-size: 12px; color: var(--text-muted); margin-top: 8px; }
    .map-legend-item { display: flex; align-items: center; gap: 5px; }
    .legend-dot { width: 12px; height: 12px; border-radius: 50%; border: 2px solid white; box-shadow: 0 0 3px rgba(0,0,0,0.3); }
    .legend-dot.pdv { background: #ef4444; }
    .legend-dot.checkin { background: #3b82f6; }
</style>

<div class="form-page-wrapper">
<div class="section-card form-card form-card-lg">
    <div class="section-header">
        <h3 class="section-title">
            <i class="fa-solid fa-<?= $isEdit ? 'pen' : 'plus' ?>"></i>
            <?= $isEdit ? 'Editar Ponto de Venda' : 'Cadastrar Ponto de Venda' ?>
        </h3>
    </div>

    <form method="POST" action="<?= $action ?>">
        <?= csrf_field() ?>

        <div class="form-row">
            <div class="form-group" style="flex:0 0 200px;">
                <label for="codigo">Código <span class="text-muted text-xs">(manual)</span></label>
                <input type="text" id="codigo" name="codigo" class="form-control"
                       value="<?= e($pdv['codigo'] ?? '') ?>" placeholder="Ex: PDV-001">
            </div>
            <div class="form-group" style="flex:1;">
                <label for="nome">Nome do Ponto de Venda <span class="text-danger">*</span></label>
                <input type="text" id="nome" name="nome" class="form-control"
                       value="<?= e($pdv['nome'] ?? '') ?>" required placeholder="Ex: Carrefour - Loja 102">
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="cnpj">CNPJ</label>
                <input type="text" id="cnpj" name="cnpj" class="form-control"
                       value="<?= e($pdv['cnpj'] ?? '') ?>" placeholder="00.000.000/0000-00" maxlength="18">
            </div>
            <div class="form-group">
                <label for="telefone">Telefone</label>
                <input type="text" id="telefone" name="telefone" class="form-control"
                       value="<?= e($pdv['telefone'] ?? '') ?>" placeholder="(00) 0000-0000">
            </div>
        </div>

        <hr style="border:none; border-top:1px solid var(--border); margin:var(--space-lg) 0;">
        
        <h4 style="margin-bottom:var(--space-md); color:var(--text-muted);">
            <i class="fa-solid fa-location-dot"></i> Endereço
        </h4>

        <div class="form-row">
            <div class="form-group" style="flex:0 0 180px;">
                <label for="cep">CEP <span class="text-danger">*</span></label>
                <input type="text" id="cep" name="cep" class="form-control"
                       value="<?= e($pdv['cep'] ?? '') ?>" placeholder="00000-000" maxlength="9">
            </div>
            <div class="form-group" style="flex:1;">
                <label for="rua">Rua / Logradouro</label>
                <input type="text" id="rua" name="rua" class="form-control"
                       value="<?= e($pdv['rua'] ?? '') ?>" placeholder="Av. Paulista">
            </div>
            <div class="form-group" style="flex:0 0 120px;">
                <label for="numero">Número</label>
                <input type="text" id="numero" name="numero" class="form-control"
                       value="<?= e($pdv['numero'] ?? '') ?>" placeholder="1234">
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="bairro">Bairro</label>
                <input type="text" id="bairro" name="bairro" class="form-control"
                       value="<?= e($pdv['bairro'] ?? '') ?>" placeholder="Bela Vista">
            </div>
            <div class="form-group">
                <label for="cidade">Cidade</label>
                <input type="text" id="cidade" name="cidade" class="form-control"
                       value="<?= e($pdv['cidade'] ?? '') ?>" placeholder="São Paulo">
            </div>
            <div class="form-group" style="flex:0 0 100px;">
                <label for="uf">UF</label>
                <input type="text" id="uf" name="uf" class="form-control"
                       value="<?= e($pdv['uf'] ?? '') ?>" placeholder="SP" maxlength="2">
            </div>
        </div>

        <div class="form-group">
            <label for="endereco">Complemento</label>
            <input type="text" id="endereco" name="endereco" class="form-control"
                   value="<?= e($pdv['endereco'] ?? '') ?>" placeholder="Bloco A, Sala 302...">
        </div>

        <hr style="border:none; border-top:1px solid var(--border); margin:var(--space-lg) 0;">

        <h4 style="margin-bottom:var(--space-md); color:var(--text-muted);">
            <i class="fa-solid fa-map-location-dot"></i> Localização
        </h4>

        <div class="form-group">
            <label>Coordenadas <span class="text-danger">*</span> <span class="text-muted text-xs font-weight-normal">(Automático ou arraste o pino)</span></label>
            
            <div style="display:flex; gap: 10px; margin-bottom: 10px;">
                <input type="text" name="latitude" id="latitude" class="form-control coords-display" 
                       value="<?= e($pdv['latitude'] ?? '') ?>" placeholder="Latitude" readonly>
                <input type="text" name="longitude" id="longitude" class="form-control coords-display" 
                       value="<?= e($pdv['longitude'] ?? '') ?>" placeholder="Longitude" readonly>
                <button type="button" id="btnBuscarCoords" class="btn-outline" style="min-width: 120px;" title="Forçar busca no mapa">
                    <i class="fa-solid fa-magnifying-glass-location"></i> Buscar
                </button>
            </div>

            <div id="map"></div>
            <p id="reverse-geo-status" class="reverse-geo-status"></p>
            
            <p id="aviso-coords" class="text-xs text-danger" style="margin-top:5px; display:none; font-weight:bold;">
                <i class="fa-solid fa-triangle-exclamation"></i> 
                Aguardando localização. O botão de salvar ficará bloqueado até que as coordenadas sejam definidas.
            </p>

            <?php if ($isEdit): ?>
            <div class="map-legend">
                <span class="map-legend-item"><span class="legend-dot pdv"></span> Localização do PDV</span>
                <?php if (!empty($ultimosCheckins)): ?>
                <span class="map-legend-item"><span class="legend-dot checkin"></span> Últimos <?= count($ultimosCheckins) ?> check-ins</span>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>

        <div class="form-group">
            <label for="responsavel">Responsável</label>
            <input type="text" id="responsavel" name="responsavel" class="form-control"
                   value="<?= e($pdv['responsavel'] ?? '') ?>" placeholder="Nome do gerente">
        </div>

        <div class="form-actions">
            <a href="/admin/pdvs" class="btn-outline">Cancelar</a>
            <button type="submit" id="btnSalvar" class="btn-primary" disabled>
                <i class="fa-solid fa-check"></i>
                <?= $isEdit ? 'Salvar Alterações' : 'Cadastrar PDV' ?>
            </button>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    // === CONFIGURAÇÃO DO MAPA ===
    let map;
    let marker;
    const defaultLat = -23.550520;
    const defaultLon = -46.633308;
    
    let currentLat = parseFloat(document.getElementById('latitude').value) || defaultLat;
    let currentLon = parseFloat(document.getElementById('longitude').value) || defaultLon;
    let initialZoom = document.getElementById('latitude').value ? 16 : 4;

    map = L.map('map').setView([currentLat, currentLon], initialZoom);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap'
    }).addTo(map);

    // Ícone customizado vermelho para o PDV
    const pdvIcon = L.divIcon({
        html: '<div style="position:relative;"><div style="width:22px;height:22px;background:#ef4444;border:3px solid white;border-radius:50%;box-shadow:0 2px 6px rgba(0,0,0,0.35);"></div><div style="width:0;height:0;border-left:6px solid transparent;border-right:6px solid transparent;border-top:8px solid #ef4444;margin:-2px auto 0;filter:drop-shadow(0 1px 1px rgba(0,0,0,0.2));"></div></div>',
        className: '',
        iconSize: [22, 30],
        iconAnchor: [11, 30]
    });

    marker = L.marker([currentLat, currentLon], { draggable: true, icon: pdvIcon }).addTo(map);
    marker.bindTooltip('Localização do PDV', { permanent: false, direction: 'top', offset: [0, -30] });

    marker.on('dragend', function(event) {
        const position = marker.getLatLng();
        atualizarInputs(position.lat, position.lng);
    });

    map.on('click', function(e) {
        marker.setLatLng(e.latlng);
        atualizarInputs(e.latlng.lat, e.latlng.lng);
    });

    let reverseGeoTimer = null;

    function atualizarInputs(lat, lon) {
        document.getElementById('latitude').value = lat.toFixed(7);
        document.getElementById('longitude').value = lon.toFixed(7);
        validarBotaoSalvar();

        // Reverse geocoding com debounce de 400ms
        clearTimeout(reverseGeoTimer);
        reverseGeoTimer = setTimeout(() => reverseGeocode(lat, lon), 400);
    }

    // === REVERSE GEOCODING (Nominatim / OpenStreetMap) ===
    async function reverseGeocode(lat, lon) {
        const statusEl = document.getElementById('reverse-geo-status');
        statusEl.className = 'reverse-geo-status loading';
        statusEl.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Buscando endereço...';

        try {
            const url = `https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lon}&zoom=18&addressdetails=1&accept-language=pt`;
            const res = await fetch(url, {
                headers: { 'User-Agent': 'TradeGriffusApp/1.0' }
            });
            const data = await res.json();

            if (data && data.address) {
                const addr = data.address;
                
                // Preenche campos do formulário
                if (addr.road) document.getElementById('rua').value = addr.road;
                if (addr.house_number) document.getElementById('numero').value = addr.house_number;
                if (addr.suburb || addr.neighbourhood) document.getElementById('bairro').value = addr.suburb || addr.neighbourhood || '';
                if (addr.city || addr.town || addr.village || addr.municipality) document.getElementById('cidade').value = addr.city || addr.town || addr.village || addr.municipality || '';
                if (addr.state) {
                    const uf = extrairUF(addr.state);
                    if (uf) document.getElementById('uf').value = uf;
                }
                if (addr.postcode) {
                    let cep = addr.postcode.replace(/\D/g, '');
                    if (cep.length === 8) {
                        cep = cep.replace(/^(\d{5})(\d{3})/, '$1-$2');
                    }
                    document.getElementById('cep').value = cep;
                }

                const enderecoResumo = [addr.road, addr.house_number, addr.city || addr.town].filter(Boolean).join(', ');
                statusEl.className = 'reverse-geo-status success';
                statusEl.innerHTML = '<i class="fa-solid fa-check"></i> ' + enderecoResumo;
            } else {
                statusEl.className = 'reverse-geo-status';
                statusEl.textContent = 'Endereço não encontrado para esta posição';
            }
        } catch (error) {
            statusEl.className = 'reverse-geo-status';
            statusEl.textContent = '';
        }
    }

    // Mapa de estados brasileiros para UF
    function extrairUF(estado) {
        if (!estado) return null;
        if (estado.length === 2) return estado.toUpperCase();
        const mapa = {
            'acre':'AC','alagoas':'AL','amapá':'AP','amazonas':'AM','bahia':'BA',
            'ceará':'CE','distrito federal':'DF','espírito santo':'ES','goiás':'GO',
            'maranhão':'MA','mato grosso':'MT','mato grosso do sul':'MS',
            'minas gerais':'MG','pará':'PA','paraíba':'PB','paraná':'PR',
            'pernambuco':'PE','piauí':'PI','rio de janeiro':'RJ',
            'rio grande do norte':'RN','rio grande do sul':'RS','rondônia':'RO',
            'roraima':'RR','santa catarina':'SC','são paulo':'SP',
            'sergipe':'SE','tocantins':'TO'
        };
        return mapa[estado.toLowerCase()] || null;
    }

    // === MARCADORES DE CHECK-IN (últimos 3) ===
    <?php if ($isEdit && !empty($ultimosCheckins)): ?>
    const checkinsData = <?= json_encode($ultimosCheckins ?? []) ?>;
    const checkinIcon = L.divIcon({
        html: '<div style="width:14px;height:14px;background:#3b82f6;border:2.5px solid white;border-radius:50%;box-shadow:0 1px 4px rgba(0,0,0,0.3);"></div>',
        className: '',
        iconSize: [14, 14],
        iconAnchor: [7, 7]
    });

    checkinsData.forEach((checkin, i) => {
        const dt = new Date(checkin.checkin_at);
        const dataFormatada = dt.toLocaleDateString('pt-BR') + ' ' + dt.toLocaleTimeString('pt-BR', {hour:'2-digit', minute:'2-digit'});
        
        L.marker([parseFloat(checkin.latitude_in), parseFloat(checkin.longitude_in)], { icon: checkinIcon })
            .addTo(map)
            .bindPopup(`<b>${checkin.promotor_nome}</b><br><small>${dataFormatada}</small>`);
    });
    <?php endif; ?>

    function validarBotaoSalvar() {
        const lat = document.getElementById('latitude').value;
        const lon = document.getElementById('longitude').value;
        const btn = document.getElementById('btnSalvar');
        const aviso = document.getElementById('aviso-coords');

        if (lat && lon && lat !== '0' && lon !== '0') {
            btn.disabled = false;
            btn.style.opacity = '1';
            btn.style.cursor = 'pointer';
            aviso.style.display = 'none';
        } else {
            btn.disabled = true;
            btn.style.opacity = '0.6';
            btn.style.cursor = 'not-allowed';
            aviso.style.display = 'block';
        }
    }

    // === BUSCA COM PHOTON API (Gratuita, baseada em OpenStreetMap) ===
    async function buscarComPhoton(endereco) {
        try {
            // Photon API - Geocoder gratuito baseado em OSM, otimizado para Europa e América do Sul
            const url = `https://photon.komoot.io/api/?q=${encodeURIComponent(endereco)}&limit=5&lang=pt`;
            const res = await fetch(url);
            const data = await res.json();

            if (data.features && data.features.length > 0) {
                // Filtra resultados do Brasil
                const resultadosBrasil = data.features.filter(feature => {
                    const country = feature.properties.country;
                    return country && (country.toLowerCase() === 'brasil' || country.toLowerCase() === 'brazil');
                });

                if (resultadosBrasil.length > 0) {
                    const coords = resultadosBrasil[0].geometry.coordinates; // [lon, lat]
                    const props = resultadosBrasil[0].properties;

                    return {
                        lat: coords[1],
                        lng: coords[0],
                        name: props.name,
                        street: props.street
                    };
                }
            }

            return null;
        } catch (error) {
            return null;
        }
    }

    // === BUSCA DE COORDENADAS (Sistema Híbrido) ===
    async function buscarCoordenadas(dadosEndereco = null) {
        const btn = document.getElementById('btnBuscarCoords');
        const iconOriginal = btn.innerHTML;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Buscando...';

        try {
            let rua, numero, bairro, cidade, uf;

            // Dados vindos do ViaCEP (AwesomeAPI)
            if (dadosEndereco) {
                rua = dadosEndereco.logradouro || '';
                bairro = dadosEndereco.bairro || '';
                cidade = dadosEndereco.localidade || '';
                uf = dadosEndereco.uf || '';
                numero = document.getElementById('numero').value || '';

                // Se AwesomeAPI já retornou coordenadas, usa elas primeiro
                if (dadosEndereco.lat && dadosEndereco.lng) {
                    const lat = parseFloat(dadosEndereco.lat);
                    const lng = parseFloat(dadosEndereco.lng);
                    
                    map.setView([lat, lng], 16);
                    marker.setLatLng([lat, lng]);
                    atualizarInputs(lat, lng);
                    btn.innerHTML = iconOriginal;
                    return;
                }
            } else {
                // Busca manual via botão
                rua = document.getElementById('rua').value || '';
                numero = document.getElementById('numero').value || '';
                bairro = document.getElementById('bairro').value || '';
                cidade = document.getElementById('cidade').value || '';
                uf = document.getElementById('uf').value || '';
            }

            if (!cidade || !uf) {
                App.toast('Preencha pelo menos Cidade e UF para buscar coordenadas.', 'warning');
                btn.innerHTML = iconOriginal;
                return;
            }

            // ESTRATÉGIA: Tenta múltiplas combinações com Photon API
            const queries = [];
            
            // 1. Endereço completo
            if (rua && numero) {
                queries.push(`${rua}, ${numero}, ${cidade}, ${uf}, Brasil`);
                queries.push(`${numero} ${rua}, ${cidade}, ${uf}, Brasil`);
            }
            
            // 2. Rua + cidade
            if (rua) {
                queries.push(`${rua}, ${cidade}, ${uf}, Brasil`);
            }
            
            // 3. Bairro + cidade
            if (bairro) {
                queries.push(`${bairro}, ${cidade}, ${uf}, Brasil`);
            }
            
            // 4. Só cidade (fallback)
            queries.push(`${cidade}, ${uf}, Brasil`);

            for (let i = 0; i < queries.length; i++) {
                const query = queries[i];

                const resultado = await buscarComPhoton(query);
                
                if (resultado) {
                    // Define zoom baseado na especificidade
                    let zoom = 13;
                    if (i === 0 || i === 1) zoom = 18; // endereço completo
                    else if (i === 2) zoom = 16; // rua
                    else if (i === 3) zoom = 15; // bairro
                    else zoom = 13; // cidade

                    map.setView([resultado.lat, resultado.lng], zoom);
                    marker.setLatLng([resultado.lat, resultado.lng]);
                    atualizarInputs(resultado.lat, resultado.lng);

                    if (i > 2) {
                        App.toast('Localização aproximada encontrada. Ajuste o pino arrastando para a posição exata.', 'info', 5000);
                    }
                    
                    btn.innerHTML = iconOriginal;
                    return;
                }

                // Aguarda 500ms entre tentativas
                if (i < queries.length - 1) {
                    await new Promise(resolve => setTimeout(resolve, 500));
                }
            }

            // Nenhuma tentativa funcionou
            App.toast('Não foi possível localizar o endereço. Posicione o pino manualmente no mapa.', 'warning', 5000);

        } catch (error) {
            App.toast('Erro ao buscar coordenadas. Tente novamente.', 'danger');
        } finally {
            btn.innerHTML = iconOriginal;
        }
    }

    // === BUSCAR CEP (AwesomeAPI - API Brasileira Gratuita) ===
    window.buscarCep = async function(cepSemFormatacao) {
        const cep = cepSemFormatacao.replace(/\D/g, '');
        if (cep.length !== 8) return;

        const cepInput = document.getElementById('cep');
        cepInput.style.color = 'var(--primary)';

        try {
            // AwesomeAPI - Retorna endereço + coordenadas do CEP
            const url = `https://cep.awesomeapi.com.br/json/${cep}`;
            
            const res = await fetch(url);
            const data = await res.json();

            if (data && !data.error && data.cep) {
                // Preenche campos do endereço
                document.getElementById('rua').value = data.address || '';
                document.getElementById('bairro').value = data.district || '';
                document.getElementById('cidade').value = data.city || '';
                document.getElementById('uf').value = data.state || '';

                // Busca coordenadas (AwesomeAPI já retorna lat/lng)
                await buscarCoordenadas({
                    logradouro: data.address,
                    bairro: data.district,
                    localidade: data.city,
                    uf: data.state,
                    lat: data.lat,
                    lng: data.lng
                });

                if (data.address) {
                    document.getElementById('numero').focus();
                }
            } else {
                App.toast('CEP não encontrado.', 'warning');
            }
        } catch (e) {
            App.toast('Erro ao buscar CEP. Verifique sua conexão.', 'danger');
        } finally {
            cepInput.style.color = '';
        }
    };

    // === EVENT LISTENERS ===
    
    // Máscara e busca automática do CEP
    document.getElementById('cep').addEventListener('input', function() {
        let v = this.value.replace(/\D/g, '');
        if (v.length > 8) v = v.slice(0, 8);
        
        let vFormatado = v;
        if (v.length > 5) {
            vFormatado = v.replace(/^(\d{5})(\d{1,3})/, '$1-$2');
        }
        this.value = vFormatado;
        
        if (v.length === 8) {
            buscarCep(v);
        }
    });

    // Botão buscar manual
    document.getElementById('btnBuscarCoords').addEventListener('click', () => buscarCoordenadas(null));

    // Busca refinada quando sair do campo número
    document.getElementById('numero').addEventListener('blur', function() {
        const rua = document.getElementById('rua').value;
        const num = this.value;
        const latAtual = document.getElementById('latitude').value;
        
        // Só busca se tiver rua e número, e coordenadas ainda não estiverem definidas
        if (rua && num && (!latAtual || latAtual === '0' || Math.abs(parseFloat(latAtual) - defaultLat) < 0.0001)) {
            buscarCoordenadas(null);
        }
    });

    // Máscara CNPJ
    document.getElementById('cnpj')?.addEventListener('input', function() {
        let v = this.value.replace(/\D/g, '');
        if (v.length > 14) v = v.slice(0, 14);
        if (v.length > 12) v = v.replace(/^(\d{2})(\d{3})(\d{3})(\d{4})(\d{1,2})/, '$1.$2.$3/$4-$5');
        else if (v.length > 8) v = v.replace(/^(\d{2})(\d{3})(\d{3})(\d{1,4})/, '$1.$2.$3/$4');
        else if (v.length > 5) v = v.replace(/^(\d{2})(\d{3})(\d{1,3})/, '$1.$2.$3');
        else v = v.replace(/^(\d{2})(\d{1,3})/, '$1.$2');
        this.value = v;
    });

    // Validação inicial
    validarBotaoSalvar();
});
</script>
</div>