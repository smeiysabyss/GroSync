// public/js/admin/satuan.js

(function () {
    "use strict";

    // Buka Modal Edit
    window.openEditModal = function (id, namaSatuan) {
        document.getElementById("editNamaSatuan").value = namaSatuan;

        const form = document.getElementById("formEditSatuan");
        form.action = "/admin/satuan/" + id;

        new bootstrap.Modal(document.getElementById("modalEditSatuan")).show();
    };

    // Buka Modal Hapus
    window.confirmDelete = function (id, namaSatuan) {
        document.getElementById("deleteNamaSatuan").textContent = namaSatuan;

        const form = document.getElementById("formHapusSatuan");
        form.action = "/admin/satuan/" + id;

        new bootstrap.Modal(document.getElementById("modalHapusSatuan")).show();
    };
})();
