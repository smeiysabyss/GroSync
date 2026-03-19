{{-- resources/views/admin/harga/index.blade.php --}}
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
    <button class="btn btn-tambah" data-bs-toggle="modal" data-bs-target="#modalTambahHarga">
        <i class="bi bi-plus-lg me-1"></i> Tambah Harga
    </button>
</div>

{{-- Product Cards Grid --}}
@if($produk->isEmpty())
    <div class="empty-state-full">
        <i class="bi bi-box-seam"></i>
        <p>Belum ada produk dengan data harga</p>
        <button class="btn btn-tambah mt-2" data-bs-toggle="modal" data-bs-target="#modalTambahHarga">
            <i class="bi bi-plus-lg me-1"></i>Tambah Sekarang
        </button>
    </div>
@else
    <div class="row g-3">
        @foreach($produk as $item)
        <div class="col-12 col-md-6 col-xl-4">
            <div class="produk-card">
                {{-- Card Header: Nama Produk --}}
                <div class="produk-card-header">
                    <div class="produk-info">
                        <div class="produk-avatar">{{ strtoupper(substr($item->nama_produk, 0, 1)) }}</div>
                        <div>
                            <div class="produk-nama">{{ $item->nama_produk }}</div>
                            <div class="produk-kategori">{{ $item->kategori->nama_kategori ?? '-' }}</div>
                        </div>
                    </div>
                </div>

                {{-- Daftar Harga per Satuan --}}
                <div class="produk-card-body">
                    @forelse($item->hargaProduk as $hp)
                    <div class="harga-row">
                        <div class="harga-detail">
                            <span class="harga-nominal">Rp. {{ number_format($hp->harga, 0, ',', '.') }} / {{ $hp->unit->satuan ?? '-' }}</span>
                            <span class="harga-stok">Stok: {{ number_format($hp->stok) }} {{ $hp->unit->satuan ?? '' }}</span>
                            @if($hp->catatan)
                            <span class="harga-catatan"><i class="bi bi-chat-left-text me-1"></i>{{ $hp->catatan }}</span>
                            @endif
                        </div>
                        <div class="harga-aksi">
                            <button
                                class="btn-aksi btn-aksi-edit"
                                title="Edit"
                                onclick="openEditModal(
                                    {{ $hp->id_harga_produk }},
                                    {{ $item->id_produk }},
                                    {{ $hp->id_unit }},
                                    {{ $hp->stok }},
                                    '{{ $hp->harga }}',
                                    '{{ addslashes($hp->catatan ?? '') }}'
                                )">
                                <i class="bi bi-pencil-fill"></i>
                            </button>
                            <button
                                class="btn-aksi btn-aksi-hapus"
                                title="Hapus"
                                onclick="confirmDelete({{ $hp->id_harga_produk }}, '{{ addslashes($item->nama_produk) }}', '{{ $hp->unit->satuan ?? '' }}')">
                                <i class="bi bi-trash-fill"></i>
                            </button>
                        </div>
                    </div>
                    @empty
                    <div class="text-muted small text-center py-2">Belum ada data harga</div>
                    @endforelse
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
     MODAL: Tambah Stok & Harga
     ============================================================ --}}
