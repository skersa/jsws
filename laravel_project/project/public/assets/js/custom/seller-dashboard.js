document.addEventListener('DOMContentLoaded', function () {

    const raw    = {!! json_encode($chartData ?? []) !!};
    const labels = {!! json_encode($chartLabels ?? []) !!};

    const finalData   = Array.isArray(raw) ? raw : [];
    const finalLabels = labels.length ? labels : Array.from({length: finalData.length}, (_, i) => (i+1)+'.');

    const ctx = document.getElementById('salesChart');
    if (!ctx) return;

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: finalLabels,
            datasets: [{
                label: 'Satış (₺)',
                data: finalData,
                backgroundColor: 'rgba(21,94,239,.2)',
                hoverBackgroundColor: 'rgba(21,94,239,.65)',
                borderRadius: 5,
                borderSkipped: false,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    padding: 10,
                    callbacks: {
                        label: ctx => ' ' + ctx.parsed.y.toLocaleString('tr-TR') + ' ₺'
                    }
                }
            },
            scales: {
                x: {
                    grid: { display: false },
                    ticks: {
                        font: { size: 10 },
                        color: '#94a3b8',
                        maxRotation: 0,
                        autoSkip: true,
                        maxTicksLimit: 10
                    }
                },
                y: {
                    beginAtZero: true,
                    grid: { color: 'rgba(255,255,255,.05)' },
                    ticks: {
                        font: { size: 10 },
                        color: '#94a3b8',
                        callback: v => v >= 1000 ? (v/1000).toFixed(1)+'K' : v
                    }
                }
            }
        }
    });

});
