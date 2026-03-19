/**
 * public/js/kasir/produk.js
 */

// ============================================================
// SEARCH FILTER — pakai id kasirSearchInput (dari layout)
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
// CART PANEL TOGGLE
// ============================================================
function toggleCartPanel() {
    const panel = document.getElementById("cartPanel");
    const backdrop = document.getElementById("cartBackdrop");
    const area = document.getElementById("produkArea");
    const isOpen = panel.classList.contains("open");

    if (isOpen) {
        panel.classList.remove("open");
        backdrop.classList.remove("show");
        area.classList.remove("cart-open");
    } else {
        panel.classList.add("open");
        backdrop.classList.add("show");
        area.classList.add("cart-open");
    }
}

window.toggleCartPanel = toggleCartPanel;

document.addEventListener("keydown", function (e) {
    if (e.key === "Escape") {
        const panel = document.getElementById("cartPanel");
        if (panel && panel.classList.contains("open")) toggleCartPanel();
    }
});

// ============================================================
// MODAL DETAIL PRODUK
// ============================================================
function openDetailModal(id_produk) {
    const p = produkData.find(function (x) {
        return x.id_produk === id_produk;
    });
    if (!p) return;

    const imgEl = document.getElementById("detailGambar");
    if (p.gambar) {
        imgEl.src = p.gambar;
        imgEl.style.display = "";
    } else {
        imgEl.src = "";
        imgEl.style.display = "none";
    }

    document.getElementById("detailNama").textContent = p.nama_produk;

    let badgesHtml =
        '<span class="detail-badge detail-badge-kategori">' +
        p.nama_kategori +
        "</span>";
    p.harga_list.forEach(function (hp) {
        badgesHtml +=
            '<span class="detail-badge detail-badge-satuan">' +
            hp.satuan +
            "</span>";
    });
    document.getElementById("detailBadges").innerHTML = badgesHtml;

    document.getElementById("detailKadaluarsa").textContent =
        p.tanggal_kadaluarsa;
    document.getElementById("detailStok").textContent = p.total_stok;
    document.getElementById("detailDeskripsi").textContent = p.deskripsi || "—";

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
            hp.harga_fmt +
            "</div>" +
            '<div class="tambah-satuan-stok">' +
            (isHabis ? "Stok habis" : "Sisa " + hp.stok) +
            "</div>" +
            "</div>";

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
