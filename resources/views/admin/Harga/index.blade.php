@extends('layouts.admin')

@section('title', 'Kelola Stok & Harga')

@push('styles')
<link href="{{ asset('css/admin/harga.css') }}" rel="stylesheet">
@endpush

@section('content')

{{-- Page Header --}}
<div class="page-header d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="page-title mb-1">Stok & Harga</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active">Kelola Stok & Harga</li>
            </ol>
        </nav>
    </div>
    {{-- Tombol Tambah Harga DIHAPUS --}}
</div>

{{-- Product Cards --}}
@if($produk->isEmpty())
    <div class="empty-state-full">
        <i class="bi bi-box-seam"></i>
        <p>Belum ada produk dengan data harga</p>
    </div>
@else
    <div class="row g-3">
        @foreach($produk as $item)
        <div class="col-12 col-md-6 col-xl-4">
            <div class="produk-card">

                {{-- Card Header --}}
                <div class="produk-card-header">
                    <div class="produk-header-info">
                        <div class="produk-nama">{{ $item->nama_produk }}</div>
                        <span class="produk-badge-kategori">{{ $item->kategori->nama_kategori ?? '-' }}</span>
                    </div>
                    <div class="produk-header-aksi">
                        <button
                            class="btn-aksi btn-aksi-edit"
                            title="Edit Harga"
                            onclick="openEditBulkModal(
                                {{ $item->id_produk }},
                                '{{ addslashes($item->nama_produk) }}',
                                {{ json_encode($item->hargaProduk->map(fn($hp) => [
                                    'id_harga_produk' => $hp->id_harga_produk,
                                    'id_unit'         => $hp->id_unit,
                                    'satuan'          => $hp->unit->satuan ?? '-',
                                    'stok'            => $hp->stok,
                                    'harga'           => $hp->harga,
                                    'harga_jual'      => $hp->harga_jual,
                                    'tanggal_kadaluarsa' => $hp->latest_tanggal_kadaluarsa ?? '',
                                    'catatan'         => $hp->catatan ?? '',
                                ])) }}
                            )">
                            <i class="bi bi-pencil-fill"></i>
                        </button>
                        <button
                            class="btn-aksi btn-aksi-hapus"
                            title="Hapus Semua Harga"
                            onclick="confirmDeleteProduk({{ $item->id_produk }}, '{{ addslashes($item->nama_produk) }}')">
                            <i class="bi bi-trash-fill"></i>
                        </button>
                    </div>
                </div>

                {{-- Daftar Harga + Tombol Tambah Stok --}}
                <div class="produk-card-body">
                    @foreach($item->hargaProduk as $hp)
                    <div class="harga-row">
                        <div class="harga-detail">
                            {{-- Harga Jual --}}
                            <div class="harga-nominal">
                                <span class="harga-label">Jual:</span>
                                Rp {{ number_format($hp->harga_jual ?? $hp->harga, 0, ',', '.') }}
                                <span class="harga-satuan">/ {{ $hp->unit->satuan ?? '-' }}</span>
                            </div>

                            {{-- Harga Beli --}}
                            <div class="harga-beli">
                                <span class="harga-label">Beli:</span>
                                Rp {{ number_format($hp->harga, 0, ',', '.') }}
                                <span class="harga-satuan">/ {{ $hp->unit->satuan ?? '-' }}</span>
                            </div>

                            <div class="harga-stok">
                                <i class="bi bi-box-seam me-1"></i>
                                Total Stok: <strong>{{ number_format($hp->stok) }}</strong> {{ $hp->unit->satuan ?? '' }}
                            </div>

                            {{-- Riwayat batch stok masuk (hanya tampilkan 1 batch terbaru) --}}
                            @php
                                $batches = $hp->stokMasuk->sortByDesc('tanggal_masuk');
                                $activeBatches = $batches->filter(function($batch) {
                                    return ($batch->sisa_stok ?? $batch->jumlah) > 0;
                                });
                                $latestBatch = $activeBatches->first();
                                $totalActive = $activeBatches->count();
                                $hiddenCount = $totalActive - 1;
                                
                                $habisCount = $batches->filter(function($batch) {
                                    return ($batch->sisa_stok ?? $batch->jumlah) == 0;
                                })->count();
                            @endphp

                            @if($latestBatch)
                            <div class="stok-batch-wrap">
                                @php
                                    $sisaStok = $latestBatch->sisa_stok ?? $latestBatch->jumlah;
                                    $expDate = $latestBatch->tanggal_kadaluarsa ? \Carbon\Carbon::parse($latestBatch->tanggal_kadaluarsa) : null;
                                    $isExpired = $expDate && $expDate->isPast();
                                    $isExpiring = $expDate && !$isExpired && $expDate->diffInDays(now()) <= 30;
                                @endphp
                                <div class="stok-batch-item">
                                    <div class="stok-batch-left">
                                        <i class="bi bi-arrow-down-circle-fill text-success me-1"></i>
                                        <span class="stok-batch-jumlah">{{ $latestBatch->jumlah }} {{ $hp->unit->satuan ?? '' }}</span>
                                        <span class="stok-batch-harga-beli">
                                            @if($latestBatch->harga_beli)
                                                | Beli: Rp {{ number_format($latestBatch->harga_beli, 0, ',', '.') }}
                                            @endif
                                        </span>
                                        <span class="stok-batch-tanggal">
                                            🆕 {{ \Carbon\Carbon::parse($latestBatch->tanggal_masuk)->format('d/m/Y') }}
                                        </span>
                                        @if($expDate)
                                        <span class="stok-batch-exp {{ $isExpired ? 'expired' : ($isExpiring ? 'expiring' : '') }}">
                                            📅 Exp: {{ $expDate->format('d/m/Y') }}
                                            @if($isExpired)
                                                <span class="badge-expired">EXPIRED!</span>
                                            @elseif($isExpiring)
                                                <span class="badge-expiring">Segera</span>
                                            @endif
                                        </span>
                                        @endif
                                        <span class="stok-batch-sisa">
                                            | Sisa: <strong>{{ $sisaStok }}</strong>
                                        </span>
                                    </div>
                                    <div class="stok-batch-right">
                                        <button class="btn-edit-batch" 
                                                onclick="editBatch({{ $latestBatch->id_stok_masuk }}, {{ $sisaStok }}, {{ $latestBatch->harga_beli }}, '{{ $latestBatch->tanggal_kadaluarsa ? \Carbon\Carbon::parse($latestBatch->tanggal_kadaluarsa)->format('Y-m-d') : '' }}')"
                                                title="Edit batch ini">
                                            <i class="bi bi-pencil-fill"></i>
                                        </button>
                                    </div>
                                </div>
                                
                                @if($hiddenCount > 0 || $habisCount > 0)
                                <div class="stok-batch-more" onclick="lihatSemuaBatch({{ $hp->id_harga_produk }}, '{{ addslashes($item->nama_produk) }}', '{{ $hp->unit->satuan ?? '' }}')">
                                    @if($hiddenCount > 0)+{{ $hiddenCount }} batch lainnya @endif
                                    @if($hiddenCount > 0 && $habisCount > 0) & @endif
                                    @if($habisCount > 0){{ $habisCount }} batch habis @endif
                                </div>
                                @endif
                            </div>
                            @elseif($habisCount > 0)
                            <div class="stok-batch-wrap">
                                <div class="stok-batch-more" onclick="lihatSemuaBatch({{ $hp->id_harga_produk }}, '{{ addslashes($item->nama_produk) }}', '{{ $hp->unit->satuan ?? '' }}')">
                                    Lihat {{ $habisCount }} batch habis
                                </div>
                            </div>
                            @endif
                                                        
                            @if($hp->catatan)
                            <div class="harga-catatan">{{ $hp->catatan }}</div>
                            @endif
                        </div>
                        
                        {{-- Tombol Tambah Stok --}}
                        <div class="harga-aksi-stok">
                            <button
                                class="btn-tambah-stok"
                                title="Tambah Stok Baru"
                                onclick="bukaModalTambahStok(
                                    {{ $hp->id_harga_produk }},
                                    '{{ addslashes($item->nama_produk) }}',
                                    '{{ $hp->unit->satuan ?? '' }}'
                                )">
                                <i class="bi bi-plus-circle-fill me-1"></i> Stok
                            </button>
                        </div>
                    </div>
                    @endforeach
                </div>

            </div>
        </div>
        @endforeach
    </div>

    @if($produk->hasPages())
    <div class="d-flex justify-content-end mt-4">
        {{ $produk->links('pagination::bootstrap-5') }}
    </div>
    @endif
