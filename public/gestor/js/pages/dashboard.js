document.addEventListener('DOMContentLoaded', function() {
    const canvas = document.getElementById('visitsChart');
    if (!canvas) return;

    const ctx = canvas.getContext('2d');
    
    // Gradiente para o gráfico
    const gradient = ctx.createLinearGradient(0, 0, 0, 400);
    gradient.addColorStop(0, 'rgba(37, 99, 235, 0.5)'); // Azul forte topo
    gradient.addColorStop(1, 'rgba(37, 99, 235, 0.0)'); // Transparente base

    const myChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: ['Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb', 'Dom'],
            datasets: [
                {
                    label: 'Visitas Realizadas',
                    data: [45, 59, 80, 81, 56, 95, 110],
                    borderColor: '#2563eb', // Azul SGC
                    backgroundColor: gradient,
                    tension: 0.4, // Curva suave
                    fill: true,
                    pointBackgroundColor: '#ffffff',
                    pointBorderColor: '#2563eb',
                    pointBorderWidth: 2,
                    pointRadius: 4
                },
                {
                    label: 'Meta Diária',
                    data: [60, 60, 60, 60, 60, 90, 90],
                    borderColor: '#f59e0b', // Laranja tracejado
                    borderDash: [5, 5],
                    backgroundColor: 'transparent',
                    tension: 0.4,
                    pointRadius: 0
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top',
                    align: 'end',
                    labels: { usePointStyle: true, boxWidth: 8 }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: { borderDash: [2, 2], drawBorder: false }
                },
                x: {
                    grid: { display: false }
                }
            }
        }
    });

    // Listen for sidebar toggle resize event to update chart
    window.addEventListener('resize', () => {
        myChart.resize();
    });
});