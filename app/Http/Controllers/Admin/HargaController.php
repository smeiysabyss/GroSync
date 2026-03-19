<?php
// app/Http/Controllers/Admin/HargaController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin\HargaProduk;
use App\Models\Admin\Produk;
use App\Models\Admin\Satuan;
use Illuminate\Http\Request;

class HargaController extends Controller
{
    /**
     * Tampilkan daftar produk beserta harga & stoknya
     */
    public function index()
    {
        // Ambil produk yang sudah punya harga, eager load relasi
        $produk = Produk::with(['hargaProduk.unit', 'kategori'])
                        ->whereHas('hargaProduk')
                        ->orderBy('id_produk', 'asc')
                        ->paginate(9);

        $semuaProduk = Produk::orderBy('nama_produk')->get();
        $satuanList  = Satuan::orderBy('satuan')->get();

        return view('admin.harga.index', compact('produk', 'semuaProduk', 'satuanList'));
    }

    /**
     * Simpan data harga baru (bisa multiple baris sekaligus)
     */
    public function store(Request $request)
    {
        $request->validate([
            'id_produk'          => 'required|exists:produk,id_produk',
            'rows'               => 'required|array|min:1',
            'rows.*.id_unit'     => 'required|exists:units,id_unit',
            'rows.*.stok'        => 'required|integer|min:0',
            'rows.*.harga'       => 'required|numeric|min:0',
            'rows.*.catatan'     => 'nullable|string|max:255',
        ], [
            'id_produk.required'      => 'Produk wajib dipilih.',
            'rows.*.id_unit.required' => 'Satuan wajib dipilih.',
            'rows.*.stok.required'    => 'Stok wajib diisi.',
            'rows.*.harga.required'   => 'Harga wajib diisi.',
        ]);

        foreach ($request->rows as $row) {
            // Cegah duplikat produk + satuan
            $exists = HargaProduk::where('id_produk', $request->id_produk)
                                 ->where('id_unit', $row['id_unit'])
                                 ->exists();

            if ($exists) {
                $satuan = Satuan::find($row['id_unit']);
                return redirect()->route('admin.harga.index')
                    ->with('error', "Harga untuk satuan \"{$satuan->satuan}\" pada produk ini sudah ada!");
            }

            HargaProduk::create([
                'id_produk'   => $request->id_produk,
                'id_kategori' => Produk::find($request->id_produk)->id_kategori,
                'id_unit'     => $row['id_unit'],
                'stok'        => $row['stok'],
                'harga'       => $row['harga'],
                'catatan'     => $row['catatan'] ?? null,
            ]);
        }

        return redirect()->route('admin.harga.index')
            ->with('success', 'Data stok & harga berhasil ditambahkan!');
    }

    /**
     * Update data harga
     */
    public function update(Request $request, HargaProduk $harga)
    {
        $request->validate([
            'id_produk' => 'required|exists:produk,id_produk',
            'id_unit'   => 'required|exists:units,id_unit',
            'stok'      => 'required|integer|min:0',
            'harga'     => 'required|numeric|min:0',
            'catatan'   => 'nullable|string|max:255',
        ]);

        $harga->update([
            'id_produk'   => $request->id_produk,
            'id_kategori' => Produk::find($request->id_produk)->id_kategori,
            'id_unit'     => $request->id_unit,
            'stok'        => $request->stok,
            'harga'       => $request->harga,
            'catatan'     => $request->catatan,
        ]);

        return redirect()->route('admin.harga.index')
            ->with('success', 'Data stok & harga berhasil diperbarui!');
    }

    /**
     * Hapus data harga
     */
    public function destroy(HargaProduk $harga)
    {
        // Cek apakah harga ini pernah dipakai di transaksi
        $dipakaiDiTransaksi = $harga->detailTransaksi()->exists();
 
        if ($dipakaiDiTransaksi) {
            return redirect()->route('admin.harga.index')
                ->with('error', 'Data harga tidak dapat dihapus karena sudah pernah digunakan dalam transaksi.');
        }
 
        $harga->delete();
 
        return redirect()->route('admin.harga.index')
            ->with('success', 'Data stok & harga berhasil dihapus!');
    }
}