(function () {
    "use strict";

    // ============================================================
    // SEARCH BAR — filter tabel satuan
    // ============================================================
    document.addEventListener("DOMContentLoaded", function () {
        const searchInput = document.getElementById("adminSearchInput");
        if (!searchInput) return;

        searchInput.placeholder = "Cari nama satuan...";

        let timer;
        searchInput.addEventListener("input", function () {
            const keyword = this.value.trim().toLowerCase();
            clearTimeout(timer);
            timer = setTimeout(function () {
                filterTabel(keyword);
            }, 300);
        });

        searchInput.addEventListener("keydown", function (e) {
            if (e.key === "Escape") {
                this.value = "";
                filterTabel("");
            }
        });
    });

    function filterTabel(keyword) {
        const rows = document.querySelectorAll(".grosync-table tbody tr");
        const tbody = document.querySelector(".grosync-table tbody");
        let ada = false;

        rows.forEach(function (row) {
            if (row.id === "searchEmpty") return;
            if (row.querySelector(".empty-state")) return;
            const teks = Array.from(row.cells)
                .map((td) => td.textContent.trim().toLowerCase())
                .join(" ");
            const cocok = keyword === "" || teks.includes(keyword);
            row.style.display = cocok ? "" : "none";
            if (cocok) ada = true;
        });

        let emptyRow = document.getElementById("searchEmpty");
        if (!ada && keyword !== "") {
            if (!emptyRow) {
                emptyRow = document.createElement("tr");
                emptyRow.id = "searchEmpty";
                emptyRow.innerHTML = `<td colspan="99" class="text-center py-5"><div class="empty-state"><i class="bi bi-search" style="font-size:2.5rem;opacity:0.3;display:block;margin-bottom:10px;"></i><p>Tidak ada hasil untuk "<strong>${keyword}</strong>"</p></div></td>`;
                tbody.appendChild(emptyRow);
            } else {
                emptyRow.querySelector("strong").textContent = keyword;
                emptyRow.style.display = "";
            }
        } else if (emptyRow) {
            emptyRow.style.display = "none";
        }
    }

    // ============================================================
    // Buka Modal Edit
    // ============================================================
    window.openEditModal = function (id, namaSatuan) {
        document.getElementById("editNamaSatuan").value = namaSatuan;
        document.getElementById("formEditSatuan").action =
            "/admin/satuan/" + id;
        new bootstrap.Modal(document.getElementById("modalEditSatuan")).show();
    };

    // ============================================================
    // Buka Modal Hapus
    // ============================================================
    window.confirmDelete = function (id, namaSatuan) {
        document.getElementById("deleteNamaSatuan").textContent = namaSatuan;
        document.getElementById("formHapusSatuan").action =
            "/admin/satuan/" + id;
        new bootstrap.Modal(document.getElementById("modalHapusSatuan")).show();
    };
})();
