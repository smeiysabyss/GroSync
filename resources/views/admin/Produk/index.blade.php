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
        <i class="bi bi-plus-lg me-1"></i> Tambah Produk
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

                {{-- Gambar Produk --}}
                <div class="produk-card-img">
                    @if($item->gambar)
                        <img src="{{ asset('storage/' . $item->gambar) }}" alt="{{ $item->nama_produk }}">
                    @else
                        <div class="produk-no-img">
                            <i class="bi bi-image"></i>
                        </div>
                    @endif
                    {{-- Badge Kategori --}}
                    <span class="produk-badge-kategori">{{ $item->kategori->nama_kategori ?? '-' }}</span>
                </div>

                {{-- Info Produk --}}
                <div class="produk-card-body">
                    <div class="produk-nama">{{ $item->nama_produk }}</div>

                    @if($item->deskripsi)
                    <div class="produk-deskripsi">{{ Str::limit($item->deskripsi, 60) }}</div>
                    @endif

                    {{-- Harga Ringkas --}}
                    @if($item->hargaProduk->isNotEmpty())
                        <div class="produk-harga-info mt-2">
                            @foreach($item->hargaProduk->take(2) as $hp)
                            <span class="harga-chip">
                                Rp {{ number_format($hp->harga, 0, ',', '.') }} / {{ $hp->unit->satuan ?? '-' }}
                            </span>
                            @endforeach
                            @if($item->hargaProduk->count() > 2)
                            <span class="harga-chip harga-chip-more">+{{ $item->hargaProduk->count() - 2 }} lainnya</span>
                            @endif
                        </div>
                    @else
                        <div class="produk-no-harga mt-2">Rp. (Harga tidak tersedia)</div>
                    @endif

                    {{-- Tanggal Kadaluarsa --}}
                    @if($item->tanggal_kadaluarsa)
                    <div class="produk-exp mt-1">
                        <i class="bi bi-calendar-event me-1"></i>
                        Exp: {{ $item->tanggal_kadaluarsa->format('d/m/Y') }}
                    </div>
                    @endif
                </div>

                {{-- Aksi --}}
                <div class="produk-card-footer">
                    <button
                        class="btn-aksi btn-aksi-edit w-100"
                        onclick="openEditModal(
                            {{ $item->id_produk }},
                            '{{ addslashes($item->nama_produk) }}',
                            {{ $item->id_kategori }},
                            '{{ $item->tanggal_kadaluarsa ? $item->tanggal_kadaluarsa->format('Y-m-d') : '' }}',
                            '{{ addslashes($item->deskripsi ?? '') }}',
                            '{{ $item->gambar ?? '' }}'
                        )">
                        <i class="bi bi-pencil-fill me-1"></i> Edit
                    </button>
                    <button
                        class="btn-aksi btn-aksi-hapus w-100"
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
<div class="modal fade" id="modalTambahProduk" tabindex="-1" aria-labelledby="modalTambahLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content grosync-modal">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTambahLabel">
                    <i class="bi bi-box-seam-fill me-2"></i>Tambah Produk
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.produk.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="row g-3">

                        {{-- Nama Produk --}}
                        <div class="col-12">
                            <label class="form-label">Nama Produk <span class="text-danger">*</span></label>
                            <input type="text" name="nama_produk"
                                   class="form-control grosync-input @error('nama_produk') is-invalid @enderror"
                                   placeholder="Masukkan nama produk"
                                   value="{{ old('nama_produk') }}" required>
                            @error('nama_produk')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Kategori --}}
                        <div class="col-12">
                            <label class="form-label">Kategori <span class="text-danger">*</span></label>
                            <select name="id_kategori" class="form-select grosync-input @error('id_kategori') is-invalid @enderror" required>
                                <option value="" disabled selected>-- Pilih Kategori --</option>
                                @foreach($kategoriList as $kat)
                                <option value="{{ $kat->id_kategori }}" {{ old('id_kategori') == $kat->id_kategori ? 'selected' : '' }}>
                                    {{ $kat->nama_kategori }}
                                </option>
                                @endforeach
                            </select>
                            @error('id_kategori')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Tanggal Kadaluarsa & Gambar --}}
                        <div class="col-md-6">
                            <label class="form-label">Tanggal Kadaluarsa</label>
                            <input type="date" name="tanggal_kadaluarsa"
                                   class="form-control grosync-input"
                                   value="{{ old('tanggal_kadaluarsa') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Gambar Produk</label>
                            <input type="file" name="gambar" id="gambarTambah"
                                   class="form-control grosync-input"
                                   accept="image/jpeg,image/png,image/webp"
                                   onchange="previewGambar(this, 'previewTambah')">
                            <div class="gambar-preview-wrap mt-2 d-none" id="previewTambahWrap">
                                <img id="previewTambah" src="" alt="Preview" class="gambar-preview">
                                <button type="button" class="btn-hapus-preview" onclick="hapusPreview('gambarTambah', 'previewTambahWrap')">
                                    <i class="bi bi-x-lg"></i>
                                </button>
                            </div>
                        </div>

                        {{-- Deskripsi --}}
                        <div class="col-12">
                            <label class="form-label">Deskripsi</label>
                            <textarea name="deskripsi" class="form-control grosync-input"
                                      rows="3" placeholder="Deskripsi produk (opsional)">{{ old('deskripsi') }}</textarea>
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

                        {{-- Tanggal Kadaluarsa & Gambar --}}
                        <div class="col-md-6">
                            <label class="form-label">Tanggal Kadaluarsa</label>
                            <input type="date" name="tanggal_kadaluarsa" id="editTanggalKadaluarsa"
                                   class="form-control grosync-input">
                        </div>
                        <div class="col-md-6">
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