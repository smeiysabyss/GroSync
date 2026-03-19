/**
 * public/js/owner/produk.js
 * Filter & interaksi halaman Data Produk owner
 */

document.addEventListener("DOMContentLoaded", function () {
    // ============================================================
    // Auto-submit form filter saat select kategori/satuan berubah
    // ============================================================
    const filterForm = document.getElementById("formFilterProduk");
    const selKategori = document.getElementById("filterKategori");
    const selSatuan = document.getElementById("filterSatuan");

    if (selKategori && filterForm) {
        selKategori.addEventListener("change", function () {
            filterForm.submit();
        });
    }

    if (selSatuan && filterForm) {
        selSatuan.addEventListener("change", function () {
            filterForm.submit();
        });
    }

    // ============================================================
    // Search input — submit setelah berhenti mengetik (debounce)
    // ============================================================
    const searchInput = document.getElementById("searchProduk");
    let searchTimer;

    if (searchInput && filterForm) {
        searchInput.addEventListener("input", function () {
            clearTimeout(searchTimer);
            searchTimer = setTimeout(function () {
                filterForm.submit();
            }, 500);
        });
    }
});
