<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\DetailTransaksi;
use App\Models\LogAktivitas;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\LabaExport;
use Barryvdh\DomPDF\Facade\Pdf;

class LaporanLabaController extends Controller
{
    public function index(Request $request)
    {
        $periode = $request->get('periode', 'bulan');
        
        $tanggalMulai = $this->getTanggalMulai($periode);
        $tanggalSelesai = Carbon::now();
        
        // Ambil detail transaksi dalam periode
        $detailTransaksis = DetailTransaksi::with(['transaksi', 'hargaProduk.produk', 'hargaProduk.unit'])
            ->whereHas('transaksi', function($q) use ($tanggalMulai, $tanggalSelesai) {
                $q->whereBetween('created_at', [$tanggalMulai, $tanggalSelesai])
                  ->where('status', 'selesai');
            })
            ->get();
        
        // Hitung agregat
        $totalPendapatan = $detailTransaksis->sum('subtotal');
        $totalHpp = $detailTransaksis->sum(function($detail) {
            return $detail->jumlah * ($detail->hargaProduk->harga ?? 0);
        });
        $labaBersih = $totalPendapatan - $totalHpp;
        $margin = $totalPendapatan > 0 ? ($labaBersih / $totalPendapatan) * 100 : 0;
        
        // Data untuk grafik (6 bulan terakhir)
        $grafikData = $this->getGrafikLaba();
        
        // Data laba per produk
        $labaPerProduk = $this->getLabaPerProduk($detailTransaksis);
        
        return view('owner.laporan.laba', compact(
            'periode',
            'totalPendapatan',
            'totalHpp',
            'labaBersih',
            'margin',
            'grafikData',
            'labaPerProduk'
        ));
    }
    
    /**
     * Export Laporan Laba (Excel atau PDF)
     */
    public function export(Request $request)
    {
        $periode = $request->get('periode', 'bulan');
        $format = $request->get('format', 'excel');
        
        $tanggalMulai = $this->getTanggalMulai($periode);
        $tanggalSelesai = Carbon::now();
        
        // Ambil detail transaksi dalam periode
        $detailTransaksis = DetailTransaksi::with(['transaksi', 'hargaProduk.produk', 'hargaProduk.unit'])
            ->whereHas('transaksi', function($q) use ($tanggalMulai, $tanggalSelesai) {
                $q->whereBetween('created_at', [$tanggalMulai, $tanggalSelesai])
                  ->where('status', 'selesai');
            })
            ->get();
        
        // Hitung agregat
        $totalPendapatan = $detailTransaksis->sum('subtotal');
        $totalHpp = $detailTransaksis->sum(function($detail) {
            return $detail->jumlah * ($detail->hargaProduk->harga ?? 0);
        });
        $labaBersih = $totalPendapatan - $totalHpp;
        $margin = $totalPendapatan > 0 ? ($labaBersih / $totalPendapatan) * 100 : 0;
        
        // Data laba per produk
        $labaPerProduk = $this->getLabaPerProduk($detailTransaksis);
        
        // Data untuk export
        $data = [
            'periode' => $this->getPeriodeLabel($periode),
            'tanggal_mulai' => $tanggalMulai->format('d/m/Y'),
            'tanggal_selesai' => $tanggalSelesai->format('d/m/Y'),
            'total_pendapatan' => $totalPendapatan,
            'total_hpp' => $totalHpp,
            'laba_bersih' => $labaBersih,
            'margin' => $margin,
            'laba_per_produk' => $labaPerProduk,
            'tanggal_export' => Carbon::now()->format('d/m/Y H:i:s'),
        ];
        
        // Catat ke Log Aktivitas
        $labelPeriode = $this->getPeriodeLabel($periode);
        LogAktivitas::catat(
            'laporan_laba',
            "Export laporan laba periode {$labelPeriode} ke format " . strtoupper($format)
        );
        
        if ($format == 'pdf') {
            $pdf = Pdf::loadView('owner.laporan.export-laba-pdf', $data);
            $pdf->setPaper('a4', 'landscape');
            return $pdf->download('laporan-laba-' . $periode . '-' . Carbon::now()->format('Ymd_His') . '.pdf');
        }
        
        // Default: Excel
        return Excel::download(new LabaExport($data), 'laporan-laba-' . $periode . '-' . Carbon::now()->format('Ymd_His') . '.xlsx');
    }
    
    private function getTanggalMulai($periode)
    {
        switch ($periode) {
            case 'hari':
                return Carbon::today();
            case 'minggu':
                return Carbon::now()->startOfWeek();
            case 'bulan':
                return Carbon::now()->startOfMonth();
            case 'tahun':
                return Carbon::now()->startOfYear();
            default:
                return Carbon::now()->startOfMonth();
        }
    }
    
    private function getPeriodeLabel($periode)
    {
        switch ($periode) {
            case 'hari':
                return 'Hari Ini';
            case 'minggu':
                return 'Minggu Ini';
            case 'bulan':
                return 'Bulan Ini';
            case 'tahun':
                return 'Tahun Ini';
            default:
                return 'Bulan Ini';
        }
    }
    
    private function getGrafikLaba()
    {
        $labels = [];
        $pendapatanData = [];
        $labaData = [];
        
        for ($i = 5; $i >= 0; $i--) {
            $bulan = Carbon::now()->subMonths($i);
            $labels[] = $bulan->translatedFormat('M Y');
            
            $detail = DetailTransaksi::with(['transaksi', 'hargaProduk'])
                ->whereHas('transaksi', function($q) use ($bulan) {
                    $q->whereMonth('created_at', $bulan->month)
                      ->whereYear('created_at', $bulan->year)
                      ->where('status', 'selesai');
                })
                ->get();
            
            $pendapatan = $detail->sum('subtotal');
            $hpp = $detail->sum(function($d) {
                return $d->jumlah * ($d->hargaProduk->harga ?? 0);
            });
            
            $pendapatanData[] = $pendapatan;
            $labaData[] = $pendapatan - $hpp;
        }
        
        return [
            'labels' => $labels,
            'pendapatan' => $pendapatanData,
            'laba' => $labaData
        ];
    }
    
    private function getLabaPerProduk($detailTransaksis)
    {
        $produkMap = [];
        
        foreach ($detailTransaksis as $detail) {
            $produkId = $detail->hargaProduk->id_produk;
            $produkNama = $detail->hargaProduk->produk->nama_produk ?? 'Produk Unknown';
            $satuan = $detail->hargaProduk->unit->satuan ?? '-';
            $jumlah = $detail->jumlah;
            $pendapatan = $detail->subtotal;
            $hpp = $detail->jumlah * ($detail->hargaProduk->harga ?? 0);
            $laba = $pendapatan - $hpp;
            
            if (!isset($produkMap[$produkId])) {
                $produkMap[$produkId] = [
                    'nama_produk' => $produkNama,
                    'satuan' => $satuan,
                    'jumlah' => 0,
                    'pendapatan' => 0,
                    'hpp' => 0,
                    'laba' => 0,
                ];
            }
            
            $produkMap[$produkId]['jumlah'] += $jumlah;
            $produkMap[$produkId]['pendapatan'] += $pendapatan;
            $produkMap[$produkId]['hpp'] += $hpp;
            $produkMap[$produkId]['laba'] += $laba;
        }
        
        return collect($produkMap)->sortByDesc('laba')->values();
    }
}