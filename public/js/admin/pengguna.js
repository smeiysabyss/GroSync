(function () {
    "use strict";

    // ============================================================
    // SEARCH BAR — filter tabel pengguna
    // ============================================================
    document.addEventListener("DOMContentLoaded", function () {
        const searchInput = document.getElementById("adminSearchInput");
        if (!searchInput) return;

        searchInput.placeholder = "Cari username / email / role...";

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

        // Auto-dismiss alert
        document.querySelectorAll(".alert").forEach(function (alert) {
            setTimeout(function () {
                bootstrap.Alert.getOrCreateInstance(alert).close();
            }, 4000);
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
    // Buka Modal Edit
    // ============================================================
    window.openEditModal = function (id, email, username, role) {
        document.getElementById("editEmail").value = email;
        document.getElementById("editUsername").value = username;
        document.getElementById("editRole").value = role;
        document.getElementById("passwordEdit").value = "";

        document.getElementById("formEditPengguna").action =
            "/admin/pengguna/" + id;

        new bootstrap.Modal(
            document.getElementById("modalEditPengguna"),
        ).show();
    };

    // ============================================================
    // Buka Modal Hapus
    // ============================================================
    window.confirmDelete = function (id, username) {
        document.getElementById("deleteUserName").textContent = username;
        document.getElementById("formHapusPengguna").action =
            "/admin/pengguna/" + id;
        new bootstrap.Modal(
            document.getElementById("modalHapusPengguna"),
        ).show();
    };
})();
