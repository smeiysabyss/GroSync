<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Transaksi;
use App\Models\User;
use App\Exports\LaporanTransaksiExport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;

class LaporanController extends Controller
{
    // ----------------------------------------------------------------
    // Tampil halaman laporan
    // ----------------------------------------------------------------
    public function index(Request $request)
    {
        $query = Transaksi::with(['user', 'detail'])
            ->orderBy('created_at', 'desc');

        if ($request->dari) {
            $query->whereDate('created_at', '>=', $request->dari);
        }
        if ($request->sampai) {
            $query->whereDate('created_at', '<=', $request->sampai);
        }
        if ($request->status) {
            $query->where('status', $request->status);
        }
        if ($request->kasir) {
            $query->where('id_users', $request->kasir);
        }

        $transaksis      = $query->paginate(20)->withQueryString();
        $totalPendapatan = $query->where('status', 'selesai')->sum('total');
        $totalTransaksi  = $transaksis->total();
        $kasirs          = User::where('role', 'kasir')->orderBy('username')->get();

        return view('owner.laporan.index', compact(
            'transaksis',
            'totalPendapatan',
            'totalTransaksi',
            'kasirs'
        ));
    }

    // ----------------------------------------------------------------
    // Export Excel — filter sama dengan index()
    // ----------------------------------------------------------------
    public function export(Request $request)
    {
        $dari    = $request->dari;
        $sampai  = $request->sampai;
        $status  = $request->status;
        $kasirId = $request->kasir;

        // Nama file dinamis sesuai filter
        $suffix = Carbon::now()->format('Ymd_His');
        if ($dari && $sampai) {
            $suffix = Carbon::parse($dari)->format('Ymd') . '_' . Carbon::parse($sampai)->format('Ymd');
        } elseif ($dari) {
            $suffix = 'mulai_' . Carbon::parse($dari)->format('Ymd');
        } elseif ($sampai) {
            $suffix = 'sampai_' . Carbon::parse($sampai)->format('Ymd');
        }

        $filename = "laporan-transaksi_{$suffix}.xlsx";

        return Excel::download(
            new LaporanTransaksiExport($dari, $sampai, $status, $kasirId),
            $filename
        );
    }
}