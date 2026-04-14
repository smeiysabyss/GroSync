{{-- ============================================================
     MODAL: Transaksi / Checkout
     ============================================================ --}}
<div class="trx-backdrop" id="trxBackdrop" onclick="tutupModalTrx()"></div>

<div class="trx-modal" id="trxModal">

    {{-- Header --}}
    <div class="trx-modal-header">
        <div class="trx-modal-header-left">
            <div class="trx-modal-icon"><i class="bi bi-receipt"></i></div>
            <div>
                <div class="trx-modal-title">Transaksi</div>
                <div class="trx-modal-subtitle">Selesaikan pembayaran</div>
            </div>
        </div>
        <button class="trx-modal-close" onclick="tutupModalTrx()">
            <i class="bi bi-x-lg"></i>
        </button>
    </div>

    <form action="{{ route('kasir.transaksi.proses') }}" method="POST" id="formTrx">
        @csrf

        <div class="trx-modal-body">

            {{-- Ringkasan --}}
            <div class="trx-summary-box">
                @php $keranjang = session('keranjang', []); @endphp
                <div class="trx-summary-row">
                    <span class="trx-summary-label">Jumlah Produk</span>
                    <span class="trx-summary-val">{{ collect($keranjang)->sum('jumlah') }} item</span>
                </div>
                <div class="trx-summary-divider"></div>
                <div class="trx-summary-row trx-summary-total">
                    <span class="trx-summary-label">Subtotal</span>
                    <span class="trx-summary-val trx-total-val">
                        Rp {{ number_format(collect($keranjang)->sum('subtotal'), 0, ',', '.') }}
                    </span>
                </div>
            </div>

            {{-- Nama Pelanggan --}}
            <div class="trx-field">
                <label class="trx-label">
                    <i class="bi bi-person me-1"></i> Nama Pelanggan
                </label>
                <input
                    type="text"
                    name="nama_pelanggan"
                    id="trxNamaPelanggan"
                    class="trx-input"
                    placeholder="Opsional"
                    autocomplete="off"
                >
            </div>

            {{-- Uang Bayar --}}
            <div class="trx-field">
                <label class="trx-label">
                    <i class="bi bi-cash-stack me-1"></i> Uang Bayar
                    <span class="trx-label-required">*</span>
                </label>
                <div class="trx-input-money-wrap">
                    <span class="trx-money-prefix">Rp</span>
                    <input
                        type="number"
                        name="uang_bayar"
                        id="trxUangBayar"
                        class="trx-input trx-input-money"
                        placeholder="0"
                        min="0"
                        step="1"
                        oninput="hitungKembalian()"
                        required
                    >
                </div>
            </div>

            {{-- Kembalian --}}
            <div class="trx-kembalian-wrap" id="trxKembalianWrap">
                <div class="trx-kembalian-row">
                    <span class="trx-kembalian-label">
                        <i class="bi bi-arrow-return-left me-1"></i> Kembalian
                    </span>
                    <span class="trx-kembalian-val" id="trxKembalianVal">—</span>
                </div>
            </div>

            {{-- Alert kurang bayar --}}
            <div class="trx-alert-kurang" id="trxAlertKurang" style="display:none;">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                Uang bayar kurang dari total!
            </div>

            {{-- Cetak struk --}}
            <label class="trx-cetak-label">
                <input type="checkbox" name="cetak_struk" id="trxCetakStruk" class="trx-cetak-checkbox">
                <span class="trx-cetak-text">
                    <i class="bi bi-printer me-1"></i> Cetak bukti pembayaran
                </span>
            </label>

        </div>

        {{-- Footer --}}
        <div class="trx-modal-footer">
            <button type="button" class="trx-btn-batal" onclick="tutupModalTrx()">
                Batal
            </button>
            <button type="submit" class="trx-btn-bayar" id="trxBtnBayar" disabled>
                <i class="bi bi-check-circle me-1"></i> Bayar
            </button>
        </div>

    </form>
</div>

{{-- ============================================================
     ALERT: Transaksi Berhasil
     ============================================================ --}}
<div class="trx-success-backdrop" id="trxSuccessBackdrop"></div>
<div class="trx-success-modal" id="trxSuccessModal">
    <div class="trx-success-icon">
        <svg viewBox="0 0 52 52" class="trx-success-svg">
            <circle cx="26" cy="26" r="25" fill="none" class="trx-success-circle"/>
            <path d="M14 27l8 8 16-16" fill="none" class="trx-success-check"/>
        </svg>
    </div>
    <div class="trx-success-title">Transaksi Berhasil!</div>
    <div class="trx-success-msg" id="trxSuccessMsg">Pembayaran telah diproses.</div>
    <div class="trx-success-actions">
        <button class="trx-success-btn-struk" id="trxBtnStruk" onclick="cetakStruk()">
            <i class="bi bi-printer me-1"></i> Cetak Struk
        </button>
        <button class="trx-success-btn-lanjut" onclick="lanjutTransaksi()">
            Transaksi Baru
        </button>
    </div>
</div>

{{-- Total untuk JS --}}
<script>
    const trxTotal = {{ collect(session('keranjang', []))->sum('subtotal') }};
</script>