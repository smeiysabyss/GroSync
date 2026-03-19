@extends('layouts.admin')

@section('title', 'Kelola Satuan')

@push('styles')
<link href="{{ asset('css/admin/satuan.css') }}" rel="stylesheet">
@endpush

@section('content')

{{-- Page Header --}}
<div class="page-header d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="page-title mb-1">Satuan</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active">Kelola Satuan</li>
            </ol>
        </nav>
    </div>
    <button class="btn btn-tambah" data-bs-toggle="modal" data-bs-target="#modalTambahSatuan">
        <i class="bi bi-plus-lg me-1"></i> Tambah Satuan
    </button>
</div>

{{-- Table Card --}}
<div class="table-card">
    <div class="table-responsive">
        <table class="table grosync-table mb-0">
            <thead>
                <tr>
                    <th width="60">No</th>
                    <th>Nama Satuan</th>
                    <th width="120">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($satuan as $index => $item)
                <tr>
                    <td>{{ $satuan->firstItem() + $index }}</td>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            </div>
                            <span class="fw-500">{{ $item->satuan }}</span>
                        </div>
                    </td>
                    <td>
                        <div class="d-flex gap-2">
                            {{-- Tombol Edit --}}
                            <button
                                class="btn-aksi btn-aksi-edit"
                                title="Edit Satuan"
                                onclick="openEditModal({{ $item->id_unit }}, '{{ addslashes($item->satuan) }}')">
                                <i class="bi bi-pencil-fill"></i>
                            </button>
                            {{-- Tombol Hapus --}}
                            <button
                                class="btn-aksi btn-aksi-hapus"
                                title="Hapus Satuan"
                                onclick="confirmDelete({{ $item->id_unit }}, '{{ addslashes($item->satuan) }}')">
                                <i class="bi bi-trash-fill"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="3" class="text-center py-5">
                        <div class="empty-state">
                            <i class="bi bi-rulers"></i>
                            <p>Belum ada satuan terdaftar</p>
                            <button class="btn btn-tambah btn-sm mt-2"
                                    data-bs-toggle="modal"
                                    data-bs-target="#modalTambahSatuan">
                                <i class="bi bi-plus-lg me-1"></i>Tambah Sekarang
                            </button>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    @if($satuan->hasPages())
    <div class="d-flex justify-content-end p-3 border-top">
        {{ $satuan->links('pagination::bootstrap-5') }}
    </div>
    @endif
</div>


{{-- ============================================================
     MODAL: Tambah Satuan
     ============================================================ --}}
<div class="modal fade" id="modalTambahSatuan" tabindex="-1" aria-labelledby="modalTambahLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content grosync-modal">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTambahLabel">
                    <i class="bi bi-rulers me-2"></i>Tambah Satuan
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.satuan.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-1">
                        <label class="form-label">Nama Satuan <span class="text-danger">*</span></label>
                        <input
                            type="text"
                            name="satuan"
                            class="form-control grosync-input @error('satuan') is-invalid @enderror"
                            placeholder="Contoh: Kg, Karton, Pcs, Pack"
                            value="{{ old('satuan') }}"
                            required
                            autofocus>
                        @error('satuan')
                            <div class="invalid-feedback">{{ $message }}</div>
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
     MODAL: Edit Satuan
     ============================================================ --}}
<div class="modal fade" id="modalEditSatuan" tabindex="-1" aria-labelledby="modalEditLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content grosync-modal">
            <div class="modal-header">
                <h5 class="modal-title" id="modalEditLabel">
                    <i class="bi bi-pencil-square me-2"></i>Edit Satuan
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="formEditSatuan" action="" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="mb-1">
                        <label class="form-label">Nama Satuan <span class="text-danger">*</span></label>
                        <input
                            type="text"
                            name="satuan"
                            id="editNamaSatuan"
                            class="form-control grosync-input"
                            placeholder="Nama satuan"
                            required>
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
<div class="modal fade" id="modalHapusSatuan" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content grosync-modal">
            <div class="modal-header border-0 pb-0">
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center px-4 pb-2">
                <div class="delete-icon-wrap mb-3">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                </div>
                <h6 class="fw-bold mb-1">Apakah anda yakin ingin menghapus data satuan ini?</h6>
                <p class="text-muted small mb-0">
                    Satuan <strong id="deleteNamaSatuan"></strong> akan dihapus permanen.
                </p>
            </div>
            <div class="modal-footer border-0 pt-2 justify-content-center gap-2">
                <button type="button" class="btn btn-batal" data-bs-dismiss="modal">Batal</button>
                <form id="formHapusSatuan" action="" method="POST">
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
<script src="{{ asset('js/admin/satuan.js') }}"></script>

@if($errors->any())
<script>
    document.addEventListener('DOMContentLoaded', function () {
        new bootstrap.Modal(document.getElementById('modalTambahSatuan')).show();
    });
</script>
@endif
@endpush