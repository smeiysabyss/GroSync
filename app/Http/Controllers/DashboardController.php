<?php

namespace App\Http\Controllers;

use App\Models\Admin\Produk;
use App\Models\Admin\HargaProduk;
use App\Models\Admin\Kategori;
use App\Models\Transaksi;
use App\Models\DetailTransaksi;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log; 

class DashboardController extends Controller
{
    /**
     * Dashboard Admin
     */
  public function adminDashboard()
{
    $totalProduk   = Produk::count();
    $totalKategori = Kategori::count();

    // Semua produk dengan relasi lengkap
    $semuaProduk = Produk::with(['kategori', 'hargaProduk.unit', 'hargaProduk.stokMasuk'])
                         ->orderBy('nama_produk')
                         ->get();

    // Stok menipis (stok < 10)
    $stokMenipis = HargaProduk::with(['produk', 'unit'])
                              ->where('stok', '<', 10)
                              ->orderBy('stok', 'asc')
                              ->get();

    // ============================================================
    // PRODUK KADALUARSA - Gabungan dari 2 sumber
    // ============================================================
    
    // 1. Produk yang memiliki tanggal_kadaluarsa di tabel produk (dalam 30 hari)
    $produkExpired = Produk::with(['kategori'])
        ->whereNotNull('tanggal_kadaluarsa')
        ->whereDate('tanggal_kadaluarsa', '<=', now()->addDays(30))
        ->get();
    
    // 2. HargaProduk yang memiliki batch stok_masuk dengan kadaluarsa (dalam 30 hari)
    $batchExpired = HargaProduk::with(['produk.kategori', 'unit', 'stokMasuk'])
        ->whereHas('stokMasuk', function($query) {
            $query->whereNotNull('tanggal_kadaluarsa')
                  ->whereDate('tanggal_kadaluarsa', '<=', now()->addDays(30));
        })
        ->get();
    
    // Gabungkan dan beri label sumber
    $produkKadaluarsa = collect();
    
    foreach ($produkExpired as $p) {
        $produkKadaluarsa->push((object)[
            'sumber' => 'produk',
            'produk' => $p,
            'kategori' => $p->kategori,
            'tanggal_kadaluarsa' => $p->tanggal_kadaluarsa,
            'satuan' => null,
            'jumlah_batch' => null,
        ]);
    }
    
    foreach ($batchExpired as $hp) {
        foreach ($hp->stokMasuk->where('tanggal_kadaluarsa', '<=', now()->addDays(30)) as $batch) {
            $produkKadaluarsa->push((object)[
                'sumber' => 'batch',
                'produk' => $hp->produk,
                'kategori' => $hp->produk->kategori,
                'satuan' => $hp->unit->satuan ?? '-',
                'jumlah_batch' => $batch->jumlah,
                'tanggal_kadaluarsa' => $batch->tanggal_kadaluarsa,
                'id_batch' => $batch->id_stok_masuk,
            ]);
        }
    }
    
    // Urutkan berdasarkan tanggal kadaluarsa terdekat
    $produkKadaluarsa = $produkKadaluarsa->sortBy('tanggal_kadaluarsa');

    return view('admin.dashboard', compact(
        'totalProduk',
        'totalKategori',
        'semuaProduk',
        'stokMenipis',
        'produkKadaluarsa'  // ← Kirim hasil gabungan
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

    // ============================================================
    // HITUNG TOTAL LABA BERSIH (CARA SEDERHANA)
    // ============================================================
    // Ambil semua detail transaksi yang sudah selesai
    $detailTransaksi = DetailTransaksi::with(['transaksi', 'hargaProduk'])
        ->whereHas('transaksi', function($q) {
            $q->where('status', 'selesai');
        })
        ->get();

    $totalPendapatan = $detailTransaksi->sum('subtotal');
    
    // Hitung HPP: jumlah × harga_beli dari harga_produk
    $totalHpp = $detailTransaksi->sum(function($detail) {
        $hargaBeli = $detail->hargaProduk->harga ?? 0;
        return $detail->jumlah * $hargaBeli;
    });
    
    $totalLaba = $totalPendapatan - $totalHpp;

    // Debug: log untuk memastikan data ada (hapus setelah fix)
    Log::info('Total Laba Debug', [
        'total_pendapatan' => $totalPendapatan,
        'total_hpp' => $totalHpp,
        'total_laba' => $totalLaba,
        'jumlah_detail' => $detailTransaksi->count()
    ]);

    $transaksiTerbaru = Transaksi::with('user')->latest()->limit(8)->get();

    $grafikHarian  = $this->grafikHarian();
    $grafikBulanan = $this->grafikBulanan();
    $grafikTahunan = $this->grafikTahunan();

    return view('owner.dashboard', compact(
        'totalProduk', 'totalKategori',
        'transaksiHariIni', 'pendapatanHariIni', 'pendapatanBulanIni', 'totalLaba',
        'transaksiTerbaru',
        'grafikHarian', 'grafikBulanan', 'grafikTahunan'
    ));
}

   /**
     * Dashboard Kasir
     */
    public function kasirDashboard()
    {
        // Ambil kategori dengan pagination (8 per halaman)
        $kategori = Kategori::withCount('produk')
                            ->orderBy('id_kategori', 'asc')
                            ->paginate(8);

        // Ambil 8 produk terbaru yang memiliki harga
        $produkTerbaru = Produk::with(['hargaProduk.unit', 'kategori'])
            ->whereHas('hargaProduk')
            ->orderBy('created_at', 'desc')
            ->take(8)
            ->get();

        // Siapkan data produk untuk JavaScript (sama formatnya dengan halaman produk)
        $produkTerbaruJs = $produkTerbaru->map(function ($produk) {
            return [
                'id_produk' => $produk->id_produk,
                'nama_produk' => $produk->nama_produk,
                'id_kategori' => $produk->id_kategori,
                'nama_kategori' => $produk->kategori->nama_kategori ?? '-',
                'gambar' => $produk->gambar ? Storage::url($produk->gambar) : null,
                'deskripsi' => $produk->deskripsi,
                'tanggal_kadaluarsa' => $produk->tanggal_kadaluarsa 
                ? Carbon::parse($produk->tanggal_kadaluarsa)->format('d/m/Y') 
                : '—',
                'harga_list' => $produk->hargaProduk->map(function ($hargaProduk) {
                    return [
                        'id_harga_produk' => $hargaProduk->id_harga_produk,
                        'satuan' => $hargaProduk->unit->satuan,
                        'harga_jual' => $hargaProduk->harga_jual,
                        'harga_fmt' => 'Rp ' . number_format($hargaProduk->harga_jual, 0, ',', '.'),
                        'stok' => $hargaProduk->stok,
                    ];
                }),
            ];
        });

        return view('kasir.dashboard', compact('kategori', 'produkTerbaru', 'produkTerbaruJs'));
    }

    // ============================================================
    // PRIVATE — Helper grafik owner (tetap dipertahankan)
    // ============================================================

    /**
     * Data grafik harian (14 hari terakhir)
     */
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

    /**
     * Data grafik bulanan (12 bulan terakhir)
     */
    private function grafikBulanan(): array
    {
        $labels  = [];
        $values  = [];
        $bulanId = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agt', 'Sep', 'Okt', 'Nov', 'Des'];

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

    /**
     * Data grafik tahunan (5 tahun terakhir)
     */
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