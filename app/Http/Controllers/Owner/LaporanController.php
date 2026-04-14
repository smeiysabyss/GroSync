<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Transaksi;
use App\Models\User;
use App\Models\LogAktivitas;
use App\Models\DetailTransaksi;
use App\Models\Admin\StokMasuk;
use App\Exports\LaporanTransaksiExport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class LaporanController extends Controller
{
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

        // ============================================================
        // HITUNG TOTAL LABA
        // ============================================================
        $totalLaba = $this->hitungLaba($request);

        $kasirs = User::where('role', 'kasir')->orderBy('username')->get();

        return view('owner.laporan.index', compact(
            'transaksis',
            'totalPendapatan',
            'totalTransaksi',
            'totalLaba',
            'kasirs'
        ));
    }

    /**
     * Hitung laba berdasarkan filter yang sama
     * Laba = SUM( (hrg_jual - harga_beli_batch) * jumlah )
     * 
     * Menggunakan metode FIFO sederhana:
     * - Ambil semua detail transaksi dalam periode
     * - Urutkan dari yang terlama
     * - Cocokkan dengan stok masuk (batch) secara berurutan
     */
    private function hitungLaba(Request $request)
    {
        // Query detail transaksi dengan filter yang sama
        $detailQuery = DetailTransaksi::whereHas('transaksi', function ($q) use ($request) {
            $q->where('status', 'selesai');
            
            if ($request->dari) {
                $q->whereDate('created_at', '>=', $request->dari);
            }
            if ($request->sampai) {
                $q->whereDate('created_at', '<=', $request->sampai);
            }
            if ($request->kasir) {
                $q->where('id_users', $request->kasir);
            }
        });

        // Ambil semua detail transaksi
        $details = $detailQuery->with(['hargaProduk.stokMasuk' => function ($q) {
            $q->orderBy('tanggal_masuk', 'asc');
        }])->orderBy('created_at', 'asc')->get();

        $totalLaba = 0;

        foreach ($details as $detail) {
            $sisaJumlah = $detail->jumlah;
            $hargaJual = $detail->hrg_jual ?? $detail->hargaProduk->harga_jual ?? 0;
            
            // Ambil batch stok masuk yang masih tersedia (FIFO)
            $batches = $detail->hargaProduk->stokMasuk()
                ->where('sisa_stok', '>', 0)
                ->orWhereNull('sisa_stok')
                ->orderBy('tanggal_masuk', 'asc')
                ->get();

            foreach ($batches as $batch) {
                if ($sisaJumlah <= 0) break;

                $stokBatchTersedia = $batch->sisa_stok ?? $batch->jumlah;
                $ambilDariBatch = min($sisaJumlah, $stokBatchTersedia);
                
                $labaBatch = ($hargaJual - $batch->harga_beli) * $ambilDariBatch;
                $totalLaba += $labaBatch;
                
                $sisaJumlah -= $ambilDariBatch;
            }
        }

        return $totalLaba;
    }

    public function export(Request $request)
    {
        $dari    = $request->dari;
        $sampai  = $request->sampai;
        $status  = $request->status;
        $kasirId = $request->kasir;

        $suffix = Carbon::now()->format('Ymd_His');
        if ($dari && $sampai) {
            $suffix = Carbon::parse($dari)->format('Ymd') . '_' . Carbon::parse($sampai)->format('Ymd');
        } elseif ($dari) {
            $suffix = 'mulai_' . Carbon::parse($dari)->format('Ymd');
        } elseif ($sampai) {
            $suffix = 'sampai_' . Carbon::parse($sampai)->format('Ymd');
        }

        $filename = "laporan-transaksi_{$suffix}.xlsx";

        $keterangan = 'Mengunduh laporan transaksi';
        if ($dari || $sampai) {
            $keterangan .= ' periode ' . ($dari ?? '...') . ' s/d ' . ($sampai ?? '...');
        }
        if ($status) {
            $keterangan .= " — status: {$status}";
        }
        LogAktivitas::catat('laporan', $keterangan . '.');

        return Excel::download(
            new LaporanTransaksiExport($dari, $sampai, $status, $kasirId),
            $filename
        );
    }
}   