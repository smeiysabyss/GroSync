document.addEventListener("DOMContentLoaded", function () {
    // ============================================================
    // Auto-submit saat select status / kasir berubah
    // ============================================================
    const filterForm = document.getElementById("formFilterLaporan");
    const selStatus = document.getElementById("filterStatus");
    const selKasir = document.getElementById("filterKasir");

    if (selStatus && filterForm) {
        selStatus.addEventListener("change", function () {
            filterForm.submit();
        });
    }

    if (selKasir && filterForm) {
        selKasir.addEventListener("change", function () {
            filterForm.submit();
        });
    }

    // ============================================================
    // Validasi: tanggal "sampai" tidak boleh kurang dari "dari"
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
