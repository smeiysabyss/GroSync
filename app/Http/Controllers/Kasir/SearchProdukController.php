<?php

namespace App\Http\Controllers\Kasir;

use App\Http\Controllers\Controller;
use App\Models\Admin\Produk;
use Illuminate\Http\Request;

class SearchProdukController extends Controller
{
    /**
     * AJAX: cari produk berdasarkan keyword
     * GET /kasir/search-produk?q=keyword
     */
    public function search(Request $request)
    {
        $keyword = $request->query('q', '');

        if (strlen($keyword) < 2) {
            return response()->json([]);
        }

        $produk = Produk::with(['kategori', 'hargaProduk'])
            ->where('nama_produk', 'like', '%' . $keyword . '%')
            ->orderBy('nama_produk', 'asc')
            ->limit(10)
            ->get();

        $result = $produk->map(function ($p) {
            $hargaMin = $p->hargaProduk->min('harga');
            return [
                'id_produk'     => $p->id_produk,
                'nama_produk'   => $p->nama_produk,
                'id_kategori'   => $p->id_kategori,
                'nama_kategori' => $p->kategori->nama_kategori ?? '—',
                'harga_min'     => $hargaMin
                    ? number_format($hargaMin, 0, ',', '.')
                    : '—',
            ];
        });

        return response()->json($result);
    }
}