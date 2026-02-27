<!-- Mapa de PDVs — todos os pontos de venda do promotor -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<div class="promotor-welcome">
    <h3><i class="fa-solid fa-map-location-dot"></i> Meu Mapa</h3>
    <p>Todos os seus pontos de venda</p>
</div>

<div class="section-card" style="padding:0; overflow:hidden; border-radius:var(--radius-lg);">
    <div id="mapaPdvs" style="height: calc(100vh - 200px); min-height:400px; width:100%;"></div>
</div>

<!-- Legenda -->
<div class="section-card" style="padding: var(--space-sm) var(--space-md);">
    <div style="display:flex; gap:var(--space-md); flex-wrap:wrap; justify-content:center; font-size:var(--font-sm);">
        <span><i class="fa-solid fa-circle" style="color:#10B981;"></i> Concluída</span>
        <span><i class="fa-solid fa-circle" style="color:#6366F1;"></i> Em andamento</span>
        <span><i class="fa-solid fa-circle" style="color:#9CA3AF;"></i> Pendente</span>
        <span><i class="fa-solid fa-circle" style="color:#3B82F6;"></i> Minha posição</span>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const pdvs = <?= json_encode($pdvs) ?>;
    const visitasHoje = <?= json_encode($visitasHoje ?? []) ?>;
    const mapEl = document.getElementById('mapaPdvs');

    // Index visitas de hoje por pdv_id
    const visitaIndex = {};
    visitasHoje.forEach(v => { visitaIndex[v.pdv_id] = v; });

    // Filtra PDVs com coordenadas
    const comCoord = pdvs.filter(p => p.latitude && p.longitude && p.latitude != 0 && p.longitude != 0);

    if (comCoord.length === 0) {
        // Centraliza no Brasil se nenhum PDV tem coordenadas
        let map = L.map('mapaPdvs').setView([-15.8, -47.9], 4);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap', maxZoom: 18,
        }).addTo(map);

        L.popup()
            .setLatLng([-15.8, -47.9])
            .setContent('<strong>Nenhum PDV com coordenadas cadastradas.</strong><br>Cadastre latitude/longitude nos PDVs.')
            .openOn(map);
        return;
    }

    let map = L.map('mapaPdvs', { zoomControl: false });
    L.control.zoom({ position: 'topright' }).addTo(map);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap', maxZoom: 18,
    }).addTo(map);

    const statusColors = {
        'pendente':      '#9CA3AF',
        'em_andamento':  '#6366F1',
        'concluida':     '#10B981',
    };

    const bounds = [];
    comCoord.forEach((p, i) => {
        const visita = visitaIndex[p.id];
        const status = visita ? visita.status : null;
        const color = status ? (statusColors[status] || '#9CA3AF') : '#F59E0B';

        const icon = L.divIcon({
            className: 'map-marker-custom',
            html: `<div style="
                background:${color}; color:white; width:30px; height:30px;
                border-radius:50%; display:flex; align-items:center; justify-content:center;
                font-weight:700; font-size:12px; border:3px solid white;
                box-shadow:0 2px 6px rgba(0,0,0,0.3);
            "><i class="fa-solid fa-store" style="font-size:12px;"></i></div>`,
            iconSize: [30, 30],
            iconAnchor: [15, 15],
        });

        const endereco = p.endereco || [p.rua, p.numero, p.bairro, p.cidade].filter(Boolean).join(', ') || '';
        
        L.marker([parseFloat(p.latitude), parseFloat(p.longitude)], { icon }).addTo(map)
            .bindPopup(`
                <strong>${p.nome}</strong><br>
                <small style="color:#666;">${endereco}</small>
                ${status ? '<br><span style="font-size:11px; font-weight:600; color:' + color + ';">' + 
                    (status === 'concluida' ? '✅ Concluída' : status === 'em_andamento' ? '🔄 Em andamento' : '⏳ Pendente') + 
                    '</span>' : ''}
            `);

        bounds.push([parseFloat(p.latitude), parseFloat(p.longitude)]);
    });

    if (bounds.length === 1) {
        map.setView(bounds[0], 15);
    } else {
        map.fitBounds(bounds, { padding: [30, 30] });
    }

    // Posição atual do promotor
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(pos => {
            const { latitude, longitude } = pos.coords;
            const myIcon = L.divIcon({
                className: 'map-marker-custom',
                html: `<div style="
                    background:#3B82F6; color:white; width:22px; height:22px;
                    border-radius:50%; border:3px solid white;
                    box-shadow:0 0 0 6px rgba(59,130,246,0.25);
                "></div>`,
                iconSize: [22, 22],
                iconAnchor: [11, 11],
            });
            L.marker([latitude, longitude], { icon: myIcon }).addTo(map)
                .bindPopup('📍 Minha posição');
        }, () => {}, { enableHighAccuracy: true, timeout: 5000 });
    }
});
</script>
