<?php

namespace App\Http\Controllers\Kasir;

use App\Http\Controllers\Controller;
use App\Models\Admin\HargaProduk;
use App\Models\Transaksi;
use App\Models\DetailTransaksi;
use App\Models\LogAktivitas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

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
                DetailTransaksi::create([
                    'id_transaksi'    => $transaksi->id_transaksi,
                    'id_harga_produk' => $item['id_harga_produk'],
                    'jumlah'          => $item['jumlah'],
                    'subtotal'        => $item['subtotal'],
                ]);

                HargaProduk::where('id_harga_produk', $item['id_harga_produk'])
                    ->decrement('stok', $item['jumlah']);
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
    // RIWAYAT — hanya transaksi hari ini milik kasir yang login
    // ================================================================

    public function riwayat()
    {
        $transaksis = Transaksi::with([
                'detail.hargaProduk.produk',
                'detail.hargaProduk.unit',
            ])
            ->where('id_users', Auth::id())
            ->whereDate('created_at', today())
            ->orderByDesc('created_at')
            ->get();

        $totalTransaksi    = $transaksis->where('status', 'selesai')->count();
        $pendapatanHariIni = $transaksis->where('status', 'selesai')->sum('total');
        $totalKeranjang    = (int) collect(session('keranjang', []))->sum('jumlah');

        return view('kasir.riwayat', compact(
            'transaksis',
            'totalTransaksi',
            'pendapatanHariIni',
            'totalKeranjang'
        ));
    }
}