// public/js/admin/harga.js

(function () {
    "use strict";

    let barisIndex = 1; // Index counter untuk baris tambahan

    // ============================================================
    // Tambah Baris Satuan (dynamic)
    // ============================================================
    window.tambahBaris = function () {
        const container = document.getElementById("barisSatuanContainer");
        const idx = barisIndex++;

        // Build options satuan
        let optionsSatuan =
            '<option value="" disabled selected>Satuan</option>';
        satuanOptions.forEach(function (s) {
            optionsSatuan += `<option value="${s.id}">${s.nama}</option>`;
        });

        const html = `
        <div class="baris-satuan" id="baris_${idx}">
            <div class="row g-2 align-items-end mb-2">
                <div class="col-md-3">
                    <label class="form-label">Satuan <span class="text-danger">*</span></label>
                    <select name="rows[${idx}][id_unit]" class="form-select grosync-input" required>
                        ${optionsSatuan}
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Stok <span class="text-danger">*</span></label>
                    <input type="number" name="rows[${idx}][stok]" class="form-control grosync-input"
                           placeholder="0" min="0" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Harga <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text rp-prefix">Rp</span>
                        <input type="number" name="rows[${idx}][harga]" class="form-control grosync-input"
                               placeholder="0" min="0" required>
                    </div>
                </div>
                <div class="col-md-2 d-flex gap-1 justify-content-end">
                    <button type="button" class="btn-row btn-row-add" onclick="tambahBaris()" title="Tambah baris">
                        <i class="bi bi-plus-lg"></i>
                    </button>
                    <button type="button" class="btn-row btn-row-remove" onclick="hapusBaris(this)" title="Hapus baris">
                        <i class="bi bi-dash-lg"></i>
                    </button>
                </div>
            </div>
            <div class="mb-0">
                <input type="text" name="rows[${idx}][catatan]" class="form-control grosync-input"
                       placeholder="Catatan (opsional)">
            </div>
        </div>`;

        container.insertAdjacentHTML("beforeend", html);
        updateTombolHapus();
    };

    // ============================================================
    // Hapus Baris Satuan
    // ============================================================
    window.hapusBaris = function (btn) {
        const baris = btn.closest(".baris-satuan");
        baris.remove();
        updateTombolHapus();
    };

    // Sembunyikan tombol hapus jika hanya tersisa 1 baris
    function updateTombolHapus() {
        const semua = document.querySelectorAll(".baris-satuan");
        semua.forEach(function (baris, i) {
            const hapusBtn = baris.querySelector(".btn-row-remove");
            if (semua.length === 1) {
                hapusBtn.classList.add("d-none");
            } else {
                hapusBtn.classList.remove("d-none");
            }
        });
    }

    // ============================================================
    // Buka Modal Edit
    // ============================================================
    window.openEditModal = function (
        idHarga,
        idProduk,
        idUnit,
        stok,
        harga,
        catatan,
    ) {
        document.getElementById("editIdProduk").value = idProduk;
        document.getElementById("editIdUnit").value = idUnit;
        document.getElementById("editStok").value = stok;
        document.getElementById("editHarga").value = harga;
        document.getElementById("editCatatan").value = catatan;

        const form = document.getElementById("formEditHarga");
        form.action = "/admin/harga/" + idHarga;

        new bootstrap.Modal(document.getElementById("modalEditHarga")).show();
    };

    // ============================================================
    // Buka Modal Hapus
    // ============================================================
    window.confirmDelete = function (idHarga, namaProduk, satuan) {
        document.getElementById("deleteHargaInfo").textContent =
            `Data harga satuan "${satuan}" untuk produk "${namaProduk}" akan dihapus permanen.`;

        const form = document.getElementById("formHapusHarga");
        form.action = "/admin/harga/" + idHarga;

        new bootstrap.Modal(document.getElementById("modalHapusHarga")).show();
    };

    // ============================================================
    // Reset form & baris saat modal tambah ditutup
    // ============================================================
    document.addEventListener("DOMContentLoaded", function () {
        const modalTambah = document.getElementById("modalTambahHarga");
        if (modalTambah) {
            modalTambah.addEventListener("hidden.bs.modal", function () {
                // Hapus semua baris kecuali yang pertama
                const container = document.getElementById(
                    "barisSatuanContainer",
                );
                const semua = container.querySelectorAll(".baris-satuan");
                semua.forEach(function (baris, i) {
                    if (i > 0) baris.remove();
                });
                // Reset baris pertama
                const pertama = container.querySelector(".baris-satuan");
                if (pertama) {
                    pertama
                        .querySelectorAll("input, select")
                        .forEach((el) => (el.value = ""));
                }
                barisIndex = 1;
                updateTombolHapus();
            });
        }
    });
})();
