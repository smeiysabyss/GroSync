(function () {
    "use strict";

    // ============================================================
    // SEARCH BAR — filter tabel kategori
    // ============================================================
    document.addEventListener("DOMContentLoaded", function () {
        const searchInput = document.getElementById("adminSearchInput");
        if (!searchInput) return;

        searchInput.placeholder = "Cari nama kategori...";

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
    // Preview Gambar — dipakai untuk modal Tambah & Edit
    // ============================================================
    window.previewGambar = function (input, previewId, wrapId, uploadBoxId) {
        const file = input.files[0];
        if (!file) return;
        const reader = new FileReader();
        reader.onload = function (e) {
            document.getElementById(previewId).src = e.target.result;
            document.getElementById(wrapId).style.display = "block";
            document.getElementById(uploadBoxId).style.display = "none";
        };
        reader.readAsDataURL(file);
    };

    window.hapusPreviewTambah = function () {
        document.getElementById("previewTambah").src = "";
        document.getElementById("previewTambahWrap").style.display = "none";
        document.getElementById("uploadBoxTambah").style.display = "block";
        document.getElementById("gambarTambah").value = "";
    };

    window.hapusPreviewEdit = function () {
        document.getElementById("previewEdit").src = "";
        document.getElementById("previewEditWrap").style.display = "none";
        document.getElementById("uploadBoxEdit").style.display = "block";
        document.getElementById("gambarEdit").value = "";
    };

    document
        .getElementById("modalTambahKategori")
        .addEventListener("hidden.bs.modal", function () {
            hapusPreviewTambah();
        });

    window.openEditModal = function (id, namaKategori, gambarUrl) {
        document.getElementById("editNamaKategori").value = namaKategori;
        document.getElementById("formEditKategori").action =
            "/admin/kategori/" + id;
        document.getElementById("gambarEdit").value = "";

        if (gambarUrl) {
            document.getElementById("previewEdit").src = gambarUrl;
            document.getElementById("previewEditWrap").style.display = "block";
            document.getElementById("uploadBoxEdit").style.display = "none";
        } else {
            document.getElementById("previewEdit").src = "";
            document.getElementById("previewEditWrap").style.display = "none";
            document.getElementById("uploadBoxEdit").style.display = "block";
        }

        new bootstrap.Modal(
            document.getElementById("modalEditKategori"),
        ).show();
    };

    window.confirmDelete = function (id, namaKategori, jumlahProduk) {
        document.getElementById("deleteNamaKategori").textContent =
            namaKategori;

        const warningEl = document.getElementById("warningProduk");
        const jumlahWarningEl = document.getElementById("jumlahProdukWarning");

        if (jumlahProduk > 0) {
            jumlahWarningEl.textContent = jumlahProduk + " produk";
            warningEl.classList.remove("d-none");
        } else {
            warningEl.classList.add("d-none");
        }

        document.getElementById("formHapusKategori").action =
            "/admin/kategori/" + id;
        new bootstrap.Modal(
            document.getElementById("modalHapusKategori"),
        ).show();
    };
})();
