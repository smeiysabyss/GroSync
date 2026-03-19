// public/js/admin/kategori.js

(function () {
    "use strict";

    // ============================================================
    // Buka Modal Edit
    // ============================================================
    window.openEditModal = function (id, namaKategori) {
        document.getElementById("editNamaKategori").value = namaKategori;

        const form = document.getElementById("formEditKategori");
        form.action = "/admin/kategori/" + id;

        new bootstrap.Modal(
            document.getElementById("modalEditKategori"),
        ).show();
    };

    // ============================================================
    // Buka Modal Hapus
    // ============================================================
    window.confirmDelete = function (id, namaKategori, jumlahProduk) {
        document.getElementById("deleteNamaKategori").textContent =
            namaKategori;

        // Tampilkan warning jika kategori masih punya produk
        const warningEl = document.getElementById("warningProduk");
        const jumlahWarningEl = document.getElementById("jumlahProdukWarning");

        if (jumlahProduk > 0) {
            jumlahWarningEl.textContent = jumlahProduk + " produk";
            warningEl.classList.remove("d-none");
        } else {
            warningEl.classList.add("d-none");
        }

        const form = document.getElementById("formHapusKategori");
        form.action = "/admin/kategori/" + id;

        new bootstrap.Modal(
            document.getElementById("modalHapusKategori"),
        ).show();
    };
})();
