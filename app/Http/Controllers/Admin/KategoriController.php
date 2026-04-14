<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin\Kategori;
use App\Models\LogAktivitas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class KategoriController extends Controller
{
    public function index()
    {
        $kategori = Kategori::withCount('produk')
                            ->orderBy('id_kategori', 'asc')
                            ->paginate(10);

        return view('admin.kategori.index', compact('kategori'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_kategori' => 'required|string|max:100|unique:kategori_produk,nama_kategori',
            'gambar'        => 'required|image|mimes:jpeg,png,webp|max:2048',
        ], [
            'nama_kategori.required' => 'Nama kategori wajib diisi.',
            'nama_kategori.unique'   => 'Nama kategori sudah ada.',
            'nama_kategori.max'      => 'Nama kategori maksimal 100 karakter.',
            'gambar.required'        => 'Gambar kategori wajib diunggah.',
            'gambar.image'           => 'File harus berupa gambar.',
            'gambar.mimes'           => 'Format gambar: jpeg, png, atau webp.',
            'gambar.max'             => 'Ukuran gambar maksimal 2MB.',
        ]);

        $pathGambar = $request->file('gambar')->store('kategori', 'public');

        Kategori::create([
            'nama_kategori' => $request->nama_kategori,
            'gambar'        => $pathGambar,
        ]);

        LogAktivitas::catat('kategori', "Menambahkan kategori \"{$request->nama_kategori}\".");

        return redirect()->route('admin.kategori.index')
            ->with('success', "Kategori \"{$request->nama_kategori}\" berhasil ditambahkan!");
    }

    public function update(Request $request, Kategori $kategori)
    {
        $request->validate([
            'nama_kategori' => [
                'required', 'string', 'max:100',
                Rule::unique('kategori_produk', 'nama_kategori')
                    ->ignore($kategori->id_kategori, 'id_kategori'),
            ],
            'gambar' => 'nullable|image|mimes:jpeg,png,webp|max:2048',
        ], [
            'nama_kategori.required' => 'Nama kategori wajib diisi.',
            'nama_kategori.unique'   => 'Nama kategori sudah ada.',
            'gambar.image'           => 'File harus berupa gambar.',
            'gambar.mimes'           => 'Format gambar: jpeg, png, atau webp.',
            'gambar.max'             => 'Ukuran gambar maksimal 2MB.',
        ]);

        $nameLama = $kategori->nama_kategori;
        $data     = ['nama_kategori' => $request->nama_kategori];

        if ($request->hasFile('gambar')) {
            if ($kategori->gambar) {
                Storage::disk('public')->delete($kategori->gambar);
            }
            $data['gambar'] = $request->file('gambar')->store('kategori', 'public');
        }

        $kategori->update($data);

        LogAktivitas::catat('kategori', "Mengubah kategori \"{$nameLama}\" menjadi \"{$request->nama_kategori}\".");

        return redirect()->route('admin.kategori.index')
            ->with('success', "Kategori berhasil diperbarui menjadi \"{$request->nama_kategori}\"!");
    }

    public function destroy(Kategori $kategori)
    {
        if ($kategori->produk()->count() > 0) {
            return redirect()->route('admin.kategori.index')
                ->with('error', "Kategori \"{$kategori->nama_kategori}\" tidak dapat dihapus karena masih memiliki produk terkait!");
        }

        if ($kategori->gambar) {
            Storage::disk('public')->delete($kategori->gambar);
        }

        $nama = $kategori->nama_kategori;
        $kategori->delete();

        LogAktivitas::catat('kategori', "Menghapus kategori \"{$nama}\".");

        return redirect()->route('admin.kategori.index')
            ->with('success', "Kategori \"{$nama}\" berhasil dihapus!");
    }
}