(function () {
    "use strict";

    let activePanel = "produk";

    // ============================================================
    // Switch Panel saat tab diklik
    // ============================================================
    window.switchPanel = function (panel) {
        if (panel === activePanel) return;

        // Sembunyikan panel aktif
        document.getElementById("panel-" + activePanel).classList.add("d-none");

        // Nonaktifkan tab aktif
        const activeTab = document.querySelector(".panel-tab.active");
        if (activeTab) activeTab.classList.remove("active");

        // Tampilkan panel baru
        const newPanel = document.getElementById("panel-" + panel);
        newPanel.classList.remove("d-none");

        // Aktifkan tab baru
        const newTab = document.querySelector(
            `.panel-tab[data-panel="${panel}"]`,
        );
        if (newTab) newTab.classList.add("active");

        activePanel = panel;
    };

    // ============================================================
    // Inisialisasi saat DOM ready
    // ============================================================
    document.addEventListener("DOMContentLoaded", function () {
        // Pastikan tab yang sesuai dengan panel aktif
        const activeTab = document.querySelector(
            `.panel-tab[data-panel="${activePanel}"]`,
        );
        if (activeTab) activeTab.classList.add("active");
    });
})();
