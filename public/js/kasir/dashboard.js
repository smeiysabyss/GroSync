// ============================================================
// PROFILE DROPDOWN
// ============================================================
function toggleKasirProfile() {
    const menu = document.getElementById("kasirProfileMenu");
    if (menu) menu.classList.toggle("show");
}

document.addEventListener("click", function (e) {
    const wrap = document.getElementById("kasirProfileWrap");
    if (wrap && !wrap.contains(e.target)) {
        const menu = document.getElementById("kasirProfileMenu");
        if (menu) menu.classList.remove("show");
    }
});

document.addEventListener("keydown", function (e) {
    if (e.key === "Escape") {
        const menu = document.getElementById("kasirProfileMenu");
        if (menu) menu.classList.remove("show");
        if (typeof tutupCartModal === "function") tutupCartModal();
        sembunyikanHasilProduk();
    }
});

// ============================================================
// SEARCH
// ============================================================
document.addEventListener("DOMContentLoaded", function () {
    const searchInput = document.getElementById("kasirSearchInput");
    if (!searchInput) return;

    let debounceTimer = null;

    searchInput.addEventListener("input", function () {
        clearTimeout(debounceTimer);
        const keyword = this.value.trim().toLowerCase();

        // Selalu filter kategori real-time
        filterKategori(keyword);

        // Cari produk via AJAX setelah berhenti mengetik 500ms
        if (keyword.length >= 2) {
            debounceTimer = setTimeout(function () {
                cariProduk(keyword);
            }, 500);
        } else {
            sembunyikanHasilProduk();
        }
    });

    // Enter → langsung cari produk
    searchInput.addEventListener("keydown", function (e) {
        if (e.key === "Enter") {
            clearTimeout(debounceTimer);
            const keyword = this.value.trim().toLowerCase();
            if (keyword.length >= 2) cariProduk(keyword);
        }
    });
});

// ============================================================
// FILTER KATEGORI (real-time)
// ============================================================
function filterKategori(keyword) {
    const cards = document.querySelectorAll(".kasir-category-card");
    let adaHasil = false;

    cards.forEach(function (card) {
        const nama = card.querySelector(".kasir-category-name");
        const teks = nama ? nama.textContent.toLowerCase() : "";
        const cocok = keyword === "" || teks.includes(keyword);
        card.style.display = cocok ? "" : "none";
        if (cocok) adaHasil = true;
    });

    let emptyEl = document.getElementById("searchEmptyDashboard");
    if (!adaHasil && keyword !== "") {
        if (!emptyEl) {
            emptyEl = document.createElement("div");
            emptyEl.id = "searchEmptyDashboard";
            emptyEl.className = "kasir-empty-state";
            emptyEl.style.gridColumn = "1 / -1";
            const grid = document.querySelector(".kasir-category-grid");
            if (grid) grid.appendChild(emptyEl);
        }
        emptyEl.innerHTML =
            '<i class="bi bi-search"></i>' +
            '<p>Kategori "<strong>' +
            keyword +
            '</strong>" tidak ditemukan</p>';
        emptyEl.style.display = "";
    } else if (emptyEl) {
        emptyEl.style.display = "none";
    }
}

// ============================================================
// CARI PRODUK via AJAX → tampilkan dropdown → klik redirect
// ============================================================
function cariProduk(keyword) {
    tampilkanLoading();

    fetch("/kasir/search-produk?q=" + encodeURIComponent(keyword), {
        headers: { "X-Requested-With": "XMLHttpRequest" },
    })
        .then(function (res) {
            return res.json();
        })
        .then(function (data) {
            tampilkanHasilProduk(data, keyword);
        })
        .catch(function () {
            sembunyikanHasilProduk();
        });
}

function tampilkanLoading() {
    let el = getAtauBuatResultsEl();
    el.innerHTML =
        '<div class="produk-search-loading">' +
        '<i class="bi bi-hourglass-split me-2"></i>Mencari produk...' +
        "</div>";
    el.style.display = "block";
}

function tampilkanHasilProduk(list, keyword) {
    let el = getAtauBuatResultsEl();

    if (!list || list.length === 0) {
        el.innerHTML =
            '<div class="produk-search-empty">' +
            'Produk "<strong>' +
            keyword +
            '</strong>" tidak ditemukan</div>';
    } else {
        el.innerHTML = list
            .slice(0, 6)
            .map(function (p) {
                return (
                    '<a href="/kasir/produk/' +
                    p.id_kategori +
                    '" class="produk-search-item">' +
                    '<div class="produk-search-item-info">' +
                    '<div class="produk-search-item-nama">' +
                    p.nama_produk +
                    "</div>" +
                    '<div class="produk-search-item-kategori">' +
                    '<i class="bi bi-grid me-1"></i>' +
                    p.nama_kategori +
                    "</div>" +
                    "</div>" +
                    '<div class="produk-search-item-harga">Rp ' +
                    p.harga_min +
                    "</div>" +
                    "</a>"
                );
            })
            .join("");
    }

    el.style.display = "block";
}

function sembunyikanHasilProduk() {
    const el = document.getElementById("produkSearchResults");
    if (el) el.style.display = "none";
}

function getAtauBuatResultsEl() {
    let el = document.getElementById("produkSearchResults");
    if (!el) {
        el = document.createElement("div");
        el.id = "produkSearchResults";
        el.className = "produk-search-results";
        const wrap = document.querySelector(".kasir-search-wrap");
        if (wrap) wrap.appendChild(el);
    }
    return el;
}

// Tutup hasil saat klik di luar
document.addEventListener("click", function (e) {
    const wrap = document.querySelector(".kasir-search-wrap");
    if (wrap && !wrap.contains(e.target)) sembunyikanHasilProduk();
});
