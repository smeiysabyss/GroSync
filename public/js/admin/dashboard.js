// public/js/admin/dashboard.js

(function () {
    "use strict";

    let activePanel = "produk";

    // ============================================================
    // Switch Panel saat card diklik
    // ============================================================
    window.switchPanel = function (panel) {
        if (panel === activePanel) return;

        // Sembunyikan panel aktif
        document.getElementById("panel-" + activePanel).classList.add("d-none");

        // Nonaktifkan card aktif
        document
            .getElementById("card-" + activePanel)
            .classList.remove("active");

        // Tampilkan panel baru
        const newPanel = document.getElementById("panel-" + panel);
        newPanel.classList.remove("d-none");

        // Aktifkan card baru
        document.getElementById("card-" + panel).classList.add("active");

        // Update header panel
        updatePanelHeader(panel);

        activePanel = panel;
    };

    // ============================================================
    // Update header panel (icon, title, count)
    // ============================================================
    function updatePanelHeader(panel) {
        const cfg = panelConfig[panel];
        const icon = document.getElementById("panelIcon");
        const title = document.getElementById("panelTitle");
        const count = document.getElementById("panelCount");

        // Ganti icon
        icon.className = "bi " + cfg.icon + " panel-header-icon";

        // Ganti warna icon sesuai panel
        const colors = { produk: "#16a34a", stok: "#d97706", exp: "#dc2626" };
        icon.style.color = colors[panel];

        title.textContent = cfg.title;
        count.textContent = cfg.count;

        // Ganti warna badge count
        const badgeColors = {
            produk: "#4a7c2f",
            stok: "#d97706",
            exp: "#dc2626",
        };
        count.style.backgroundColor = badgeColors[panel];
    }

    // ============================================================
    // Inisialisasi saat DOM ready
    // ============================================================
    document.addEventListener("DOMContentLoaded", function () {
        // Set header awal
        updatePanelHeader("produk");
    });
})();
