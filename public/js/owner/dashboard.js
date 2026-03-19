/**
 * public/js/owner/dashboard.js
 * Grafik pendapatan owner dashboard
 *
 * Requires:
 *   - Chart.js (loaded via CDN in blade)
 *   - window.ownerChartData (didefinisikan di dashboard.blade.php)
 */

document.addEventListener("DOMContentLoaded", function () {
    const { dataHarian, dataBulanan, dataTahunan } = window.ownerChartData;

    const ctx = document.getElementById("grafikPendapatan");
    if (!ctx) return;

    const chart = new Chart(ctx.getContext("2d"), {
        type: "bar",
        data: {
            labels: dataHarian.labels,
            datasets: [
                {
                    label: "Pendapatan",
                    data: dataHarian.values,
                    backgroundColor: "rgba(58,107,26,0.15)",
                    borderColor: "#3a6b1a",
                    borderWidth: 2,
                    borderRadius: 8,
                    borderSkipped: false,
                },
            ],
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: (ctx) => "Rp " + ctx.raw.toLocaleString("id-ID"),
                    },
                },
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: { color: "#f3f4f6" },
                    ticks: {
                        font: { family: "Poppins", size: 11 },
                        callback: (val) =>
                            "Rp " +
                            (val >= 1000000
                                ? (val / 1000000).toFixed(1) + "jt"
                                : val >= 1000
                                  ? (val / 1000).toFixed(0) + "rb"
                                  : val),
                    },
                },
                x: {
                    grid: { display: false },
                    ticks: { font: { family: "Poppins", size: 11 } },
                },
            },
        },
    });

    // Expose ke global supaya tombol period bisa akses
    window.ownerChart = chart;
    window.ownerChartData = { dataHarian, dataBulanan, dataTahunan };
});

/**
 * Ganti periode grafik
 * Dipanggil dari onclick di blade
 */
function gantiPeriode(periode, btn) {
    document
        .querySelectorAll(".chart-tab")
        .forEach((b) => b.classList.remove("active"));
    btn.classList.add("active");

    const map = {
        hari: window.ownerChartData.dataHarian,
        bulan: window.ownerChartData.dataBulanan,
        tahun: window.ownerChartData.dataTahunan,
    };

    const d = map[periode];
    window.ownerChart.data.labels = d.labels;
    window.ownerChart.data.datasets[0].data = d.values;
    window.ownerChart.update();
}
