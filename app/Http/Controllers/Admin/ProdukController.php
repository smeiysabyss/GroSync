<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin\Produk;
use App\Models\Admin\Kategori;
use App\Models\Admin\Satuan;
use App\Models\Admin\HargaProduk;
use App\Models\Admin\StokMasuk;
use App\Models\LogAktivitas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class ProdukController extends Controller
{
    public function index()
    {
        $produk = Produk::with(['kategori', 'hargaProduk.unit'])
                        ->orderBy('id_produk', 'asc')
                        ->paginate(9);

        $kategoriList = Kategori::orderBy('nama_kategori')->get();
        $satuanList   = Satuan::orderBy('satuan')->get();

        return view('admin.produk.index', compact('produk', 'kategoriList', 'satuanList'));
    }

    public function store(Request $request)      
    {
        $request->validate([
            'nama_produk'        => 'required|string|max:255',
            'id_kategori'        => 'required|exists:kategori_produk,id_kategori',
            'id_unit'            => 'required|exists:units,id_unit',
            'stok_awal'          => 'required|integer|min:0',
            'harga_beli_awal'    => 'required|numeric|min:0',
            'harga_jual'         => 'required|numeric|min:0',
            'tanggal_kadaluarsa' => 'required|date',
            'gambar'             => 'nullable|image|max:2048',
            'deskripsi'          => 'nullable|string',
        ]);

        // 1. Simpan produk
        $produk = Produk::create([
            'nama_produk'        => $request->nama_produk,
            'id_kategori'        => $request->id_kategori,
            'tanggal_kadaluarsa' => $request->tanggal_kadaluarsa,
            'deskripsi'          => $request->deskripsi,
            'gambar'             => $request->hasFile('gambar') ? $request->file('gambar')->store('produk', 'public') : null,
        ]);

        // 2. Simpan ke harga_produk
        $hargaProduk = HargaProduk::create([
            'id_produk'   => $produk->id_produk,
            'id_kategori' => $request->id_kategori,
            'id_unit'     => $request->id_unit,
            'stok'        => $request->stok_awal,
            'harga'       => $request->harga_beli_awal,
            'harga_jual'  => $request->harga_jual,
            'catatan'     => null,
        ]);

        // Simpan stok awal ke stok_masuk (untuk laba)
        StokMasuk::create([
            'id_harga_produk'    => $hargaProduk->id_harga_produk,
            'id_users'           => Auth::id(),
            'jenis'              => 'awal',   
            'jumlah'             => $request->stok_awal,
            'sisa_stok'          => $request->stok_awal,
            'harga_beli'         => $request->harga_beli_awal,
            'tanggal_masuk'      => now()->toDateString(),
            'tanggal_kadaluarsa' => $request->tanggal_kadaluarsa,
            'catatan'            => null,
        ]);

        LogAktivitas::catat('produk', "Menambahkan produk baru: {$request->nama_produk} dengan stok awal {$request->stok_awal}");

        return redirect()->route('admin.produk.index')->with('success', "Produk {$request->nama_produk} berhasil ditambahkan!");
    }
    public function update(Request $request, Produk $produk)
    {
        $request->validate([
            'nama_produk'        => 'required|string|max:255',
            'id_kategori'        => 'required|exists:kategori_produk,id_kategori',
            'tanggal_kadaluarsa' => 'required|date',
            'gambar'             => 'nullable|image|mimes:jpeg,png,webp|max:2048',
            'deskripsi'          => 'nullable|string',
        ]);

        $namaLama = $produk->nama_produk;

        $data = [
            'nama_produk'        => $request->nama_produk,
            'id_kategori'        => $request->id_kategori,
            'tanggal_kadaluarsa' => $request->tanggal_kadaluarsa,
            'deskripsi'          => $request->deskripsi,
        ];

        if ($request->hasFile('gambar')) {
            if ($produk->gambar) Storage::disk('public')->delete($produk->gambar);
            $data['gambar'] = $request->file('gambar')->store('produk', 'public');
        }

        if ($request->hapus_gambar == '1' && $produk->gambar) {
            Storage::disk('public')->delete($produk->gambar);
            $data['gambar'] = null;
        }

        $produk->update($data);

        LogAktivitas::catat('produk', "Mengubah produk \"{$namaLama}\" menjadi \"{$request->nama_produk}\".");

        return redirect()->route('admin.produk.index')
            ->with('success', "Produk \"{$request->nama_produk}\" berhasil diperbarui!");
    }

    public function destroy(Produk $produk)
    {
        if ($produk->hargaProduk()->count() > 0) {
            return redirect()->route('admin.produk.index')
                ->with('error', "Produk \"{$produk->nama_produk}\" tidak dapat dihapus karena masih memiliki data stok/harga!");
        }

        if ($produk->gambar) Storage::disk('public')->delete($produk->gambar);

        $nama = $produk->nama_produk;
        $produk->delete();

        LogAktivitas::catat('produk', "Menghapus produk \"{$nama}\".");

        return redirect()->route('admin.produk.index')
            ->with('success', "Produk \"{$nama}\" berhasil dihapus!");
    }

 
public function getDetail($id)
{
    $produk = Produk::with(['kategori', 'hargaProduk.unit', 'hargaProduk.stokMasuk' => function($q) {
        $q->orderBy('tanggal_masuk', 'desc');
    }])->findOrFail($id);
    
    // Format data untuk response JSON
    $data = [
        'id_produk' => $produk->id_produk,
        'nama_produk' => $produk->nama_produk,
        'nama_kategori' => $produk->kategori->nama_kategori ?? '-',
        'deskripsi' => $produk->deskripsi ?? 'Tidak ada deskripsi',
        'gambar' => $produk->gambar ? Storage::url($produk->gambar) : null,
        'tanggal_kadaluarsa' => $produk->tanggal_kadaluarsa ? Carbon::parse($produk->tanggal_kadaluarsa)->format('d/m/Y') : '-',
        'harga_list' => $produk->hargaProduk->map(function($hp) {
            $margin = $hp->harga > 0 ? (($hp->harga_jual - $hp->harga) / $hp->harga) * 100 : 0;
            return [
                'id_harga_produk' => $hp->id_harga_produk,
                'satuan' => $hp->unit->satuan ?? '-',
                'harga_beli' => $hp->harga,
                'harga_beli_fmt' => 'Rp ' . number_format($hp->harga, 0, ',', '.'),
                'harga_jual' => $hp->harga_jual,
                'harga_jual_fmt' => 'Rp ' . number_format($hp->harga_jual, 0, ',', '.'),
                'stok' => $hp->stok,
                'margin' => round($margin, 1),
                'margin_class' => $margin >= 20 ? 'margin-high' : ($margin >= 10 ? 'margin-medium' : 'margin-low'),
            ];
        }),
        'riwayat_stok' => $produk->hargaProduk->flatMap(function($hp) {
            return $hp->stokMasuk->map(function($sm) use ($hp) {
                return [
                    'id_stok_masuk' => $sm->id_stok_masuk,
                    'tanggal_masuk' => Carbon::parse($sm->tanggal_masuk)->format('d/m/Y'),
                    'jumlah' => $sm->jumlah,
                    'harga_beli' => $sm->harga_beli,
                    'harga_beli_fmt' => 'Rp ' . number_format($sm->harga_beli, 0, ',', '.'),
                    'satuan' => $hp->unit->satuan ?? '-',
                    'kadaluarsa' => $sm->tanggal_kadaluarsa ? Carbon::parse($sm->tanggal_kadaluarsa)->format('d/m/Y') : '-',
                    'sisa_stok' => $sm->sisa_stok ?? $sm->jumlah,
                ];
            });
        })->sortByDesc('tanggal_masuk')->values(),
    ];
    
    return response()->json($data);
}

    public function getEditData($id)
    {
        $produk = Produk::findOrFail($id);
        return response()->json([
            'id_produk' => $produk->id_produk,
            'nama_produk' => $produk->nama_produk,
            'id_kategori' => $produk->id_kategori,
            'deskripsi' => $produk->deskripsi,
            'gambar' => $produk->gambar,
        ]);
    }
}