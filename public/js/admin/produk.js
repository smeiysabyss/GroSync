(function () {
    "use strict";

    // ============================================================
    // SEARCH BAR — filter card produk (bukan tabel)
    // ============================================================
    document.addEventListener("DOMContentLoaded", function () {
        const searchInput = document.getElementById("adminSearchInput");
        if (!searchInput) return;

        searchInput.placeholder = "Cari nama produk...";

        let searchTimer;
        searchInput.addEventListener("input", function () {
            const keyword = this.value.trim().toLowerCase();
            clearTimeout(searchTimer);
            searchTimer = setTimeout(function () {
                filterProdukCard(keyword);
            }, 300);
        });

        searchInput.addEventListener("keydown", function (e) {
            if (e.key === "Escape") {
                this.value = "";
                filterProdukCard("");
            }
        });
    });

    function filterProdukCard(keyword) {
        const cards = document.querySelectorAll(".produk-card");
        let adaHasil = false;

        cards.forEach(function (card) {
            const nama =
                card.querySelector(".produk-nama")?.textContent.toLowerCase() ||
                "";
            const cocok = keyword === "" || nama.includes(keyword);

            // card berada di dalam .col-12
            card.closest(".col-12").style.display = cocok ? "" : "none";
            if (cocok) adaHasil = true;
        });

        // Tampilkan pesan kosong jika tidak ada hasil
        let emptyEl = document.getElementById("searchEmptyProduk");
        const grid = document.querySelector(".row.g-3");

        if (!adaHasil && keyword !== "" && grid) {
            if (!emptyEl) {
                emptyEl = document.createElement("div");
                emptyEl.id = "searchEmptyProduk";
                emptyEl.className = "col-12";
                emptyEl.innerHTML = `
                    <div class="empty-state-full">
                        <i class="bi bi-search"></i>
                        <p>Tidak ada produk untuk "<strong>${keyword}</strong>"</p>
                    </div>`;
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

        const hapusInput = document.getElementById("hapusGambarInput");
        if (hapusInput) hapusInput.value = "1";
    };

    // ============================================================
    // Buka Modal Edit
    // ============================================================
    window.openEditModal = function (id, nama, idKategori, deskripsi, gambar) {
        document.getElementById("editNamaProduk").value = nama;
        document.getElementById("editIdKategori").value = idKategori;
        document.getElementById("editDeskripsi").value = deskripsi;
        document.getElementById("hapusGambarInput").value = "0";

        const form = document.getElementById("formEditProduk");
        form.action = "/admin/produk/" + id;

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

    // ============================================================
    // MODAL DETAIL PRODUK - ADMIN
    // ============================================================
    window.openDetailProduk = function (idProduk) {
        // Tampilkan loading
        const modalBody = document.getElementById("detailProdukBody");
        if (modalBody) {
            modalBody.innerHTML = `
                <div class="text-center py-5">
                    <div class="spinner-border text-success" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2 text-muted">Memuat data produk...</p>
                </div>
            `;
        }

        // Buka modal
        const modal = new bootstrap.Modal(
            document.getElementById("modalDetailProduk"),
        );
        modal.show();

        // Fetch data dari server
        fetch("/admin/produk/" + idProduk + "/detail", {
            headers: {
                "X-Requested-With": "XMLHttpRequest",
                Accept: "application/json",
            },
        })
            .then((response) => response.json())
            .then((data) => {
                renderDetailProduk(data);
            })
            .catch((error) => {
                console.error("Error:", error);
                if (modalBody) {
                    modalBody.innerHTML = `
                        <div class="text-center py-5 text-danger">
                            <i class="bi bi-exclamation-triangle-fill" style="font-size: 2rem;"></i>
                            <p class="mt-2">Gagal memuat data produk. Silakan coba lagi.</p>
                        </div>
                    `;
                }
            });
    };

    function renderDetailProduk(p) {
        const container = document.getElementById("detailProdukBody");
        if (!container) return;

        // Badge kategori
        const kategoriBadge = `
            <span class="detail-badge detail-badge-kategori">
                <i class="bi bi-tag me-1"></i> ${escapeHtml(p.nama_kategori)}
            </span>
        `;

        // Generate tabel harga
        let hargaRows = "";
        if (p.harga_list && p.harga_list.length > 0) {
            p.harga_list.forEach(function (hp) {
                const stokDisplay =
                    hp.stok > 0
                        ? hp.stok.toLocaleString("id-ID")
                        : '<span class="text-danger"><i class="bi bi-exclamation-triangle me-1"></i>Habis</span>';

                hargaRows += `
                    <tr>
                        <td class="detail-harga-satuan">${escapeHtml(hp.satuan)}</td>
                        <td class="detail-harga-beli">${hp.harga_beli_fmt}</td>
                        <td class="detail-harga-jual">${hp.harga_jual_fmt}</td>
                        <td class="detail-harga-stok ${hp.stok <= 0 ? "habis" : ""}">
                            ${stokDisplay}
                        </td>
                        <td class="${hp.margin_class}">${hp.margin}%</td>
                    </tr>
                `;
            });
        } else {
            hargaRows = `
                <tr>
                    <td colspan="5" class="text-center text-muted py-3">Tidak ada data harga</td>
                </tr>
            `;
        }

        // Generate riwayat stok masuk
        let riwayatRows = "";
        if (p.riwayat_stok && p.riwayat_stok.length > 0) {
            p.riwayat_stok.forEach(function (r) {
                const sisaDisplay =
                    r.sisa_stok > 0
                        ? r.sisa_stok.toLocaleString("id-ID")
                        : '<span class="text-danger">Habis</span>';

                riwayatRows += `
                    <tr>
                        <td>${r.tanggal_masuk}</td>
                        <td>${r.jumlah.toLocaleString("id-ID")} ${escapeHtml(r.satuan)}</td>
                        <td>${r.harga_beli_fmt}</td>
                        <td>${r.kadaluarsa !== "-" ? r.kadaluarsa : '<span class="text-muted">—</span>'}</td>
                        <td>${sisaDisplay}</td>
                    </tr>
                `;
            });
        } else {
            riwayatRows = `
                <tr>
                    <td colspan="5" class="text-center text-muted py-3">Belum ada riwayat stok masuk</td>
                </tr>
            `;
        }

        container.innerHTML = `
            <div class="detail-container">
                <!-- KOLOM KIRI: GAMBAR -->
                <div class="detail-gambar-col">
                    <div class="detail-gambar-wrap">
                        ${
                            p.gambar
                                ? `<img src="${p.gambar}" alt="${escapeHtml(p.nama_produk)}" class="detail-gambar">`
                                : `<div class="detail-gambar-placeholder"><i class="bi bi-image"></i></div>`
                        }
                    </div>
                </div>
                
                <!-- KOLOM KANAN: INFO PRODUK -->
                <div class="detail-info-col">
                    <div class="detail-nama-produk">${escapeHtml(p.nama_produk)}</div>
                    
                    <div class="detail-badges">
                        ${kategoriBadge}
                    </div>
                    
                    <div class="detail-deskripsi-section">
                        <div class="detail-deskripsi-label">
                            <i class="bi bi-file-text"></i> Deskripsi
                        </div>
                        <div class="detail-deskripsi">${escapeHtml(p.deskripsi) || '<span class="text-muted">Tidak ada deskripsi</span>'}</div>
                    </div>
                    
                    <div class="detail-harga-section">
                        <div class="detail-harga-label">
                            <i class="bi bi-tags"></i> Informasi Harga & Stok
                        </div>
                        <div class="table-responsive">
                            <table class="detail-harga-table">
                                <thead>
                                    <tr>
                                        <th>Satuan</th>
                                        <th>Harga Beli</th>
                                        <th>Harga Jual</th>
                                        <th>Stok</th>
                                        <th>Margin</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    ${hargaRows}
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                    <div class="detail-riwayat-section">
                        <div class="detail-riwayat-label">
                            <i class="bi bi-clock-history"></i> Riwayat Stok Masuk
                        </div>
                        <div class="table-responsive">
                            <table class="detail-riwayat-table">
                                <thead>
                                    <tr>
                                        <th>Tanggal</th>
                                        <th>Jumlah</th>
                                        <th>Harga Beli</th>
                                        <th>Kadaluarsa</th>
                                        <th>Sisa</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    ${riwayatRows}
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        `;
    }

    function editFromDetail(idProduk) {
        // Tutup modal detail
        const detailModal = bootstrap.Modal.getInstance(
            document.getElementById("modalDetailProduk"),
        );
        if (detailModal) detailModal.hide();

        // Fetch data produk untuk edit
        fetch("/admin/produk/" + idProduk + "/edit-data")
            .then((response) => response.json())
            .then((data) => {
                window.openEditModal(
                    data.id_produk,
                    data.nama_produk,
                    data.id_kategori,
                    data.deskripsi || "",
                    data.gambar || "",
                );
            })
            .catch((error) => {
                console.error("Error:", error);
                // Fallback: reload page dengan parameter edit
                window.location.href = "/admin/produk?edit=" + idProduk;
            });
    }

    function escapeHtml(text) {
        if (!text) return "";
        const div = document.createElement("div");
        div.textContent = text;
        return div.innerHTML;
    }
})();
