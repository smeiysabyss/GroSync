@extends('layouts.admin')

@section('title', 'Kelola Kategori')

@push('styles')
<link href="{{ asset('css/admin/kategori.css') }}" rel="stylesheet">
@endpush

@section('content')

{{-- Page Header --}}
<div class="page-header d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="page-title mb-1">Kategori</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active">Kelola Kategori</li>
            </ol>
        </nav>
    </div>
    <button class="btn btn-tambah" data-bs-toggle="modal" data-bs-target="#modalTambahKategori">
        <i class="bi bi-plus-lg me-1"></i> Tambah Kategori
    </button>
</div>

{{-- Table Card --}}
<div class="table-card">
    <div class="table-responsive">
        <table class="table grosync-table mb-0">
            <thead>
                <tr>
                    <th width="60">No</th>
                    <th width="80">Gambar</th>
                    <th>Nama Kategori</th>
                    <th width="160">Jumlah Produk</th>
                    <th width="120">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($kategori as $index => $item)
                <tr>
                    <td>{{ $kategori->firstItem() + $index }}</td>
                    <td>
                        {{-- Thumbnail gambar kategori --}}
                        @if($item->gambar)
                            <img src="{{ Storage::url($item->gambar) }}"
                                 alt="{{ $item->nama_kategori }}"
                                 class="kategori-thumb">
                        @else
                            <div class="kategori-thumb-placeholder">
                                <i class="bi bi-image"></i>
                            </div>
                        @endif
                    </td>
                    <td>
                        <span class="fw-500">{{ $item->nama_kategori }}</span>
                    </td>
                    <td>
                        <span class="badge-produk">
                            {{ $item->produk_count }} Produk
                        </span>
                    </td>
                    <td>
                        <div class="d-flex gap-2">
                            <button
                                class="btn-aksi btn-aksi-edit"
                                title="Edit Kategori"
                                onclick="openEditModal(
                                    {{ $item->id_kategori }},
                                    '{{ addslashes($item->nama_kategori) }}',
                                    '{{ $item->gambar ? Storage::url($item->gambar) : '' }}'
                                )">
                                <i class="bi bi-pencil-fill"></i>
                            </button>
                            <button
                                class="btn-aksi btn-aksi-hapus"
                                title="Hapus Kategori"
                                onclick="confirmDelete({{ $item->id_kategori }}, '{{ addslashes($item->nama_kategori) }}', {{ $item->produk_count ?? 0 }})">
                                <i class="bi bi-trash-fill"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center py-5">
                        <div class="empty-state">
                            <i class="bi bi-tag"></i>
                            <p>Belum ada kategori produk</p>
                            <button class="btn btn-tambah btn-sm mt-2" data-bs-toggle="modal" data-bs-target="#modalTambahKategori">
                                <i class="bi bi-plus-lg me-1"></i>Tambah Sekarang
                            </button>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($kategori->hasPages())
    <div class="d-flex justify-content-end p-3 border-top">
        {{ $kategori->links('pagination::bootstrap-5') }}
    </div>
    @endif
</div>


{{-- ============================================================
     MODAL: Tambah Kategori
     ============================================================ --}}
<div class="modal fade" id="modalTambahKategori" tabindex="-1" aria-labelledby="modalTambahLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content grosync-modal">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTambahLabel">
                    <i class="bi bi-tag-fill me-2"></i>Tambah Kategori
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            {{-- enctype wajib untuk upload file --}}
            <form action="{{ route('admin.kategori.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">

                    {{-- Nama Kategori --}}
                    <div class="mb-3">
                        <label class="form-label">Nama Kategori <span class="text-danger">*</span></label>
                        <input
                            type="text"
                            name="nama_kategori"
                            class="form-control grosync-input @error('nama_kategori') is-invalid @enderror"
                            placeholder="Contoh: Bumbu Dapur, Frozen Food"
                            value="{{ old('nama_kategori') }}"
                            required
                            autofocus>
                        @error('nama_kategori')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Upload Gambar --}}
                    <div class="mb-1">
                        <label class="form-label">Gambar Kategori <span class="text-danger">*</span></label>

                        {{-- Preview area --}}
                        <div class="gambar-preview-wrap" id="previewTambahWrap" style="display:none;">
                            <img id="previewTambah" src="" alt="Preview" class="gambar-preview-img">
                            <button type="button" class="gambar-preview-remove" onclick="hapusPreviewTambah()" title="Hapus gambar">
                                <i class="bi bi-x"></i>
                            </button>
                        </div>

                        {{-- Upload Box --}}
                        <div class="gambar-upload-box" id="uploadBoxTambah" onclick="document.getElementById('gambarTambah').click()">
                            <i class="bi bi-cloud-arrow-up"></i>
                            <p>Klik untuk pilih gambar</p>
                            <small>JPEG, PNG, WEBP • Maks. 2MB</small>
                        </div>

                        <input
                            type="file"
                            id="gambarTambah"
                            name="gambar"
                            accept="image/jpeg,image/png,image/webp"
                            style="display:none;"
                            onchange="previewGambar(this, 'previewTambah', 'previewTambahWrap', 'uploadBoxTambah')"
                        >
                        @error('gambar')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
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
     MODAL: Edit Kategori
     ============================================================ --}}