@endif


{{-- ============================================================
     MODAL: Tambah Stok Baru (untuk produk yang sudah ada)
     ============================================================ --}}
<div class="modal fade" id="modalTambahStok" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content grosync-modal">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title"><i class="bi bi-arrow-down-circle-fill me-2"></i>Tambah Stok Baru</h5>
                    <div class="modal-subtitle" id="tambahStokNama">—</div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="formTambahStok" action="" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="stok-info-box mb-3">
                        <i class="bi bi-info-circle me-2"></i>
                        Stok baru akan ditambahkan ke total stok yang ada dan dicatat sebagai batch terpisah.
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Jumlah Stok Masuk <span class="text-danger">*</span></label>
                            <input type="number" name="jumlah" class="form-control grosync-input" min="1" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Harga Beli <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text rp-prefix">Rp</span>
                                <input type="number" name="harga_beli" class="form-control grosync-input" min="0" step="100" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Tanggal Masuk <span class="text-danger">*</span></label>
                            <input type="date" name="tanggal_masuk" id="inputTanggalMasuk" class="form-control grosync-input" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Tanggal Kadaluarsa <span class="text-danger">*</span></label>
                            <input type="date" name="tanggal_kadaluarsa" class="form-control grosync-input" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-batal" data-bs-dismiss="modal"><i class="bi bi-x-lg me-1"></i>Batal</button>
                    <button type="submit" class="btn btn-simpan">
                        <i class="bi bi-plus-circle me-1"></i>Tambah Stok
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>


