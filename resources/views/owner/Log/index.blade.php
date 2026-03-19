@extends('layouts.owner')

@section('title', 'Log Aktivitas')

@push('styles')
<link href="{{ asset('css/owner/log.css') }}" rel="stylesheet">
@endpush

@section('content')

<div class="ow-page-header">
    <div>
        <div class="ow-page-title">Log Aktivitas</div>
        <div class="ow-page-subtitle">Riwayat aktivitas seluruh pengguna sistem</div>
    </div>
</div>

{{-- FILTER --}}
<div class="ow-card mb-4">
    <div class="ow-card-body">
        <form method="GET" action="{{ route('owner.log') }}" id="formFilterLog" class="row g-3 align-items-end">
            <div class="col-sm-6 col-md-3">
                <label class="form-label ow-filter-label">Dari Tanggal</label>
                <input type="date" name="dari" id="filterDari"
                       class="form-control ow-filter-input"
                       value="{{ request('dari') }}">
            </div>
            <div class="col-sm-6 col-md-3">
                <label class="form-label ow-filter-label">Sampai Tanggal</label>
                <input type="date" name="sampai" id="filterSampai"
                       class="form-control ow-filter-input"
                       value="{{ request('sampai') }}">
            </div>
            <div class="col-sm-6 col-md-2">
                <label class="form-label ow-filter-label">Modul</label>
                <select name="modul" id="filterModul" class="form-select ow-filter-select">
                    <option value="">Semua Modul</option>
                    @foreach($moduls as $m)
                    <option value="{{ $m }}" {{ request('modul') === $m ? 'selected' : '' }}>
                        {{ ucfirst($m) }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div class="col-sm-6 col-md-2">
                <label class="form-label ow-filter-label">User</label>
                <select name="user" id="filterUser" class="form-select ow-filter-select">
                    <option value="">Semua User</option>
                    @foreach($users as $u)
                    <option value="{{ $u->id }}" {{ request('user') == $u->id ? 'selected' : '' }}>
                        {{ $u->username }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn ow-btn-filter w-100">
                    <i class="bi bi-funnel me-1"></i> Filter
                </button>
                @if(request()->hasAny(['dari','sampai','modul','user']))
                <a href="{{ route('owner.log') }}" class="btn ow-btn-reset">
                    <i class="bi bi-x"></i>
                </a>
                @endif
            </div>
        </form>
    </div>
</div>

{{-- LOG TABLE --}}
<div class="ow-card">
    <div class="ow-table-wrap">
        <table class="ow-table">
            <thead>
                <tr>
                    <th class="col-no">#</th>
                    <th>User</th>
                    <th>Role</th>
                    <th>Modul</th>
                    <th>Aktivitas</th>
                    <th>Waktu</th>
                </tr>
            </thead>
            <tbody>
                @forelse($logs as $i => $log)
                <tr>
                    <td class="ow-td-no">{{ $logs->firstItem() + $i }}</td>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <div class="ow-log-avatar">
                                {{ strtoupper(substr($log->user->username ?? 'U', 0, 1)) }}
                            </div>
                            <span class="ow-log-username">{{ $log->user->username ?? '-' }}</span>
                        </div>
                    </td>
                    <td>
                        @php $role = $log->user->role ?? '-'; @endphp
                        <span class="ow-badge {{
                            $role === 'owner'          ? 'ow-badge-amber' :
                            ($role === 'administrator' ? 'ow-badge-blue'  : 'ow-badge-green')
                        }}">{{ ucfirst($role) }}</span>
                    </td>
                    <td>
                        <span class="ow-badge ow-badge-gray ow-modul-badge">
                            <i class="bi bi-{{
                                $log->modul === 'transaksi' ? 'receipt' :
                                ($log->modul === 'produk'   ? 'box-seam' :
                                ($log->modul === 'user'     ? 'person'   : 'gear'))
                            }} me-1"></i>
                            {{ ucfirst($log->modul) }}
                        </span>
                    </td>
                    <td class="ow-td-activity">{{ $log->activity }}</td>
                    <td class="ow-td-waktu">
                        {{ \Carbon\Carbon::parse($log->created_at)->format('d/m/Y H:i:s') }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6">
                        <div class="ow-empty">
                            <div class="ow-empty-icon"><i class="bi bi-clock-history"></i></div>
                            <div class="ow-empty-text">Belum ada log aktivitas</div>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($logs->hasPages())
    <div class="ow-pagination">
        {{ $logs->withQueryString()->links() }}
    </div>
    @endif
</div>

@endsection

@push('scripts')
<script src="{{ asset('js/owner/log.js') }}"></script>
@endpush