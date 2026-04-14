<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin\HargaProduk;
use App\Models\Admin\StokMasuk;
use App\Models\Admin\Produk;
use App\Models\Admin\Satuan;
use App\Models\LogAktivitas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StokMasukController extends Controller
{
    public function index()
    {
        $stokMasuk = StokMasuk::with(['hargaProduk.produk', 'hargaProduk.unit', 'user'])
                              ->orderBy('created_at', 'desc')
                              ->paginate(15);

        $produkList = Produk::with('hargaProduk.unit')->get();

        return view('admin.stok_masuk.index', compact('stokMasuk', 'produkList'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'id_harga_produk' => 'required|exists:harga_produk,id_harga_produk',
            'jumlah'          => 'required|integer|min:1',
            'harga_beli'      => 'required|numeric|min:0',
            'tanggal_masuk'   => 'required|date',
            'tanggal_kadaluarsa' => 'nullable|date',
            'catatan'         => 'nullable|string',
        ]);

        $hargaProduk = HargaProduk::findOrFail($request->id_harga_produk);

        // 1. Tambah stok di harga_produk
        $hargaProduk->stok += $request->jumlah;
        $hargaProduk->save();

        // 2. Catat batch stok masuk
        StokMasuk::create([
            'id_harga_produk'    => $request->id_harga_produk,
            'jumlah'             => $request->jumlah,
            'harga_beli'         => $request->harga_beli,
            'tanggal_masuk'      => $request->tanggal_masuk,
            'tanggal_kadaluarsa' => $request->tanggal_kadaluarsa,
            'catatan'            => $request->catatan,
            'id_users'           => Auth::id(),
        ]);

        $namaProduk = $hargaProduk->produk->nama_produk;
        $satuan     = $hargaProduk->unit->satuan;

        LogAktivitas::catat('stok', "Menambah stok {$request->jumlah} {$satuan} untuk produk \"{$namaProduk}\" (harga beli Rp " . number_format($request->harga_beli, 0, ',', '.') . ")");

        return redirect()->route('admin.stok_masuk.index')
            ->with('success', "Stok {$namaProduk} berhasil ditambahkan!");
    }

    public function destroy(StokMasuk $stokMasuk)
    {
        $jumlah = $stokMasuk->jumlah;
        $hargaProduk = $stokMasuk->hargaProduk;
        $namaProduk = $hargaProduk->produk->nama_produk;

        // Kurangi stok di harga_produk
        $hargaProduk->stok -= $jumlah;
        $hargaProduk->save();

        $stokMasuk->delete();

        LogAktivitas::catat('stok', "Menghapus riwayat stok masuk {$jumlah} untuk produk \"{$namaProduk}\"");

        return redirect()->route('admin.stok_masuk.index')
            ->with('success', "Riwayat stok masuk berhasil dihapus!");
    }
}