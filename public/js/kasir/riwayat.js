(function () {
    "use strict";

    function formatRp(angka) {
        return "Rp " + Number(angka).toLocaleString("id-ID");
    }

    // ============================================================
    // Override search bar dari dashboard.js
    // Ganti handler dengan filter tabel riwayat
    // ============================================================
    document.addEventListener("DOMContentLoaded", function () {
        const searchInput = document.getElementById("kasirSearchInput");
        if (!searchInput) return;

        // Ganti placeholder
        searchInput.placeholder = "Cari no. transaksi / pelanggan...";

        // Clone element untuk hapus semua event listener lama dari dashboard.js
        const newInput = searchInput.cloneNode(true);
        searchInput.parentNode.replaceChild(newInput, searchInput);

        // Sembunyikan dropdown hasil produk jika muncul
        const resultsEl = document.getElementById("produkSearchResults");
        if (resultsEl) resultsEl.style.display = "none";

        // Pasang handler baru untuk filter tabel
        let searchTimer;
        newInput.addEventListener("input", function () {
            clearTimeout(searchTimer);
            const keyword = this.value.trim().toLowerCase();
            searchTimer = setTimeout(function () {
                filterTabel(keyword);
            }, 300);
        });

        // Clear filter saat input dikosongkan
        newInput.addEventListener("keydown", function (e) {
            if (e.key === "Escape") {
                this.value = "";
                filterTabel("");
            }
        });
    });

    // ============================================================
    // Filter tabel riwayat
    // ============================================================
    function filterTabel(keyword) {
        const rows = document.querySelectorAll(
            ".rw-table tbody tr:not(#searchEmptyRow)",
        );
        const tbody = document.querySelector(".rw-table tbody");
        let ada = false;

        rows.forEach(function (row) {
            if (row.querySelector(".rw-empty")) return; // skip baris empty state

            const nomor = (
                row.querySelector(".rw-nomor")?.textContent || ""
            ).toLowerCase();
            const pelanggan = (row.cells[2]?.textContent || "").toLowerCase();
            const cocok =
                keyword === "" ||
                nomor.includes(keyword) ||
                pelanggan.includes(keyword);

            row.style.display = cocok ? "" : "none";
            if (cocok) ada = true;
        });

        // Baris pesan tidak ada hasil
        let emptyRow = document.getElementById("searchEmptyRow");
        if (!ada && keyword !== "") {
            if (!emptyRow) {
                emptyRow = document.createElement("tr");
                emptyRow.id = "searchEmptyRow";
                tbody.appendChild(emptyRow);
            }
            emptyRow.innerHTML = `
                <td colspan="10">
                    <div class="rw-empty">
                        <div class="rw-empty-icon"><i class="bi bi-search"></i></div>
                        <div class="rw-empty-text">Tidak ada hasil untuk "<strong>${keyword}</strong>"</div>
                    </div>
                </td>`;
            emptyRow.style.display = "";
        } else if (emptyRow) {
            emptyRow.style.display = "none";
        }
    }

    // ============================================================
    // Modal Detail
    // ============================================================
    const overlay = document.getElementById("overlayDetail");
    const modal = document.getElementById("modalDetail");
    const btnTutup = document.getElementById("btnTutupDetail");

    function bukaModal() {
        overlay.classList.add("active");
        modal.classList.add("active");
    }

    function tutupModal() {
        overlay.classList.remove("active");
        modal.classList.remove("active");
    }

    btnTutup.addEventListener("click", tutupModal);
    overlay.addEventListener("click", tutupModal);

    document.addEventListener("keydown", function (e) {
        if (e.key === "Escape") tutupModal();
    });

    // ============================================================
    // Delegasi klik tombol detail & struk
    // ============================================================
    document
        .querySelector(".rw-table tbody")
        .addEventListener("click", function (e) {
            const btnDetail = e.target.closest(".rw-btn-detail");
            const btnStruk = e.target.closest(".rw-btn-struk");

            if (btnDetail) {
                const trx = JSON.parse(btnDetail.dataset.trx);

                document.getElementById("detailNomor").textContent = trx.nomor;

                const infoItems = [
                    { label: "Pelanggan", value: trx.pelanggan },
                    { label: "Tanggal", value: trx.waktu },
                    { label: "Total", value: formatRp(trx.total) },
                    { label: "Uang Bayar", value: formatRp(trx.bayar) },
                    { label: "Kembalian", value: formatRp(trx.kembali) },
                    {
                        label: "Status",
                        value:
                            trx.status.charAt(0).toUpperCase() +
                            trx.status.slice(1),
                    },
                ];

                document.getElementById("detailInfoGrid").innerHTML = infoItems
                    .map(
                        (item) => `
                    <div class="rw-detail-info-item">
                        <div class="rw-detail-info-label">${item.label}</div>
                        <div class="rw-detail-info-value">${item.value}</div>
                    </div>
                `,
                    )
                    .join("");

                document.getElementById("detailItems").innerHTML = trx.items
                    .map(
                        (item) => `
                    <tr>
                        <td>${item.nama}</td>
                        <td>${item.satuan}</td>
                        <td class="text-center">${item.jumlah}</td>
                        <td class="text-end">${formatRp(item.subtotal)}</td>
                    </tr>
                `,
                    )
                    .join("");

                document.getElementById("detailTotal").textContent = formatRp(
                    trx.total,
                );
                bukaModal();
            }

            if (btnStruk) {
                const id = btnStruk.dataset.id;
                document.getElementById("iframeStruk").src =
                    `/kasir/transaksi/${id}/struk`;
            }
        });
})();
