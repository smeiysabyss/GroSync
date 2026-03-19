/**
 * public/js/owner/log.js
 * Filter halaman Log Aktivitas owner
 */

document.addEventListener("DOMContentLoaded", function () {
    // ============================================================
    // Auto-submit saat select modul / user berubah
    // ============================================================
    const filterForm = document.getElementById("formFilterLog");
    const selModul = document.getElementById("filterModul");
    const selUser = document.getElementById("filterUser");

    if (selModul && filterForm) {
        selModul.addEventListener("change", function () {
            filterForm.submit();
        });
    }

    if (selUser && filterForm) {
        selUser.addEventListener("change", function () {
            filterForm.submit();
        });
    }

    // ============================================================
    // Validasi tanggal
    // ============================================================
    const inputDari = document.getElementById("filterDari");
    const inputSampai = document.getElementById("filterSampai");

    if (inputDari && inputSampai) {
        inputSampai.addEventListener("change", function () {
            if (inputDari.value && this.value < inputDari.value) {
                this.value = inputDari.value;
            }
        });

        inputDari.addEventListener("change", function () {
            if (inputSampai.value && inputSampai.value < this.value) {
                inputSampai.value = this.value;
            }
        });
    }
});
