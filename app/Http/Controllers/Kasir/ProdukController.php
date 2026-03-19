<?php

namespace App\Http\Controllers\Kasir;

use App\Http\Controllers\Controller;
use App\Models\Admin\Produk;
use App\Models\Admin\Kategori;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

class ProdukController extends Controller
{
    public function produk($id_kategori)
    {
        $kategori = Kategori::findOrFail($id_kategori);

        $produk = Produk::with(['hargaProduk.unit', 'kategori'])
                        ->where('id_kategori', $id_kategori)
                        ->orderBy('nama_produk', 'asc')
                        ->get();

        $totalKeranjang = (int) collect(session('keranjang', []))->sum('jumlah');

        // Siapkan data produk untuk JS — hindari arrow function di dalam @json blade
        $produkJs = $produk->map(function ($p) {
            return [
                'id_produk'          => $p->id_produk,
                'nama_produk'        => $p->nama_produk,
                'deskripsi'          => $p->deskripsi ?? '—',
                'gambar'             => $p->gambar ? Storage::url($p->gambar) : '',
                'tanggal_kadaluarsa' => $p->tanggal_kadaluarsa
                                        ? Carbon::parse($p->tanggal_kadaluarsa)->format('d-m-Y')
                                        : '—',
                'total_stok'         => $p->hargaProduk->sum('stok'),
                'nama_kategori'      => $p->kategori->nama_kategori ?? '—',
                'harga_list'         => $p->hargaProduk->map(function ($hp) {
                    return [
                        'id_harga_produk' => $hp->id_harga_produk,
                        'satuan'          => $hp->unit->satuan,
                        'harga'           => $hp->harga,
                        'harga_fmt'       => 'Rp ' . number_format($hp->harga, 0, ',', '.'),
                        'stok'            => $hp->stok,
                    ];
                })->values(),
            ];
        })->values();

        return view('kasir.produk', compact('kategori', 'produk', 'totalKeranjang', 'produkJs'));
    }
}