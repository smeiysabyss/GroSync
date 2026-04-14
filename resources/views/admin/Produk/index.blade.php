@extends('layouts.admin')

@section('title', 'Kelola Produk')

@push('styles')
<link href="{{ asset('css/admin/produk.css') }}" rel="stylesheet">
@endpush

@section('content')

{{-- Page Header --}}
<div class="page-header d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="page-title mb-1">Produk</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active">Kelola Produk</li>
            </ol>
        </nav>
    </div>
    <button class="btn btn-tambah" data-bs-toggle="modal" data-bs-target="#modalTambahProduk">
        <i class="bi bi-plus-lg me-1"></i> Tambah Produk Baru
    </button>
</div>

{{-- Product Grid --}}
@if($produk->isEmpty())
    <div class="empty-state-full">
        <i class="bi bi-box-seam"></i>
        <p>Tidak ada produk terkini</p>
        <button class="btn btn-tambah mt-2" data-bs-toggle="modal" data-bs-target="#modalTambahProduk">
            <i class="bi bi-plus-lg me-1"></i>Tambah Sekarang
        </button>
    </div>
@else
    <div class="row g-3">
        @foreach($produk as $item)
        <div class="col-12 col-sm-6 col-xl-4">
            <div class="produk-card">

                {{-- Gambar Produk (Klik untuk detail) --}}
                <div class="produk-card-img" style="cursor: pointer;" onclick="openDetailProduk({{ $item->id_produk }})">
                    @if($item->gambar)
                        <img src="{{ asset('storage/' . $item->gambar) }}" alt="{{ $item->nama_produk }}">
                    @else
                        <div class="produk-no-img">
                            <i class="bi bi-image"></i>
                        </div>
                    @endif
                    <span class="produk-badge-kategori">{{ $item->kategori->nama_kategori ?? '-' }}</span>
                </div>

                {{-- Info Produk --}}
                <div class="produk-card-body">
                    <div class="produk-nama" style="cursor: pointer; color: #3a6b1a;" onclick="openDetailProduk({{ $item->id_produk }})">
                        {{ $item->nama_produk }}
                    </div>

                    @if($item->deskripsi)
                    <div class="produk-deskripsi">{{ Str::limit($item->deskripsi, 50) }}</div>
                    @endif

                    {{-- Daftar Harga per Satuan + Stok --}}
                    <div class="produk-harga-list mt-2">
                        @foreach($item->hargaProduk as $hp)
                        <div class="produk-harga-item">
                            <span class="produk-harga-nominal">
                                Rp {{ number_format($hp->harga_jual ?? $hp->harga, 0, ',', '.') }}
                            </span>
                            <span class="produk-harga-satuan">/ {{ $hp->unit->satuan ?? '-' }}</span>
                            <span class="produk-harga-stok">
                                (Stok: {{ number_format($hp->stok) }})
                            </span>
                        </div>
                        @endforeach
                    </div>

                    {{-- Total Stok --}}
                    @php
                        $totalStok = $item->hargaProduk->sum('stok');
                    @endphp
                    <div class="produk-total-stok">
                        <i class="bi bi-box-seam-fill me-1"></i>
                        Total Stok: <strong>{{ number_format($totalStok) }}</strong>
                    </div>
                </div>

                {{-- Aksi --}}
                <div class="produk-card-footer">
                    <button class="btn-aksi btn-aksi-detail" onclick="openDetailProduk({{ $item->id_produk }})">
                        <i class="bi bi-eye-fill me-1"></i> Detail
                    </button>
                    <button class="btn-aksi btn-aksi-edit"
                        onclick="openEditModal(
                            {{ $item->id_produk }},
                            '{{ addslashes($item->nama_produk) }}',
                            {{ $item->id_kategori }},
                            '{{ addslashes($item->deskripsi ?? '') }}',
                            '{{ $item->gambar ?? '' }}'
                        )">
                        <i class="bi bi-pencil-fill me-1"></i> Edit
                    </button>
                    <button class="btn-aksi btn-aksi-hapus"
                        onclick="confirmDelete({{ $item->id_produk }}, '{{ addslashes($item->nama_produk) }}')">
                        <i class="bi bi-trash-fill me-1"></i> Hapus
                    </button>
                </div>

            </div>
        </div>
        @endforeach
    </div>

    {{-- Pagination --}}
    @if($produk->hasPages())
    <div class="d-flex justify-content-end mt-4">
        {{ $produk->links('pagination::bootstrap-5') }}
    </div>
    @endif
@endif


{{-- ============================================================
     MODAL: Tambah Produk
     ============================================================ --}}
