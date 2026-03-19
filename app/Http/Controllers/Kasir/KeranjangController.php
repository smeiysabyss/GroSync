<?php

namespace App\Http\Controllers\Kasir;

use App\Http\Controllers\Controller;
use App\Models\Admin\HargaProduk;
use Illuminate\Http\Request;

class KeranjangController extends Controller
{
    /**
     * Tampilkan isi keranjang
     */
    public function keranjang()
    {
        $keranjang      = session('keranjang', []);
        $totalKeranjang = $this->hitungTotalKeranjang();
        $totalHarga     = collect($keranjang)->sum('subtotal');

        return view('kasir.keranjang', compact('keranjang', 'totalKeranjang', 'totalHarga'));
    }

    /**
     * Tambah produk ke keranjang
     */
    public function tambah(Request $request)
    {
        $request->validate([
            'id_harga_produk' => 'required|exists:harga_produk,id_harga_produk',
            'jumlah'          => 'required|integer|min:1',
        ]);

        $harga  = HargaProduk::with(['produk', 'unit'])->findOrFail($request->id_harga_produk);
        $jumlah = (int) $request->jumlah;

        if ($jumlah > $harga->stok) {
            return back()->with('error', "Stok {$harga->produk->nama_produk} ({$harga->unit->satuan}) tidak mencukupi! Tersisa {$harga->stok}.");
        }

        $keranjang = session('keranjang', []);
        $key       = 'item_' . $harga->id_harga_produk;

        if (isset($keranjang[$key])) {
            $qtyBaru = $keranjang[$key]['jumlah'] + $jumlah;

            if ($qtyBaru > $harga->stok) {
                return back()->with('error', "Total qty melebihi stok yang tersedia ({$harga->stok})!");
            }

            $keranjang[$key]['jumlah']   = $qtyBaru;
            $keranjang[$key]['subtotal'] = $qtyBaru * $harga->harga;
        } else {
            $keranjang[$key] = [
                'id_harga_produk' => $harga->id_harga_produk,
                'id_produk'       => $harga->id_produk,
                'nama_produk'     => $harga->produk->nama_produk,
                'satuan'          => $harga->unit->satuan,
                'harga'           => (float) $harga->harga,
                'jumlah'          => $jumlah,
                'subtotal'        => $jumlah * (float) $harga->harga,
                'stok_tersedia'   => $harga->stok,
                'gambar'          => $harga->produk->gambar,
            ];
        }

        session(['keranjang' => $keranjang]);
        return back()->with('success', "{$harga->produk->nama_produk} berhasil ditambahkan ke keranjang!");
    }

    /**
     * Update qty item di keranjang
     */
    public function update(Request $request)
    {
        $request->validate([
            'key'    => 'required|string',
            'jumlah' => 'required|integer|min:1',
        ]);

        $keranjang = session('keranjang', []);
        $key       = $request->key;

        if (!isset($keranjang[$key])) {
            return back()->with('error', 'Item tidak ditemukan di keranjang.');
        }

        $harga = HargaProduk::find($keranjang[$key]['id_harga_produk']);

        if ($request->jumlah > $harga->stok) {
            return back()->with('error', "Stok tidak mencukupi! Tersisa {$harga->stok}.");
        }

        $keranjang[$key]['jumlah']        = $request->jumlah;
        $keranjang[$key]['subtotal']      = $request->jumlah * $keranjang[$key]['harga'];
        $keranjang[$key]['stok_tersedia'] = $harga->stok;

        session(['keranjang' => $keranjang]);
        return back()->with('success', 'Jumlah berhasil diperbarui.');
    }

    /**
     * Hapus item dari keranjang
     */
    public function hapus(Request $request)
    {
        $keranjang = session('keranjang', []);
        $key       = $request->key;

        if (isset($keranjang[$key])) {
            $nama = $keranjang[$key]['nama_produk'];
            unset($keranjang[$key]);
            session(['keranjang' => $keranjang]);
            return back()->with('success', "{$nama} dihapus dari keranjang.");
        }

        return back()->with('error', 'Item tidak ditemukan.');
    }

    // ============================================================
    // Helper
    // ============================================================
    private function hitungTotalKeranjang(): int
    {
        return (int) collect(session('keranjang', []))->sum('jumlah');
    }
}