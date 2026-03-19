<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Admin\Produk;
use App\Models\Admin\Kategori;
use App\Models\Admin\Satuan;
use Illuminate\Http\Request;

class ProdukController extends Controller
{
    public function index(Request $request)
    {
        $query = Produk::with(['kategori', 'hargaProduk.unit']);

        if ($request->filled('q')) {
            $query->where('nama_produk', 'like', '%' . $request->q . '%');
        }

        if ($request->filled('kategori')) {
            $query->where('id_kategori', $request->kategori);
        }

        if ($request->filled('satuan')) {
            $query->whereHas('hargaProduk', fn($q) => $q->where('id_unit', $request->satuan));
        }

        $produks     = $query->latest()->paginate(15);
        $totalProduk = Produk::count();
        $kategoris   = Kategori::orderBy('nama_kategori')->get();
        $units       = Satuan::orderBy('satuan')->get();

        return view('owner.produk.index', compact('produks', 'totalProduk', 'kategoris', 'units'));
    }
}