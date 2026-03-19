// public/js/admin/produk.js

(function () {
    "use strict";

    // ============================================================
    // Preview Gambar saat file dipilih
    // ============================================================
    window.previewGambar = function (input, previewId) {
        const wrapId = previewId + "Wrap";
        const wrap = document.getElementById(wrapId);
        const img = document.getElementById(previewId);

        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function (e) {
                img.src = e.target.result;
                wrap.classList.remove("d-none");
            };
            reader.readAsDataURL(input.files[0]);
        }
    };

    // ============================================================
    // Hapus Preview Gambar
    // ============================================================
    window.hapusPreview = function (inputId, wrapId) {
        const input = document.getElementById(inputId);
        const wrap = document.getElementById(wrapId);

        input.value = "";
        wrap.classList.add("d-none");

        // Jika modal edit, set flag hapus gambar
        const hapusInput = document.getElementById("hapusGambarInput");
        if (hapusInput) hapusInput.value = "1";
    };

    // ============================================================
    // Buka Modal Edit — isi field dengan data produk
    // ============================================================
    window.openEditModal = function (
        id,
        nama,
        idKategori,
        tanggal,
        deskripsi,
        gambar,
    ) {
        document.getElementById("editNamaProduk").value = nama;
        document.getElementById("editIdKategori").value = idKategori;
        document.getElementById("editTanggalKadaluarsa").value = tanggal;
        document.getElementById("editDeskripsi").value = deskripsi;
        document.getElementById("hapusGambarInput").value = "0";

        // Set action form
        const form = document.getElementById("formEditProduk");
        form.action = "/admin/produk/" + id;

        // Preview gambar jika ada
        const previewWrap = document.getElementById("previewEditWrap");
        const previewImg = document.getElementById("previewEdit");
        const fileInput = document.getElementById("gambarEdit");

        fileInput.value = "";

        if (gambar) {
            previewImg.src = "/storage/" + gambar;
            previewWrap.classList.remove("d-none");
        } else {
            previewImg.src = "";
            previewWrap.classList.add("d-none");
        }

        new bootstrap.Modal(document.getElementById("modalEditProduk")).show();
    };

    // ============================================================
    // Buka Modal Hapus
    // ============================================================
    window.confirmDelete = function (id, nama) {
        document.getElementById("deleteNamaProduk").textContent = nama;

        const form = document.getElementById("formHapusProduk");
        form.action = "/admin/produk/" + id;

        new bootstrap.Modal(document.getElementById("modalHapusProduk")).show();
    };
})();
