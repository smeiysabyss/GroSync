/**
 * public/js/kasir/riwayat.js
 */

(function () {
    "use strict";

    function formatRp(angka) {
        return "Rp " + Number(angka).toLocaleString("id-ID");
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

    // Tombol X
    btnTutup.addEventListener("click", tutupModal);

    // Klik overlay
    overlay.addEventListener("click", tutupModal);

    // Escape
    document.addEventListener("keydown", (e) => {
        if (e.key === "Escape") tutupModal();
    });

    // Delegasi klik tombol detail
    document
        .querySelector(".rw-table tbody")
        .addEventListener("click", function (e) {
            const btnDetail = e.target.closest(".rw-btn-detail");
            const btnStruk = e.target.closest(".rw-btn-struk");

            // ── Lihat Detail ──
            if (btnDetail) {
                const trx = JSON.parse(btnDetail.dataset.trx);

                document.getElementById("detailNomor").textContent = trx.nomor;

                const infoItems = [
                    { label: "Pelanggan", value: trx.pelanggan },
                    { label: "Waktu", value: trx.waktu },
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

            // ── Cetak Struk ──
            if (btnStruk) {
                const id = btnStruk.dataset.id;
                document.getElementById("iframeStruk").src =
                    `/kasir/transaksi/${id}/struk`;
            }
        });
})();
