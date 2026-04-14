// ============================================================
// STATE
// ============================================================
var trxIdResult = null;

// ============================================================
// BUKA / TUTUP MODAL
// ============================================================
function bukaModalTrx() {
    if (typeof trxTotal === "undefined" || trxTotal <= 0) {
        alert("Keranjang masih kosong!");
        return;
    }

    document.getElementById("trxBackdrop").classList.add("show");
    setTimeout(function () {
        document.getElementById("trxModal").classList.add("show");
    }, 10);

    // Reset form
    document.getElementById("trxUangBayar").value = "";
    document.getElementById("trxNamaPelanggan").value = "";
    document.getElementById("trxKembalianVal").textContent = "—";
    document.getElementById("trxKembalianVal").className = "trx-kembalian-val";
    document.getElementById("trxKembalianWrap").className =
        "trx-kembalian-wrap";
    document.getElementById("trxAlertKurang").style.display = "none";
    document.getElementById("trxBtnBayar").disabled = true;
    document.getElementById("trxBtnBayar").innerHTML =
        '<i class="bi bi-check-circle me-1"></i> Bayar';

    setTimeout(function () {
        document.getElementById("trxUangBayar").focus();
    }, 200);
}

function tutupModalTrx() {
    document.getElementById("trxModal").classList.remove("show");
    document.getElementById("trxBackdrop").classList.remove("show");
}

// Tutup dengan Escape
document.addEventListener("keydown", function (e) {
    if (e.key === "Escape") tutupModalTrx();
});

// ============================================================
// HITUNG KEMBALIAN REAL-TIME
// ============================================================
function hitungKembalian() {
    var bayar = parseFloat(document.getElementById("trxUangBayar").value) || 0;
    var kembalian = bayar - trxTotal;

    var valEl = document.getElementById("trxKembalianVal");
    var wrapEl = document.getElementById("trxKembalianWrap");
    var alertEl = document.getElementById("trxAlertKurang");
    var btnBayar = document.getElementById("trxBtnBayar");

    if (bayar <= 0) {
        valEl.textContent = "—";
        valEl.className = "trx-kembalian-val";
        wrapEl.className = "trx-kembalian-wrap";
        alertEl.style.display = "none";
        btnBayar.disabled = true;
        return;
    }

    if (kembalian < 0) {
        valEl.textContent = "- Rp " + formatRupiah(Math.abs(kembalian));
        valEl.className = "trx-kembalian-val negatif";
        wrapEl.className = "trx-kembalian-wrap negatif";
        alertEl.style.display = "flex";
        btnBayar.disabled = true;
    } else {
        valEl.textContent = "Rp " + formatRupiah(kembalian);
        valEl.className = "trx-kembalian-val positif";
        wrapEl.className = "trx-kembalian-wrap positif";
        alertEl.style.display = "none";
        btnBayar.disabled = false;
    }
}

// ============================================================
// SUBMIT via AJAX
// ============================================================
document.addEventListener("DOMContentLoaded", function () {
    // Submit form transaksi
    var form = document.getElementById("formTrx");
    if (form) {
        form.addEventListener("submit", function (e) {
            e.preventDefault();

            var btnBayar = document.getElementById("trxBtnBayar");
            btnBayar.disabled = true;
            btnBayar.innerHTML =
                '<i class="bi bi-hourglass-split me-1"></i> Memproses...';

            fetch(form.action, {
                method: "POST",
                headers: { "X-Requested-With": "XMLHttpRequest" },
                body: new FormData(form),
            })
                .then(function (res) {
                    return res.json();
                })
                .then(function (data) {
                    if (data.success) {
                        trxIdResult = data.id_transaksi;
                        tutupModalTrx();
                        tampilkanSukses(data);
                    } else {
                        alert(data.message || "Terjadi kesalahan, coba lagi.");
                        btnBayar.disabled = false;
                        btnBayar.innerHTML =
                            '<i class="bi bi-check-circle me-1"></i> Bayar';
                    }
                })
                .catch(function () {
                    alert("Gagal terhubung ke server.");
                    btnBayar.disabled = false;
                    btnBayar.innerHTML =
                        '<i class="bi bi-check-circle me-1"></i> Bayar';
                });
        });
    }

    // Tombol CHECKOUT di cart panel → buka modal
    var checkoutBtn = document.querySelector(".cart-checkout-btn");
    if (checkoutBtn) {
        checkoutBtn.addEventListener("click", function (e) {
            e.preventDefault();
            bukaModalTrx();
        });
    }
});

// ============================================================
// ALERT SUKSES
// ============================================================
function tampilkanSukses(data) {
    document.getElementById("trxSuccessMsg").textContent =
        "Nomor: " +
        data.nomor_unik +
        " · Kembalian: Rp " +
        formatRupiah(data.kembalian);

    var cetakCheckbox = document.getElementById("trxCetakStruk");
    var btnStruk = document.getElementById("trxBtnStruk");
    if (cetakCheckbox && !cetakCheckbox.checked) {
        btnStruk.style.display = "none";
    } else {
        btnStruk.style.display = "";
    }

    document.getElementById("trxSuccessBackdrop").classList.add("show");
    setTimeout(function () {
        document.getElementById("trxSuccessModal").classList.add("show");
    }, 10);
}

function cetakStruk() {
    if (trxIdResult) {
        var iframe = document.createElement("iframe");
        iframe.style.display = "none";
        iframe.src = "/kasir/transaksi/" + trxIdResult + "/struk";
        document.body.appendChild(iframe);

        setTimeout(function () {
            document.body.removeChild(iframe);
        }, 10000);
    }
    lanjutTransaksi();
}

function lanjutTransaksi() {
    document.getElementById("trxSuccessModal").classList.remove("show");
    document.getElementById("trxSuccessBackdrop").classList.remove("show");
    window.location.reload();
}

// ============================================================
// HELPER
// ============================================================
function formatRupiah(angka) {
    return Math.round(angka).toLocaleString("id-ID");
}
