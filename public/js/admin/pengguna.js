(function () {
    "use strict";

    // ============================================================
    // Toggle Password Visibility
    // ============================================================
    window.togglePassword = function (inputId, btn) {
        const input = document.getElementById(inputId);
        const icon = btn.querySelector("i");

        if (input.type === "password") {
            input.type = "text";
            icon.classList.replace("bi-eye", "bi-eye-slash");
        } else {
            input.type = "password";
            icon.classList.replace("bi-eye-slash", "bi-eye");
        }
    };

    // ============================================================
    // Buka Modal Edit — isi field dengan data user
    // ============================================================
    window.openEditModal = function (id, email, username, role) {
        // Isi form
        document.getElementById("editEmail").value = email;
        document.getElementById("editUsername").value = username;
        document.getElementById("editRole").value = role;
        document.getElementById("passwordEdit").value = "";

        // Set action form ke route update
        const form = document.getElementById("formEditPengguna");
        form.action = "/admin/pengguna/" + id;

        // Tampilkan modal
        const modal = new bootstrap.Modal(
            document.getElementById("modalEditPengguna"),
        );
        modal.show();
    };

    // ============================================================
    // Buka Modal Hapus — konfirmasi dengan nama user
    // ============================================================
    window.confirmDelete = function (id, username) {
        document.getElementById("deleteUserName").textContent = username;

        const form = document.getElementById("formHapusPengguna");
        form.action = "/admin/pengguna/" + id;

        const modal = new bootstrap.Modal(
            document.getElementById("modalHapusPengguna"),
        );
        modal.show();
    };

    // ============================================================
    // Auto-dismiss alert setelah 4 detik
    // ============================================================
    document.addEventListener("DOMContentLoaded", function () {
        const alerts = document.querySelectorAll(".alert");
        alerts.forEach(function (alert) {
            setTimeout(function () {
                const bsAlert = bootstrap.Alert.getOrCreateInstance(alert);
                bsAlert.close();
            }, 4000);
        });
    });
})();
