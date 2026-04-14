(function () {
    "use strict";

    // ============================================================
    // SEARCH BAR — filter card produk harga
    // ============================================================
    document.addEventListener("DOMContentLoaded", function () {
        const searchInput = document.getElementById("adminSearchInput");
        if (!searchInput) return;

        searchInput.placeholder = "Cari nama produk / kategori...";

        let timer;
        searchInput.addEventListener("input", function () {
            const keyword = this.value.trim().toLowerCase();
            clearTimeout(timer);
            timer = setTimeout(function () {
                filterCard(keyword);
            }, 300);
        });

        searchInput.addEventListener("keydown", function (e) {
            if (e.key === "Escape") {
                this.value = "";
                filterCard("");
            }
        });
    });

    function filterCard(keyword) {
        const cards = document.querySelectorAll(".produk-card");
        let ada = false;

        cards.forEach(function (card) {
            const nama = (
                card.querySelector(".produk-nama")?.textContent || ""
            ).toLowerCase();
            const kategori = (
                card.querySelector(".produk-badge-kategori")?.textContent || ""
            ).toLowerCase();
            const cocok =
                keyword === "" ||
                nama.includes(keyword) ||
                kategori.includes(keyword);
            card.closest(".col-12").style.display = cocok ? "" : "none";
            if (cocok) ada = true;
        });

        let emptyEl = document.getElementById("searchEmptyCard");
        const grid = document.querySelector(".row.g-3");

        if (!ada && keyword !== "" && grid) {
            if (!emptyEl) {
                emptyEl = document.createElement("div");
                emptyEl.id = "searchEmptyCard";
                emptyEl.className = "col-12";
                emptyEl.innerHTML = `<div class="empty-state-full"><i class="bi bi-search"></i><p>Tidak ada produk untuk "<strong>${keyword}</strong>"</p></div>`;
                grid.appendChild(emptyEl);
            } else {
                emptyEl.querySelector("strong").textContent = keyword;
                emptyEl.style.display = "";
            }
        } else if (emptyEl) {
            emptyEl.style.display = "none";
        }
    }

    // ============================================================
    // MODAL TAMBAH STOK BARU
    // ============================================================
    window.bukaModalTambahStok = function (idHargaProduk, namaProduk, satuan) {
        document.getElementById("tambahStokNama").textContent =
            namaProduk + " — " + satuan;

        const form = document.getElementById("formTambahStok");
        form.action = routeTambahStok + "/" + idHargaProduk + "/tambah-stok";
        form.reset();

        const today = new Date().toISOString().split("T")[0];
        document.getElementById("inputTanggalMasuk").value = today;

        new bootstrap.Modal(document.getElementById("modalTambahStok")).show();
    };

    // ============================================================
    // MODAL EDIT BULK
    // ============================================================
    let editIdProduk = null;
    let editBulkIndex = 0;
    let deletedIds = [];

    window.openEditBulkModal = function (idProduk, namaProduk, hargaRows) {
        editIdProduk = idProduk;
        deletedIds = [];
        editBulkIndex = 0;

        document.getElementById("editBulkNamaProduk").textContent = namaProduk;
        document.getElementById("formEditBulk").action =
            routeUpdateBulk + "/" + idProduk + "/update-bulk";

        const container = document.getElementById("editBulkContainer");
        container.innerHTML = "";
        document.getElementById("deletedInputs").innerHTML = "";

        // Debug: lihat data yang masuk
        console.log("Harga Rows:", hargaRows);

        hargaRows.forEach((row, i) => {
            container.insertAdjacentHTML("beforeend", buildEditBaris(i, row));
            editBulkIndex = i + 1;
        });

        updateHapusEdit();
        new bootstrap.Modal(document.getElementById("modalEditBulk")).show();
    };

    function buildEditBaris(idx, row) {
        const idHarga = row ? row.id_harga_produk : "";
        const idUnit = row ? row.id_unit : "";
        const stok = row ? row.stok : "";
        const harga = row ? row.harga : "";
        const hargaJual = row ? row.harga_jual : "";
        const catatan = row ? row.catatan : "";
        // Ambil tanggal kadaluarsa dari row, pastikan formatnya YYYY-MM-DD
        let tanggalKadaluarsa = row ? row.tanggal_kadaluarsa : "";

        // Jika tanggalKadaluarsa ada, pastikan formatnya benar untuk input date
        if (
            tanggalKadaluarsa &&
            tanggalKadaluarsa !== "null" &&
            tanggalKadaluarsa !== ""
        ) {
            // Cek apakah formatnya sudah YYYY-MM-DD
            if (!tanggalKadaluarsa.match(/^\d{4}-\d{2}-\d{2}$/)) {
                // Coba parse dari format lain
                try {
                    const parsed = new Date(tanggalKadaluarsa);
                    if (!isNaN(parsed.getTime())) {
                        tanggalKadaluarsa = parsed.toISOString().split("T")[0];
                    }
                } catch (e) {
                    tanggalKadaluarsa = "";
                }
            }
        } else {
            tanggalKadaluarsa = "";
        }

        console.log("Build row:", idx, "Tanggal:", tanggalKadaluarsa);

        let opts = "";
        satuanOptions.forEach((s) => {
            const sel = String(s.id) === String(idUnit) ? "selected" : "";
            opts += `<option value="${s.id}" ${sel}>${s.nama}</option>`;
        });

        return `
    <div class="baris-satuan" id="edit_baris_${idx}" data-idx="${idx}">
        <input type="hidden" name="rows[${idx}][id_harga_produk]" value="${idHarga}">
        <div class="row g-2 align-items-end mb-2">
            <div class="col-md-2">
                <label class="form-label">Satuan <span class="text-danger">*</span></label>
                <select name="rows[${idx}][id_unit]" class="form-select grosync-input" required>
                    <option value="" disabled ${!idUnit ? "selected" : ""}>Pilih</option>
                    ${opts}
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Stok <span class="text-danger">*</span></label>
                <input type="number" name="rows[${idx}][stok]" class="form-control grosync-input" placeholder="0" min="0" value="${stok}" required>
            </div>
            <div class="col-md-2">
                <label class="form-label">Harga Beli <span class="text-danger">*</span></label>
                <div class="input-group">
                    <span class="input-group-text rp-prefix">Rp</span>
                    <input type="number" name="rows[${idx}][harga]" class="form-control grosync-input" placeholder="0" min="0" value="${harga}" required>
                </div>
            </div>
            <div class="col-md-2">
                <label class="form-label">Harga Jual <span class="text-danger">*</span></label>
                <div class="input-group">
                    <span class="input-group-text rp-prefix">Rp</span>
                    <input type="number" name="rows[${idx}][harga_jual]" class="form-control grosync-input" placeholder="0" min="0" value="${hargaJual}" required>
                </div>
            </div>
            <div class="col-md-2">
                <label class="form-label">Tanggal Kadaluarsa <span class="text-danger">*</span></label>
                <input type="date" name="rows[${idx}][tanggal_kadaluarsa]" class="form-control grosync-input" value="${tanggalKadaluarsa}" required>
            </div>
            <div class="col-md-2 d-flex gap-1 justify-content-end">
                <button type="button" class="btn-row btn-row-remove" onclick="hapusBarisEdit(this, '${idHarga}')"><i class="bi bi-dash-lg"></i></button>
                <button type="button" class="btn-row btn-row-add" onclick="tambahBarisEdit()"><i class="bi bi-plus-lg"></i></button>
            </div>
        </div>
        <div class="mb-0">
            <input type="text" name="rows[${idx}][catatan]" class="form-control grosync-input" placeholder="Catatan (opsional)" value="${catatan}">
        </div>
    </div>`;
    }

    window.tambahBarisEdit = function () {
        const container = document.getElementById("editBulkContainer");
        const idx = editBulkIndex++;

        const newRow = {
            id_harga_produk: null,
            id_unit: null,
            stok: 0,
            harga: 0,
            harga_jual: 0,
            tanggal_kadaluarsa: "",
            catatan: "",
        };

        container.insertAdjacentHTML("beforeend", buildEditBaris(idx, newRow));
        updateHapusEdit();
    };

    window.hapusBarisEdit = function (btn, idHarga) {
        if (idHarga && idHarga !== "" && idHarga !== null) {
            deletedIds.push(idHarga);
            syncDeletedInputs();
        }
        btn.closest(".baris-satuan").remove();
        updateHapusEdit();
    };

    function syncDeletedInputs() {
        const wrap = document.getElementById("deletedInputs");
        wrap.innerHTML = "";
        deletedIds.forEach((id, i) => {
            wrap.insertAdjacentHTML(
                "beforeend",
                `<input type="hidden" name="deleted[${i}]" value="${id}">`,
            );
        });
    }

    function updateHapusEdit() {
        const semua = document.querySelectorAll(
            "#editBulkContainer .baris-satuan",
        );
        semua.forEach((b) => {
            const h = b.querySelector(".btn-row-remove");
            h.classList.toggle("d-none", semua.length === 1);
        });
    }

    // ============================================================
    // MODAL HAPUS SEMUA HARGA PRODUK
    // ============================================================
    window.confirmDeleteProduk = function (idProduk, namaProduk) {
        document.getElementById("deleteNamaProduk").textContent = namaProduk;
        document.getElementById("formHapusProduk").action =
            routeDestroyProduk + "/" + idProduk + "/destroy-produk";
        new bootstrap.Modal(document.getElementById("modalHapusProduk")).show();
    };

    // ============================================================
    // LIHAT SEMUA BATCH (Modal Pop Up)
    // ============================================================
    window.lihatSemuaBatch = function (idHargaProduk, namaProduk, satuan) {
        document.getElementById("lihatBatchNama").innerHTML =
            namaProduk + " — " + satuan;

        fetch("/admin/harga/" + idHargaProduk + "/batches")
            .then((response) => response.json())
            .then((data) => {
                const container = document.getElementById(
                    "lihatBatchContainer",
                );
                container.innerHTML = "";

                if (data.batches.length === 0) {
                    container.innerHTML =
                        '<div class="text-center py-4 text-muted">Tidak ada riwayat stok masuk</div>';
                } else {
                    data.batches.forEach((batch) => {
                        const isHabis = batch.sisa_stok === 0;
                        const sisaStok =
                            batch.sisa_stok !== undefined
                                ? batch.sisa_stok
                                : batch.jumlah;

                        container.innerHTML += `
                            <div class="lihat-batch-item ${isHabis ? "lihat-batch-habis" : ""}" data-id="${batch.id}">
                                <div class="lihat-batch-jumlah">
                                    <strong>${batch.jumlah} ${satuan}</strong>
                                    ${isHabis ? '<span class="badge-batch-habis">Habis</span>' : ""}
                                    ${!isHabis ? '<button class="btn-edit-batch" onclick="editBatch(' + batch.id + ", " + batch.sisa_stok + ", " + batch.harga_beli + ", '" + (batch.tanggal_kadaluarsa_formatted || "") + '\')"><i class="bi bi-pencil-fill"></i></button>' : ""}
                                </div>
                                <div class="lihat-batch-detail">
                                    Beli: Rp ${formatRibuan(batch.harga_beli)} | 
                                    Masuk: ${batch.tanggal_masuk_formatted}
                                    ${batch.tanggal_kadaluarsa_formatted ? ` | Exp: ${batch.tanggal_kadaluarsa_formatted}` : ""}
                                </div>
                                <div class="lihat-batch-sisa">
                                    Sisa stok: <strong>${sisaStok} ${satuan}</strong>
                                </div>
                            </div>
                        `;
                    });
                }

                new bootstrap.Modal(
                    document.getElementById("modalLihatBatch"),
                ).show();
            })
            .catch((error) => {
                console.error("Error:", error);
                alert("Gagal memuat data batch");
            });
    };

    function formatRibuan(angka) {
        if (!angka) return "0";
        return Number(angka).toLocaleString("id-ID");
    }

    // ============================================================
    // EDIT BATCH STOK MASUK
    // ============================================================
    window.editBatch = function (
        id,
        currentSisaStok,
        currentHargaBeli,
        currentKadaluarsa,
    ) {
        const form = document.getElementById("formEditBatch");
        form.action = "/admin/harga/batch/" + id + "/update";

        document.getElementById("editSisaStok").value = currentSisaStok;
        document.getElementById("editHargaBeli").value = currentHargaBeli;

        if (currentKadaluarsa && currentKadaluarsa !== "") {
            // Konversi format tanggal jika perlu
            let formattedDate = currentKadaluarsa;
            if (currentKadaluarsa.match(/\d{2}\/\d{2}\/\d{4}/)) {
                const parts = currentKadaluarsa.split("/");
                formattedDate = `${parts[2]}-${parts[1]}-${parts[0]}`;
            }
            document.getElementById("editTanggalKadaluarsa").value =
                formattedDate;
        } else {
            document.getElementById("editTanggalKadaluarsa").value = "";
        }

        new bootstrap.Modal(document.getElementById("modalEditBatch")).show();
    };

    // Submit edit batch via AJAX
    document
        .getElementById("formEditBatch")
        ?.addEventListener("submit", function (e) {
            e.preventDefault();

            const form = this;
            const url = form.action;
            const formData = new FormData(form);

            fetch(url, {
                method: "POST",
                headers: {
                    "X-CSRF-TOKEN": document.querySelector(
                        'meta[name="csrf-token"]',
                    ).content,
                    Accept: "application/json",
                },
                body: formData,
            })
                .then((response) => response.json())
                .then((data) => {
                    if (data.success) {
                        bootstrap.Modal.getInstance(
                            document.getElementById("modalEditBatch"),
                        ).hide();
                        location.reload();
                    } else {
                        alert("Error: " + data.message);
                    }
                })
                .catch((error) => {
                    console.error("Error:", error);
                    alert("Terjadi kesalahan saat menyimpan data.");
                });
        });
})();
