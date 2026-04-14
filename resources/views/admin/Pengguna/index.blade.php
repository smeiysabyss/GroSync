@extends('layouts.admin')

@section('title', 'Kelola Pengguna')

@push('styles')
<link href="{{ asset('css/admin/pengguna.css') }}" rel="stylesheet">
@endpush

@section('content')

{{-- Page Header --}}
<div class="page-header d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="page-title mb-1">Pengguna</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active">Kelola Pengguna</li>
            </ol>
        </nav>
    </div>
    <button class="btn btn-tambah" data-bs-toggle="modal" data-bs-target="#modalTambahPengguna">
        <i class="bi bi-plus-lg me-1"></i> Tambah Pengguna
    </button>
</div>

{{-- Table Card --}}
<div class="table-card">
    <div class="table-responsive">
        <table class="table grosync-table mb-0">
            <thead>
                <tr>
                    <th width="50">No</th>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th width="100">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($pengguna as $index => $user)
                <tr>
                    {{-- Nomor --}}
                    <td>{{ $pengguna->firstItem() + $index }}</td>

                    {{-- Username --}}
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <span class="fw-500">{{ $user->username }}</span>
                        </div>
                    </td>

                    {{-- Email --}}
                    <td class="text-muted">{{ $user->email }}</td>

                    {{-- Role --}}
                    <td>
                        <span class="badge-role badge-role-{{ strtolower($user->role) }}">
                            {{ ucfirst($user->role) }}
                        </span>
                    </td>

                    {{-- Status Toggle --}}
                    <td>
                        @if($user->id === Auth::id())
                            {{-- Akun milik admin sendiri: di-disable, tidak bisa diubah --}}
                            <div class="form-check form-switch status-switch">
                                <input
                                    class="form-check-input"
                                    type="checkbox"
                                    role="switch"
                                    checked
                                    disabled
                                    title="Anda tidak dapat menonaktifkan akun Anda sendiri"
                                >
                                <label class="form-check-label status-label text-aktif">
                                    Aktif <small class="text-muted">(Anda)</small>
                                </label>
                            </div>
                        @else
                            {{-- Akun pengguna lain: toggle normal --}}
                            <form action="{{ route('admin.pengguna.toggle-status', $user->id) }}" method="POST" class="d-inline">
                                @csrf
                                @method('PATCH')
                                <div class="form-check form-switch status-switch">
                                    <input
                                        class="form-check-input"
                                        type="checkbox"
                                        role="switch"
                                        id="status_{{ $user->id }}"
                                        {{ $user->status === 'aktif' ? 'checked' : '' }}
                                        onchange="this.form.submit()"
                                        title="{{ $user->status === 'aktif' ? 'Nonaktifkan' : 'Aktifkan' }}"
                                    >
                                    <label class="form-check-label status-label {{ $user->status === 'aktif' ? 'text-aktif' : 'text-nonaktif' }}"
                                           for="status_{{ $user->id }}">
                                        {{ $user->status === 'aktif' ? 'Aktif' : 'Nonaktif' }}
                                    </label>
                                </div>
                            </form>
                        @endif
                    </td>

                    {{-- Aksi --}}
                    <td>
                        <div class="d-flex gap-2">
                            {{-- Tombol Edit --}}
                            <button
                                class="btn-aksi btn-aksi-edit"
                                title="Edit Pengguna"
                                onclick="openEditModal(
                                    {{ $user->id }},
                                    '{{ $user->email }}',
                                    '{{ $user->username }}',
                                    '{{ $user->role }}'
                                )">
                                <i class="bi bi-pencil-fill"></i>
                            </button>

                            {{-- Tombol Hapus: sembunyikan untuk akun sendiri --}}
                            @if($user->id !== Auth::id())
                            <button
                                class="btn-aksi btn-aksi-hapus"
                                title="Hapus Pengguna"
                                onclick="confirmDelete({{ $user->id }}, '{{ $user->username }}')">
                                <i class="bi bi-trash-fill"></i>
                            </button>
                            @endif
                        </div>
                    </td>

                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center py-5">
                        <div class="empty-state">
                            <i class="bi bi-people"></i>
                            <p>Belum ada pengguna terdaftar</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    @if($pengguna->hasPages())
    <div class="d-flex justify-content-end p-3 border-top">
        {{ $pengguna->links('pagination::bootstrap-5') }}
    </div>
    @endif
</div>


{{-- ============================================================
     MODAL: Tambah Pengguna
     ============================================================ --}}
