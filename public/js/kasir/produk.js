// ============================================================
// SEARCH FILTER
// ============================================================
document.addEventListener("DOMContentLoaded", function () {
    const searchInput = document.getElementById("kasirSearchInput");
    if (searchInput) {
        searchInput.addEventListener("input", function () {
            filterProduk(this.value.trim().toLowerCase());
        });
    }
});

function filterProduk(keyword) {
    const cards = document.querySelectorAll(".produk-card");
    let ada = false;

    cards.forEach(function (card) {
        const nama = card.dataset.nama || "";
        const cocok = keyword === "" || nama.includes(keyword);
        card.style.display = cocok ? "" : "none";
        if (cocok) ada = true;
    });

    let emptyEl = document.getElementById("searchEmpty");
    if (!ada && keyword !== "") {
        if (!emptyEl) {
            emptyEl = document.createElement("div");
            emptyEl.id = "searchEmpty";
            emptyEl.className = "produk-empty";
            document.getElementById("produkGrid").appendChild(emptyEl);
        }
        emptyEl.innerHTML =
            '<i class="bi bi-search"></i>' +
            '<p>Produk "<strong>' +
            keyword +
            '</strong>" tidak ditemukan</p>';
        emptyEl.style.display = "";
    } else if (emptyEl) {
        emptyEl.style.display = "none";
    }
}

// ============================================================
// CART MODAL POPUP
// ============================================================
function bukaCartModal() {
    document.getElementById("cartModal").classList.add("show");
    document.getElementById("cartModalBackdrop").classList.add("show");
    document.body.style.overflow = "hidden";
}

function tutupCartModal() {
    document.getElementById("cartModal").classList.remove("show");
    document.getElementById("cartModalBackdrop").classList.remove("show");
    document.body.style.overflow = "";
}

window.toggleCartPanel = function () {
    const modal = document.getElementById("cartModal");
    if (modal.classList.contains("show")) {
        tutupCartModal();
    } else {
        bukaCartModal();
    }
};

document.addEventListener("keydown", function (e) {
    if (e.key === "Escape") tutupCartModal();
});

// ============================================================
// FORMAT RIBUAN (Helper)
// ============================================================
function formatRibuan(angka) {
    if (!angka && angka !== 0) return "0";
    return Number(angka).toLocaleString("id-ID");
}

// ============================================================
// MODAL DETAIL PRODUK (REDESIGN)
// ============================================================
function openDetailModal(id_produk) {
    const p = produkData.find(function (x) {
        return x.id_produk === id_produk;
    });
    if (!p) return;

    // Set Gambar
    const imgEl = document.getElementById("detailGambar");
    const placeholderEl = document.getElementById("detailGambarPlaceholder");

    if (p.gambar && p.gambar !== "") {
        imgEl.src = p.gambar;
        imgEl.style.display = "block";
        placeholderEl.style.display = "none";
    } else {
        imgEl.style.display = "none";
        placeholderEl.style.display = "flex";
    }

    // Set Nama Produk
    document.getElementById("detailNama").textContent = p.nama_produk;

    // Set Badges (Kategori + Satuan)
    let badgesHtml =
        '<span class="detail-badge detail-badge-kategori"><i class="bi bi-tag me-1"></i>' +
        (p.nama_kategori || "-") +
        "</span>";

    p.harga_list.forEach(function (hp) {
        badgesHtml +=
            '<span class="detail-badge detail-badge-satuan"><i class="bi bi-box me-1"></i>' +
            hp.satuan +
            "</span>";
    });
    document.getElementById("detailBadges").innerHTML = badgesHtml;

    // Set Kadaluarsa
    const kadaluarsaEl = document.getElementById("detailKadaluarsa");
    if (p.tanggal_kadaluarsa && p.tanggal_kadaluarsa !== "—") {
        kadaluarsaEl.textContent = p.tanggal_kadaluarsa;
        // Cek apakah kadaluarsa sudah lewat atau hampir
        const expDate = new Date(
            p.tanggal_kadaluarsa.split("-").reverse().join("-"),
        );
        const today = new Date();
        const diffDays = Math.ceil((expDate - today) / (1000 * 60 * 60 * 24));

        if (diffDays < 0) {
            kadaluarsaEl.classList.add("kadaluarsa-warning");
        } else if (diffDays <= 30) {
            kadaluarsaEl.classList.add("kadaluarsa-near");
        }
    } else {
        kadaluarsaEl.textContent = "—";
    }

    // Set Deskripsi
    document.getElementById("detailDeskripsi").textContent =
        p.deskripsi || "Tidak ada deskripsi untuk produk ini.";

    // Set Tabel Harga
    const tableBody = document.getElementById("detailHargaTableBody");
    tableBody.innerHTML = "";

    let totalStok = 0;

    p.harga_list.forEach(function (hp) {
        const isHabis = hp.stok <= 0;
        totalStok += hp.stok;

        const row = document.createElement("tr");
        row.innerHTML = `
            <td class="detail-harga-satuan">${hp.satuan}</td>
            <td class="detail-harga-harga">Rp ${formatRibuan(hp.harga_jual)}</td>
            <td class="detail-harga-stok ${isHabis ? "habis" : ""}">
                ${isHabis ? '<i class="bi bi-exclamation-triangle me-1"></i>Habis' : formatRibuan(hp.stok)}
            </td>
        `;
        tableBody.appendChild(row);
    });

    // Set Total Stok di meta
    const stokEl = document.getElementById("detailStok");
    if (totalStok > 0) {
        stokEl.textContent = formatRibuan(totalStok) + " item tersedia";
        stokEl.style.color = "#3a6b1a";
    } else {
        stokEl.textContent = "Stok Habis";
        stokEl.style.color = "#dc2626";
    }

    // Set tombol tambah ke keranjang
    const btnTambah = document.getElementById("detailBtnTambah");
    if (totalStok <= 0) {
        btnTambah.disabled = true;
        btnTambah.style.opacity = "0.5";
        btnTambah.style.cursor = "not-allowed";
        btnTambah.innerHTML = '<i class="bi bi-cart-plus"></i> Stok Habis';
    } else {
        btnTambah.disabled = false;
        btnTambah.style.opacity = "1";
        btnTambah.style.cursor = "pointer";
        btnTambah.innerHTML =
            '<i class="bi bi-cart-plus"></i> Tambah ke Keranjang';

        // Hapus event listener lama agar tidak double
        const newBtn = btnTambah.cloneNode(true);
        btnTambah.parentNode.replaceChild(newBtn, btnTambah);

        newBtn.addEventListener("click", function (e) {
            e.stopPropagation();
            const modal = bootstrap.Modal.getInstance(
                document.getElementById("modalDetailProduk"),
            );
            if (modal) modal.hide();
            openTambahModal(p.id_produk);
        });
    }

    // Tampilkan modal
    new bootstrap.Modal(document.getElementById("modalDetailProduk")).show();
}

