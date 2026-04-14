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
    }
});

// ============================================================
// SEARCH (filter kategori saja)
// ============================================================
document.addEventListener("DOMContentLoaded", function () {
    const searchInput = document.getElementById("kasirSearchInput");
    if (!searchInput) return;

    searchInput.addEventListener("input", function () {
        const keyword = this.value.trim().toLowerCase();
        filterKategori(keyword);
    });
});

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
// CART MODAL
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