<div class="modal fade" id="modalTambahPengguna" tabindex="-1" aria-labelledby="modalTambahLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content grosync-modal">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTambahLabel">
                    <i class="bi bi-person-plus-fill me-2"></i>Tambah Pengguna
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.pengguna.store') }}" method="POST">
                @csrf
                <div class="modal-body">

                    {{-- Email --}}
                    <div class="mb-3">
                        <label class="form-label">Email <span class="text-danger">*</span></label>
                        <input type="email" name="email"
                               class="form-control grosync-input @error('email') is-invalid @enderror"
                               placeholder="contoh@email.com" value="{{ old('email') }}" required>
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Username --}}
                    <div class="mb-3">
                        <label class="form-label">Username <span class="text-danger">*</span></label>
                        <input type="text" name="username"
                               class="form-control grosync-input @error('username') is-invalid @enderror"
                               placeholder="Nama pengguna" value="{{ old('username') }}" required>
                        @error('username')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Password --}}
                    <div class="mb-3">
                        <label class="form-label">Password <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="password" name="password" id="passwordTambah"
                                   class="form-control grosync-input @error('password') is-invalid @enderror"
                                   placeholder="Minimal 8 karakter" required>
                            <button class="btn btn-toggle-pw" type="button" onclick="togglePassword('passwordTambah', this)">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                        @error('password')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Role --}}
                    <div class="mb-1">
                        <label class="form-label">Role <span class="text-danger">*</span></label>
                        <select name="role" class="form-select grosync-input @error('role') is-invalid @enderror" required>
                            <option value="" disabled selected>-- Pilih Role --</option>
                            <option value="administrator" {{ old('role') == 'administrator' ? 'selected' : '' }}>Administrator</option>
                            <option value="kasir" {{ old('role') == 'kasir' ? 'selected' : '' }}>Kasir</option>
                            <option value="owner" {{ old('role') == 'owner' ? 'selected' : '' }}>Owner</option>
                        </select>
                        @error('role')
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
     MODAL: Edit Pengguna
     ============================================================ --}}
<div class="modal fade" id="modalEditPengguna" tabindex="-1" aria-labelledby="modalEditLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content grosync-modal">
            <div class="modal-header">
                <h5 class="modal-title" id="modalEditLabel">
                    <i class="bi bi-pencil-square me-2"></i>Edit Pengguna
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="formEditPengguna" action="" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">

                    {{-- Email --}}
                    <div class="mb-3">
                        <label class="form-label">Email <span class="text-danger">*</span></label>
                        <input type="email" name="email" id="editEmail"
                               class="form-control grosync-input" placeholder="contoh@email.com" required>
                    </div>

                    {{-- Username --}}
                    <div class="mb-3">
                        <label class="form-label">Username <span class="text-danger">*</span></label>
                        <input type="text" name="username" id="editUsername"
                               class="form-control grosync-input" placeholder="Nama pengguna" required>
                    </div>

                    {{-- Password --}}
                    <div class="mb-3">
                        <label class="form-label">Password <small class="text-muted">(kosongkan jika tidak diubah)</small></label>
                        <div class="input-group">
                            <input type="password" name="password" id="passwordEdit"
                                   class="form-control grosync-input" placeholder="Password baru">
                            <button class="btn btn-toggle-pw" type="button" onclick="togglePassword('passwordEdit', this)">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                    </div>

                    {{-- Role --}}
                    <div class="mb-1">
                        <label class="form-label">Role <span class="text-danger">*</span></label>
                        <select name="role" id="editRole" class="form-select grosync-input" required>
                            <option value="administrator">Administrator</option>
                            <option value="kasir">Kasir</option>
                            <option value="owner">Owner</option>
                        </select>
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
<div class="modal fade" id="modalHapusPengguna" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content grosync-modal">
            <div class="modal-header border-0 pb-0">
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center px-4 pb-2">
                <div class="delete-icon-wrap mb-3">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                </div>
                <h6 class="fw-bold mb-1">Hapus Pengguna?</h6>
                <p class="text-muted small mb-0">Akun <strong id="deleteUserName"></strong> akan dihapus permanen.</p>
            </div>
            <div class="modal-footer border-0 pt-2 justify-content-center gap-2">
                <button type="button" class="btn btn-batal" data-bs-dismiss="modal">Batal</button>
                <form id="formHapusPengguna" action="" method="POST">
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
<script src="{{ asset('js/admin/pengguna.js') }}"></script>

{{-- Buka modal tambah otomatis jika ada validation error --}}
@if($errors->any())
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var modal = new bootstrap.Modal(document.getElementById('modalTambahPengguna'));
        modal.show();
    });
</script>
@endif
@endpush