// ============================================================
// MODAL TAMBAH KE KERANJANG
// ============================================================
var selectedIdHarga = null;
var selectedStokMax = 0;

function openTambahModal(id_produk) {
    const p = produkData.find(function (x) {
        return x.id_produk === id_produk;
    });
    if (!p) return;

    selectedIdHarga = null;
    selectedStokMax = 0;
    document.getElementById("tambahQty").value = 1;

    const imgEl = document.getElementById("tambahGambar");
    imgEl.src = p.gambar || "";
    imgEl.style.display = p.gambar ? "" : "none";
    document.getElementById("tambahNama").textContent = p.nama_produk;

    const satuanList = document.getElementById("tambahSatuanList");
    satuanList.innerHTML = "";

    p.harga_list.forEach(function (hp, idx) {
        const isHabis = hp.stok <= 0;
        const isActive = idx === 0 && !isHabis;
        const item = document.createElement("label");

        item.className =
            "tambah-satuan-item" +
            (isActive ? " active" : "") +
            (isHabis ? " disabled" : "");

        // 🔥 PERBAIKAN: Gunakan harga_jual langsung, bukan harga_fmt (tapi harga_fmt juga boleh asal isinya dari harga_jual)
        const hargaTampil = hp.harga_jual
            ? hp.harga_jual
            : hp.harga_fmt
              ? parseFloat(hp.harga_fmt.replace(/[^0-9]/g, ""))
              : 0;
        const hargaFormatted = "Rp " + formatRibuan(hargaTampil);

        item.innerHTML =
            '<input type="radio" class="tambah-satuan-radio" name="satuan_pilih"' +
            ' value="' +
            hp.id_harga_produk +
            '"' +
            ' data-stok="' +
            hp.stok +
            '"' +
            (isActive ? " checked" : "") +
            (isHabis ? " disabled" : "") +
            ">" +
            '<div class="tambah-satuan-label">' +
            '<div class="tambah-satuan-nama">' +
            hp.satuan +
            "</div>" +
            '<div class="tambah-satuan-harga">' +
            hargaFormatted + // ← PASTIKAN INI HARGA JUAL
            "</div>" +
            '<div class="tambah-satuan-stok">' +
            (isHabis ? "Stok habis" : "Sisa " + hp.stok) +
            "</div></div>";

        satuanList.appendChild(item);

        if (isActive) {
            selectedIdHarga = hp.id_harga_produk;
            selectedStokMax = hp.stok;
            document.getElementById("tambahQty").max = hp.stok;
        }
    });

    satuanList.querySelectorAll("input[type=radio]").forEach(function (radio) {
        radio.addEventListener("change", function () {
            satuanList
                .querySelectorAll(".tambah-satuan-item")
                .forEach(function (el) {
                    el.classList.remove("active");
                });
            this.closest(".tambah-satuan-item").classList.add("active");
            selectedIdHarga = parseInt(this.value);
            selectedStokMax = parseInt(this.dataset.stok);
            document.getElementById("tambahQty").max = selectedStokMax;
            document.getElementById("tambahQty").value = 1;
        });
    });

    new bootstrap.Modal(document.getElementById("modalTambahKeranjang")).show();
}

function kurangQty() {
    const input = document.getElementById("tambahQty");
    const val = parseInt(input.value) || 1;
    if (val > 1) input.value = val - 1;
}

function tambahQty() {
    const input = document.getElementById("tambahQty");
    const val = parseInt(input.value) || 1;
    if (val < selectedStokMax) input.value = val + 1;
}

function submitTambah(e) {
    e.preventDefault();

    if (!selectedIdHarga) {
        alert("Pilih satuan terlebih dahulu!");
        return;
    }

    const qty = parseInt(document.getElementById("tambahQty").value);
    if (qty < 1 || qty > selectedStokMax) {
        alert("Jumlah harus antara 1 dan " + selectedStokMax);
        return;
    }

    document.getElementById("tambahIdHarga").value = selectedIdHarga;
    document.getElementById("tambahJumlahHidden").value = qty;
    document.getElementById("formTambahKeranjang").submit();
}