{{-- ============================================================
     MODAL: Edit Bulk Harga Produk
     ============================================================ --}}
<div class="modal fade" id="modalEditBulk" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content grosync-modal">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title"><i class="bi bi-pencil-square me-2"></i>Edit Stok & Harga</h5>
                    <div class="modal-subtitle" id="editBulkNamaProduk">—</div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="formEditBulk" action="" method="POST">
                @csrf
                <div id="deletedInputs"></div>
                <div class="modal-body">
                    <div id="editBulkContainer"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-batal" data-bs-dismiss="modal"><i class="bi bi-x-lg me-1"></i>Batal</button>
                    <button type="submit" class="btn btn-simpan"><i class="bi bi-check-lg me-1"></i>Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>


{{-- ============================================================
     MODAL: Konfirmasi Hapus Semua Harga Produk
     ============================================================ --}}
<div class="modal fade" id="modalHapusProduk" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content grosync-modal">
            <div class="modal-header border-0 pb-0">
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center px-4 pb-2">
                <div class="delete-icon-wrap mb-3">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                </div>
                <h6 class="fw-bold mb-1">Hapus Semua Harga?</h6>
                <p class="text-muted small mb-0">
                    Semua data harga produk <strong id="deleteNamaProduk"></strong> akan dihapus permanen.
                </p>
            </div>
            <div class="modal-footer border-0 pt-2 justify-content-center gap-2">
                <button type="button" class="btn btn-batal" data-bs-dismiss="modal">Batal</button>
                <form id="formHapusProduk" action="" method="POST">
                    @csrf
                    @method('POST')
                    <button type="submit" class="btn btn-danger-solid">
                        <i class="bi bi-trash-fill me-1"></i>Hapus
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>


{{-- ============================================================
     MODAL: Lihat Semua Batch Stok Masuk
     ============================================================ --}}
<div class="modal fade" id="modalLihatBatch" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content grosync-modal">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title"><i class="bi bi-box-seam me-2"></i>Riwayat Stok Masuk</h5>
                    <div class="modal-subtitle" id="lihatBatchNama">—</div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="lihatBatchContainer"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-batal" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>


{{-- ============================================================
     MODAL: Edit Batch Stok Masuk
     ============================================================ --}}
<div class="modal fade" id="modalEditBatch" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content grosync-modal">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-pencil-square me-2"></i>Edit Batch Stok</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="formEditBatch" action="" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Sisa Stok <span class="text-danger">*</span></label>
                        <input type="number" name="sisa_stok" id="editSisaStok" class="form-control grosync-input" min="0" required>
                        <small class="text-muted">Jumlah stok yang masih tersedia di batch ini</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Harga Beli <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text rp-prefix">Rp</span>
                            <input type="number" name="harga_beli" id="editHargaBeli" class="form-control grosync-input" min="0" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Tanggal Kadaluarsa <small class="text-muted">(opsional)</small></label>
                        <input type="date" name="tanggal_kadaluarsa" id="editTanggalKadaluarsa" class="form-control grosync-input">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-batal" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-simpan">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    const satuanOptions      = @json($satuanList->map(fn($s) => ['id' => $s->id_unit, 'nama' => $s->satuan]));
    const routeUpdateBulk    = '{{ url("admin/harga") }}';
    const routeDestroyProduk = '{{ url("admin/harga") }}';
    const routeTambahStok    = '{{ url("admin/harga") }}';
</script>
<script src="{{ asset('js/admin/harga.js') }}"></script>
@endpush