<div class="modal fade" id="modalTambahProduk" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content grosync-modal">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-box-seam-fill me-2"></i>Tambah Produk</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.produk.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="row g-3">

                        {{-- Nama Produk --}}
                        <div class="col-12">
                            <label class="form-label">Nama Produk <span class="text-danger">*</span></label>
                            <input type="text" name="nama_produk" class="form-control grosync-input" required>
                        </div>

                        {{-- Kategori --}}
                        <div class="col-md-6">
                            <label class="form-label">Kategori <span class="text-danger">*</span></label>
                            <select name="id_kategori" class="form-select grosync-input" required>
                                <option value="">-- Pilih --</option>
                                @foreach($kategoriList as $kat)
                                <option value="{{ $kat->id_kategori }}">{{ $kat->nama_kategori }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Satuan --}}
                        <div class="col-md-6">
                            <label class="form-label">Satuan <span class="text-danger">*</span></label>
                            <select name="id_unit" class="form-select grosync-input" required>
                                <option value="">-- Pilih --</option>
                                @foreach($satuanList as $unit)
                                <option value="{{ $unit->id_unit }}">{{ $unit->satuan }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Stok Awal & Harga --}}
                        <div class="col-md-4">
                            <label class="form-label">Stok Awal <span class="text-danger">*</span></label>
                            <input type="number" name="stok_awal" class="form-control grosync-input" min="1" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Harga Beli Awal <span class="text-danger">*</span></label>
                            <input type="number" name="harga_beli_awal" class="form-control grosync-input" min="0" step="100" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Harga Jual <span class="text-danger">*</span></label>
                            <input type="number" name="harga_jual" class="form-control grosync-input" min="0" step="100" required>
                        </div>

                        {{-- Tanggal Kadaluarsa & Gambar --}}
                        <div class="col-md-6">
                            <label class="form-label">Tanggal Kadaluarsa <span class="text-danger">*</span></label>
                            <input type="date" name="tanggal_kadaluarsa" class="form-control grosync-input" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Gambar</label>
                            <input type="file" name="gambar" class="form-control grosync-input" accept="image/*">
                        </div>

                        {{-- Deskripsi --}}
                        <div class="col-12">
                            <label class="form-label">Deskripsi</label>
                            <textarea name="deskripsi" class="form-control grosync-input" rows="2"></textarea>
                        </div>

                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-batal" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-simpan">Simpan Produk</button>
                </div>
            </form>
        </div>
    </div>
</div>


{{-- ============================================================
     MODAL: Edit Produk
     ============================================================ --}}
<div class="modal fade" id="modalEditProduk" tabindex="-1" aria-labelledby="modalEditLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content grosync-modal">
            <div class="modal-header">
                <h5 class="modal-title" id="modalEditLabel">
                    <i class="bi bi-pencil-square me-2"></i>Edit Data Produk
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="formEditProduk" action="" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="row g-3">

                        {{-- Nama Produk --}}
                        <div class="col-12">
                            <label class="form-label">Nama Produk <span class="text-danger">*</span></label>
                            <input type="text" name="nama_produk" id="editNamaProduk"
                                   class="form-control grosync-input" required>
                        </div>

                        {{-- Kategori --}}
                        <div class="col-12">
                            <label class="form-label">Kategori <span class="text-danger">*</span></label>
                            <select name="id_kategori" id="editIdKategori" class="form-select grosync-input" required>
                                @foreach($kategoriList as $kat)
                                <option value="{{ $kat->id_kategori }}">{{ $kat->nama_kategori }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Gambar Produk --}}
                        <div class="col-md-12">
                            <label class="form-label">Gambar Produk</label>
                            <input type="file" name="gambar" id="gambarEdit"
                                   class="form-control grosync-input"
                                   accept="image/jpeg,image/png,image/webp"
                                   onchange="previewGambar(this, 'previewEdit')">
                            <div class="gambar-preview-wrap mt-2" id="previewEditWrap">
                                <img id="previewEdit" src="" alt="Preview" class="gambar-preview">
                                <button type="button" class="btn-hapus-preview" onclick="hapusPreview('gambarEdit', 'previewEditWrap')">
                                    <i class="bi bi-x-lg"></i>
                                </button>
                            </div>
                            <input type="hidden" name="hapus_gambar" id="hapusGambarInput" value="0">
                        </div>

                        {{-- Deskripsi --}}
                        <div class="col-12">
                            <label class="form-label">Deskripsi</label>
                            <textarea name="deskripsi" id="editDeskripsi"
                                      class="form-control grosync-input" rows="3"></textarea>
                        </div>

                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-batal" data-bs-dismiss="modal">
                        <i class="bi bi-x-lg me-1"></i>Batal
                    </button>
                    <button type="submit" class="btn btn-simpan">
                        <i class="bi bi-check-lg me-1"></i>Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>


{{-- ============================================================
     MODAL: Konfirmasi Hapus
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
                <h6 class="fw-bold mb-1">Apakah anda yakin ingin menghapus data ini?</h6>
                <p class="text-muted small mb-0">Produk <strong id="deleteNamaProduk"></strong> akan dihapus permanen.</p>
            </div>
            <div class="modal-footer border-0 pt-2 justify-content-center gap-2">
                <button type="button" class="btn btn-batal" data-bs-dismiss="modal">Batal</button>
                <form id="formHapusProduk" action="" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger-solid">
                        <i class="bi bi-trash-fill me-1"></i>Hapus
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>


{{-- ============================================================
     MODAL: Detail Produk (ADMIN)
     ============================================================ --}}
<div class="modal fade modal-detail-produk" id="modalDetailProduk" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content grosync-modal">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-box-seam-fill me-2"></i>Detail Produk
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0" id="detailProdukBody">
                <div class="text-center py-5">
                    <div class="spinner-border text-success" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2 text-muted">Memuat data produk...</p>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src="{{ asset('js/admin/produk.js') }}"></script>

@if($errors->any())
<script>
    document.addEventListener('DOMContentLoaded', function () {
        new bootstrap.Modal(document.getElementById('modalTambahProduk')).show();
    });
</script>
@endif
@endpush