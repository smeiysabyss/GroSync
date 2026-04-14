/**
 * Laporan Laba - Owner
 * Grafik Tren Laba 6 Bulan Terakhir
 */

document.addEventListener("DOMContentLoaded", function () {
    // Cek apakah elemen canvas ada
    const canvasElement = document.getElementById("labaChart");
    if (!canvasElement) return;

    // Ambil data dari atribut data di blade
    const chartLabels = window.labaChartData?.labels || [];
    const chartPendapatan = window.labaChartData?.pendapatan || [];
    const chartLaba = window.labaChartData?.laba || [];

    if (chartLabels.length === 0) return;

    // Inisialisasi Chart
    const ctx = canvasElement.getContext("2d");
    new Chart(ctx, {
        type: "line",
        data: {
            labels: chartLabels,
            datasets: [
                {
                    label: "Pendapatan",
                    data: chartPendapatan,
                    borderColor: "#3a6b1a",
                    backgroundColor: "rgba(58, 107, 26, 0.1)",
                    borderWidth: 2,
                    fill: true,
                    tension: 0.3,
                    pointRadius: 3,
                    pointHoverRadius: 5,
                    pointBackgroundColor: "#3a6b1a",
                },
                {
                    label: "Laba Bersih",
                    data: chartLaba,
                    borderColor: "#8ece3f",
                    backgroundColor: "rgba(142, 206, 63, 0.05)",
                    borderWidth: 2,
                    fill: true,
                    tension: 0.3,
                    pointRadius: 3,
                    pointHoverRadius: 5,
                    pointBackgroundColor: "#8ece3f",
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                tooltip: {
                    callbacks: {
                        label: function (context) {
                            let label = context.dataset.label || "";
                            let value = context.raw;
                            return (
                                label + ": Rp " + value.toLocaleString("id-ID")
                            );
                        },
                    },
                },
                legend: {
                    position: "top",
                    labels: {
                        font: {
                            family: "Poppins, sans-serif",
                            size: 11,
                        },
                        boxWidth: 12,
                        usePointStyle: true,
                    },
                },
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function (value) {
                            return "Rp " + value.toLocaleString("id-ID");
                        },
                        font: {
                            family: "Poppins, sans-serif",
                            size: 10,
                        },
                    },
                    grid: {
                        color: "#e5e7eb",
                        drawBorder: true,
                    },
                },
                x: {
                    ticks: {
                        font: {
                            family: "Poppins, sans-serif",
                            size: 10,
                        },
                    },
                    grid: {
                        display: false,
                    },
                },
            },
        },
    });
});