<div class="modal fade" id="modalEditKategori" tabindex="-1" aria-labelledby="modalEditLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content grosync-modal">
            <div class="modal-header">
                <h5 class="modal-title" id="modalEditLabel">
                    <i class="bi bi-pencil-square me-2"></i>Edit Kategori
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="formEditKategori" action="" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="modal-body">

                    {{-- Nama Kategori --}}
                    <div class="mb-3">
                        <label class="form-label">Nama Kategori <span class="text-danger">*</span></label>
                        <input
                            type="text"
                            name="nama_kategori"
                            id="editNamaKategori"
                            class="form-control grosync-input"
                            placeholder="Nama kategori"
                            required>
                    </div>

                    {{-- Gambar saat ini + opsi ganti --}}
                    <div class="mb-1">
                        <label class="form-label">Gambar Kategori <small class="text-muted">(kosongkan jika tidak diganti)</small></label>

                        {{-- Preview gambar saat ini / gambar baru --}}
                        <div class="gambar-preview-wrap" id="previewEditWrap">
                            <img id="previewEdit" src="" alt="Preview" class="gambar-preview-img">
                            <button type="button" class="gambar-preview-remove" onclick="hapusPreviewEdit()" title="Hapus / ganti gambar">
                                <i class="bi bi-x"></i>
                            </button>
                        </div>

                        {{-- Upload Box (muncul jika gambar dihapus) --}}
                        <div class="gambar-upload-box" id="uploadBoxEdit" style="display:none;" onclick="document.getElementById('gambarEdit').click()">
                            <i class="bi bi-cloud-arrow-up"></i>
                            <p>Klik untuk pilih gambar baru</p>
                            <small>JPEG, PNG, WEBP • Maks. 2MB</small>
                        </div>

                        <input
                            type="file"
                            id="gambarEdit"
                            name="gambar"
                            accept="image/jpeg,image/png,image/webp"
                            style="display:none;"
                            onchange="previewGambar(this, 'previewEdit', 'previewEditWrap', 'uploadBoxEdit')"
                        >
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
<div class="modal fade" id="modalHapusKategori" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content grosync-modal">
            <div class="modal-header border-0 pb-0">
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center px-4 pb-2">
                <div class="delete-icon-wrap mb-3">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                </div>
                <h6 class="fw-bold mb-1">Hapus Kategori?</h6>
                <p class="text-muted small mb-0">
                    Kategori <strong id="deleteNamaKategori"></strong> akan dihapus permanen.
                </p>
                <p class="text-danger small mt-2 mb-0 d-none" id="warningProduk">
                    <i class="bi bi-exclamation-circle me-1"></i>
                    Kategori ini memiliki <strong id="jumlahProdukWarning"></strong> produk terkait!
                </p>
            </div>
            <div class="modal-footer border-0 pt-2 justify-content-center gap-2">
                <button type="button" class="btn btn-batal" data-bs-dismiss="modal">Batal</button>
                <form id="formHapusKategori" action="" method="POST">
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
<script src="{{ asset('js/admin/kategori.js') }}"></script>

@if($errors->any())
<script>
    document.addEventListener('DOMContentLoaded', function () {
        new bootstrap.Modal(document.getElementById('modalTambahKategori')).show();
    });
</script>
@endif
@endpush