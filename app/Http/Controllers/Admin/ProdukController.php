<?php
// app/Http/Controllers/Admin/ProdukController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin\Produk;
use App\Models\Admin\Kategori;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProdukController extends Controller
{
    public function index()
    {
        $produk = Produk::with(['kategori', 'hargaProduk.unit'])
                        ->orderBy('id_produk', 'asc')
                        ->paginate(9);

        $kategoriList = Kategori::orderBy('nama_kategori')->get();

        return view('admin.produk.index', compact('produk', 'kategoriList'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_produk'        => 'required|string|max:255',
            'id_kategori'        => 'required|exists:kategori_produk,id_kategori',
            'tanggal_kadaluarsa' => 'nullable|date',
            'gambar'             => 'nullable|image|mimes:jpeg,png,webp|max:2048',
            'deskripsi'          => 'nullable|string',
        ], [
            'nama_produk.required' => 'Nama produk wajib diisi.',
            'id_kategori.required' => 'Kategori wajib dipilih.',
            'gambar.image'         => 'File harus berupa gambar.',
            'gambar.max'           => 'Ukuran gambar maksimal 2MB.',
        ]);

        $data = [
            'nama_produk'        => $request->nama_produk,
            'id_kategori'        => $request->id_kategori,
            'tanggal_kadaluarsa' => $request->tanggal_kadaluarsa,
            'deskripsi'          => $request->deskripsi,
        ];

        // Upload gambar jika ada
        if ($request->hasFile('gambar')) {
            $data['gambar'] = $request->file('gambar')->store('produk', 'public');
        }

        Produk::create($data);

        return redirect()->route('admin.produk.index')
            ->with('success', "Produk \"{$request->nama_produk}\" berhasil ditambahkan!");
    }

    public function update(Request $request, Produk $produk)
    {
        $request->validate([
            'nama_produk'        => 'required|string|max:255',
            'id_kategori'        => 'required|exists:kategori_produk,id_kategori',
            'tanggal_kadaluarsa' => 'nullable|date',
            'gambar'             => 'nullable|image|mimes:jpeg,png,webp|max:2048',
            'deskripsi'          => 'nullable|string',
        ]);

        $data = [
            'nama_produk'        => $request->nama_produk,
            'id_kategori'        => $request->id_kategori,
            'tanggal_kadaluarsa' => $request->tanggal_kadaluarsa,
            'deskripsi'          => $request->deskripsi,
        ];

        // Upload gambar baru jika ada
        if ($request->hasFile('gambar')) {
            // Hapus gambar lama
            if ($produk->gambar) {
                Storage::disk('public')->delete($produk->gambar);
            }
            $data['gambar'] = $request->file('gambar')->store('produk', 'public');
        }

        // Hapus gambar jika user klik hapus preview
        if ($request->hapus_gambar == '1' && $produk->gambar) {
            Storage::disk('public')->delete($produk->gambar);
            $data['gambar'] = null;
        }

        $produk->update($data);

        return redirect()->route('admin.produk.index')
            ->with('success', "Produk \"{$request->nama_produk}\" berhasil diperbarui!");
    }

    public function destroy(Produk $produk)
    {
        // Cek apakah produk masih punya harga
        if ($produk->hargaProduk()->count() > 0) {
            return redirect()->route('admin.produk.index')
                ->with('error', "Produk \"{$produk->nama_produk}\" tidak dapat dihapus karena masih memiliki data harga terkait!");
        }

        // Hapus gambar dari storage
        if ($produk->gambar) {
            Storage::disk('public')->delete($produk->gambar);
        }

        $nama = $produk->nama_produk;
        $produk->delete();

        return redirect()->route('admin.produk.index')
            ->with('success', "Produk \"{$nama}\" berhasil dihapus!");
    }
}