<div class="modal fade" id="modalTambahHarga" tabindex="-1" aria-labelledby="modalTambahLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content grosync-modal">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTambahLabel">
                    <i class="bi bi-plus-circle-fill me-2"></i>Tambah Stok & Harga
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.harga.store') }}" method="POST" id="formTambahHarga">
                @csrf
                <div class="modal-body">

                    {{-- Pilih Produk --}}
                    <div class="mb-4">
                        <label class="form-label">Produk <span class="text-danger">*</span></label>
                        <select name="id_produk" class="form-select grosync-input @error('id_produk') is-invalid @enderror" required>
                            <option value="" disabled selected>-- Pilih Produk --</option>
                            @foreach($semuaProduk as $p)
                            <option value="{{ $p->id_produk }}" {{ old('id_produk') == $p->id_produk ? 'selected' : '' }}>
                                {{ $p->nama_produk }}
                            </option>
                            @endforeach
                        </select>
                        @error('id_produk')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Baris Satuan, Stok, Harga (dynamic) --}}
                    <div id="barisSatuanContainer">
                        {{-- Baris pertama (default) --}}
                        <div class="baris-satuan" id="baris_0">
                            <div class="row g-2 align-items-end mb-2">
                                <div class="col-md-3">
                                    <label class="form-label">Satuan <span class="text-danger">*</span></label>
                                    <select name="rows[0][id_unit]" class="form-select grosync-input" required>
                                        <option value="" disabled selected>Satuan</option>
                                        @foreach($satuanList as $s)
                                        <option value="{{ $s->id_unit }}">{{ $s->satuan }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Stok <span class="text-danger">*</span></label>
                                    <input type="number" name="rows[0][stok]" class="form-control grosync-input"
                                           placeholder="0" min="0" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Harga <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text rp-prefix">Rp</span>
                                        <input type="number" name="rows[0][harga]" class="form-control grosync-input"
                                               placeholder="0" min="0" required>
                                    </div>
                                </div>
                                <div class="col-md-2 d-flex gap-1 justify-content-end">
                                    <button type="button" class="btn-row btn-row-add" onclick="tambahBaris()" title="Tambah baris">
                                        <i class="bi bi-plus-lg"></i>
                                    </button>
                                    <button type="button" class="btn-row btn-row-remove d-none" onclick="hapusBaris(this)" title="Hapus baris">
                                        <i class="bi bi-dash-lg"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="mb-3">
                                <input type="text" name="rows[0][catatan]" class="form-control grosync-input"
                                       placeholder="Catatan (opsional)">
                            </div>
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
     MODAL: Edit Stok & Harga
     ============================================================ --}}
<div class="modal fade" id="modalEditHarga" tabindex="-1" aria-labelledby="modalEditLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content grosync-modal">
            <div class="modal-header">
                <h5 class="modal-title" id="modalEditLabel">
                    <i class="bi bi-pencil-square me-2"></i>Edit Stok & Harga
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="formEditHarga" action="" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">

                    {{-- Produk (read-only display) --}}
                    <div class="mb-3">
                        <label class="form-label">Produk</label>
                        <select name="id_produk" id="editIdProduk" class="form-select grosync-input" required>
                            @foreach($semuaProduk as $p)
                            <option value="{{ $p->id_produk }}">{{ $p->nama_produk }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="row g-2">
                        <div class="col-md-4">
                            <label class="form-label">Satuan <span class="text-danger">*</span></label>
                            <select name="id_unit" id="editIdUnit" class="form-select grosync-input" required>
                                @foreach($satuanList as $s)
                                <option value="{{ $s->id_unit }}">{{ $s->satuan }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Stok <span class="text-danger">*</span></label>
                            <input type="number" name="stok" id="editStok"
                                   class="form-control grosync-input" min="0" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Harga <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text rp-prefix">Rp</span>
                                <input type="number" name="harga" id="editHarga"
                                       class="form-control grosync-input" min="0" required>
                            </div>
                        </div>
                    </div>

                    <div class="mt-3">
                        <label class="form-label">Catatan</label>
                        <input type="text" name="catatan" id="editCatatan"
                               class="form-control grosync-input" placeholder="Catatan (opsional)">
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
<div class="modal fade" id="modalHapusHarga" tabindex="-1" aria-hidden="true">
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
                <p class="text-muted small mb-0" id="deleteHargaInfo"></p>
            </div>
            <div class="modal-footer border-0 pt-2 justify-content-center gap-2">
                <button type="button" class="btn btn-batal" data-bs-dismiss="modal">Batal</button>
                <form id="formHapusHarga" action="" method="POST">
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
<script>
    // Data satuan untuk dynamic rows
    const satuanOptions = @json($satuanList->map(fn($s) => ['id' => $s->id_unit, 'nama' => $s->satuan]));
</script>
<script src="{{ asset('js/admin/harga.js') }}"></script>

@if($errors->any())
<script>
    document.addEventListener('DOMContentLoaded', function () {
        new bootstrap.Modal(document.getElementById('modalTambahHarga')).show();
    });
</script>
@endif
@endpush