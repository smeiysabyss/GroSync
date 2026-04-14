<?php

namespace App\Http\Controllers\Kasir;

use App\Http\Controllers\Controller;
use App\Models\Admin\HargaProduk;
use App\Models\Admin\StokMasuk;
use App\Models\Transaksi;
use App\Models\DetailTransaksi;
use App\Models\LogAktivitas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class TransaksiController extends Controller
{
    // ================================================================
    // PROSES TRANSAKSI BARU
    // ================================================================

    public function proses(Request $request)
    {
        $request->validate([
            'nama_pelanggan' => 'nullable|string|max:255',
            'uang_bayar'     => 'required|numeric|min:0',
        ], [
            'uang_bayar.required' => 'Nominal uang bayar wajib diisi.',
            'uang_bayar.min'      => 'Uang bayar tidak boleh negatif.',
        ]);

        $keranjang = session('keranjang', []);

        if (empty($keranjang)) {
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Keranjang masih kosong!']);
            }
            return back()->with('error', 'Keranjang masih kosong!');
        }

        $total     = collect($keranjang)->sum('subtotal');
        $kembalian = (float) $request->uang_bayar - $total;

        if ($kembalian < 0) {
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Uang bayar kurang dari total belanja!']);
            }
            return back()->with('error', 'Uang bayar kurang dari total belanja!');
        }

        DB::beginTransaction();
        try {
            $transaksi = Transaksi::create([
                'id_users'       => Auth::id(),
                'nomor_unik'     => Transaksi::generateNomorUnik(),
                'nama_pelanggan' => $request->nama_pelanggan ?? 'Umum',
                'uang_bayar'     => $request->uang_bayar,
                'total'          => $total,
                'kembalian'      => $kembalian,
                'status'         => 'selesai',
            ]);

            foreach ($keranjang as $item) {
                // 1. Simpan detail transaksi dengan harga jual
                DetailTransaksi::create([
                    'id_transaksi'    => $transaksi->id_transaksi,
                    'id_harga_produk' => $item['id_harga_produk'],
                    'jumlah'          => $item['jumlah'],
                    'hrg_jual'        => $item['harga_jual'],
                    'subtotal'        => $item['subtotal'],
                ]);

                // 2. Kurangi stok di harga_produk
                HargaProduk::where('id_harga_produk', $item['id_harga_produk'])
                    ->decrement('stok', $item['jumlah']);

                // 3. FIFO: Kurangi sisa_stok dari batch stok masuk (urutan dari yang terlama)
                $sisaJumlah = $item['jumlah'];
                $batches = StokMasuk::where('id_harga_produk', $item['id_harga_produk'])
                    ->where(function ($q) {
                        $q->where('sisa_stok', '>', 0)
                          ->orWhereNull('sisa_stok');
                    })
                    ->orderBy('tanggal_masuk', 'asc')
                    ->get();

                foreach ($batches as $batch) {
                    if ($sisaJumlah <= 0) break;

                    $stokTersedia = $batch->sisa_stok ?? $batch->jumlah;
                    $ambil = min($sisaJumlah, $stokTersedia);

                    // Update sisa_stok
                    $batch->sisa_stok = $stokTersedia - $ambil;
                    $batch->save();

                    $sisaJumlah -= $ambil;
                }
            }

            LogAktivitas::catat(
                'transaksi',
                "Transaksi {$transaksi->nomor_unik} — " .
                $transaksi->nama_pelanggan .
                " — Total: Rp " . number_format($total, 0, ',', '.')
            );

            DB::commit();
            session()->forget('keranjang');

            if ($request->ajax()) {
                return response()->json([
                    'success'      => true,
                    'id_transaksi' => $transaksi->id_transaksi,
                    'nomor_unik'   => $transaksi->nomor_unik,
                    'total'        => $total,
                    'kembalian'    => $kembalian,
                    'message'      => 'Transaksi berhasil!',
                ]);
            }

            return redirect()->route('kasir.transaksi.struk', $transaksi->id_transaksi)
                ->with('success', 'Transaksi berhasil diproses!');

        } catch (\Exception $e) {
            DB::rollBack();

            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Terjadi kesalahan, silakan coba lagi.']);
            }
            return back()->with('error', 'Terjadi kesalahan saat memproses transaksi.');
        }
    }

    // ================================================================
    // STRUK PDF
    // ================================================================

    public function struk($id)
    {
        $transaksi = Transaksi::with([
            'detail.hargaProduk.produk',
            'detail.hargaProduk.unit',
            'user',
        ])->findOrFail($id);

        if ($transaksi->id_users !== Auth::id()) {
            abort(403);
        }

        $total     = $transaksi->total;
        $kembalian = $transaksi->kembalian;

        $pdf = Pdf::loadView('kasir.struk', compact('transaksi', 'total', 'kembalian'))
                  ->setPaper([0, 0, 226.77, 650], 'portrait');

        return $pdf->download("struk-{$transaksi->nomor_unik}.pdf");
    }

    // ================================================================
    // RIWAYAT — dengan filter periode
    // ================================================================

    public function riwayat(Request $request)
    {
        $periode = $request->get('periode', 'hari_ini');

        $query = Transaksi::with([
                'detail.hargaProduk.produk',
                'detail.hargaProduk.unit',
            ])
            ->where('id_users', Auth::id())
            ->orderBy('created_at');

        switch ($periode) {
            case 'minggu':
                $query->whereBetween('created_at', [
                    Carbon::now()->startOfWeek(),
                    Carbon::now()->endOfWeek(),
                ]);
                $labelPeriode = 'Minggu Ini';
                $subtitlePeriode = Carbon::now()->startOfWeek()->format('d M') . ' – ' . Carbon::now()->endOfWeek()->format('d M Y');
                break;

            case 'bulan':
                $query->whereMonth('created_at', Carbon::now()->month)
                      ->whereYear('created_at', Carbon::now()->year);
                $labelPeriode    = 'Bulan Ini';
                $subtitlePeriode = Carbon::now()->translatedFormat('F Y');
                break;

            case 'tahun':
                $query->whereYear('created_at', Carbon::now()->year);
                $labelPeriode    = 'Tahun Ini';
                $subtitlePeriode = Carbon::now()->format('Y');
                break;

            default:
                $periode = 'hari_ini';
                $query->whereDate('created_at', today());
                $labelPeriode    = 'Hari Ini';
                $subtitlePeriode = Carbon::now()->translatedFormat('l, d F Y');
                break;
        }

        $transaksis = $query->get();

        $totalTransaksi    = $transaksis->where('status', 'selesai')->count();
        $pendapatanHariIni = $transaksis->where('status', 'selesai')->sum('total');
        $totalKeranjang    = (int) collect(session('keranjang', []))->sum('jumlah');

        return view('kasir.riwayat', compact(
            'transaksis',
            'totalTransaksi',
            'pendapatanHariIni',
            'totalKeranjang',
            'periode',
            'labelPeriode',
            'subtitlePeriode'
        ));
    }
}