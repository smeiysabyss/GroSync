<?php

namespace App\Http\Controllers;

use App\Models\Admin\Produk;
use App\Models\Admin\HargaProduk;
use App\Models\Admin\Kategori;
use App\Models\Transaksi;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Dashboard Admin
     */
    public function adminDashboard()
    {
        $totalProduk   = Produk::count();
        $totalKategori = Kategori::count();

        $semuaProduk = Produk::with(['kategori', 'hargaProduk.unit'])
                             ->orderBy('nama_produk')
                             ->get();

        $stokMenipis = HargaProduk::with(['produk', 'unit'])
                                  ->where('stok', '<', 10)
                                  ->orderBy('stok', 'asc')
                                  ->get();

        $produkKadaluarsa = Produk::with('kategori')
                                  ->whereNotNull('tanggal_kadaluarsa')
                                  ->whereDate('tanggal_kadaluarsa', '<=', now()->addDays(30))
                                  ->orderBy('tanggal_kadaluarsa', 'asc')
                                  ->get();

        return view('admin.dashboard', compact(
            'totalProduk',
            'totalKategori',
            'semuaProduk',
            'stokMenipis',
            'produkKadaluarsa'
        ));
    }

    /**
     * Dashboard Owner
     */
    public function ownerDashboard()
    {
        $today = Carbon::today();

        $totalProduk    = Produk::count();
        $totalKategori  = Kategori::count();

        $transaksiHariIni   = Transaksi::whereDate('created_at', $today)->where('status', 'selesai')->count();
        $pendapatanHariIni  = Transaksi::whereDate('created_at', $today)->where('status', 'selesai')->sum('total');
        $pendapatanBulanIni = Transaksi::whereMonth('created_at', $today->month)
                                       ->whereYear('created_at', $today->year)
                                       ->where('status', 'selesai')
                                       ->sum('total');

        $transaksiTerbaru = Transaksi::with('user')->latest()->limit(8)->get();

        $grafikHarian  = $this->grafikHarian();
        $grafikBulanan = $this->grafikBulanan();
        $grafikTahunan = $this->grafikTahunan();

        return view('owner.dashboard', compact(
            'totalProduk', 'totalKategori',
            'transaksiHariIni', 'pendapatanHariIni', 'pendapatanBulanIni',
            'transaksiTerbaru',
            'grafikHarian', 'grafikBulanan', 'grafikTahunan'
        ));
    }

    /**
     * Dashboard Kasir
     */
    public function kasirDashboard()
    {
        $kategori = Kategori::withCount('produk')
                            ->orderBy('id_kategori', 'asc')
                            ->paginate(8);

        return view('kasir.dashboard', compact('kategori'));
    }

    // ============================================================
    // PRIVATE — Helper grafik owner
    // ============================================================

    private function grafikHarian(): array
    {
        $labels = [];
        $values = [];

        for ($i = 13; $i >= 0; $i--) {
            $date     = Carbon::today()->subDays($i);
            $labels[] = $date->format('d/m');
            $values[] = (float) Transaksi::whereDate('created_at', $date)
                                         ->where('status', 'selesai')
                                         ->sum('total');
        }

        return ['labels' => $labels, 'values' => $values];
    }

    private function grafikBulanan(): array
    {
        $labels  = [];
        $values  = [];
        $bulanId = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agt','Sep','Okt','Nov','Des'];

        for ($i = 11; $i >= 0; $i--) {
            $date     = Carbon::now()->subMonths($i);
            $labels[] = $bulanId[$date->month - 1] . ' ' . $date->format('y');
            $values[] = (float) Transaksi::whereMonth('created_at', $date->month)
                                         ->whereYear('created_at', $date->year)
                                         ->where('status', 'selesai')
                                         ->sum('total');
        }

        return ['labels' => $labels, 'values' => $values];
    }

    private function grafikTahunan(): array
    {
        $labels = [];
        $values = [];

        for ($i = 4; $i >= 0; $i--) {
            $year     = Carbon::now()->subYears($i)->year;
            $labels[] = (string) $year;
            $values[] = (float) Transaksi::whereYear('created_at', $year)
                                         ->where('status', 'selesai')
                                         ->sum('total');
        }

        return ['labels' => $labels, 'values' => $values];
    }
}