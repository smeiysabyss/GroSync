<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin\Satuan;
use App\Models\LogAktivitas;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SatuanController extends Controller
{
    public function index()
    {
        $satuan = Satuan::orderBy('id_unit', 'asc')->paginate(10);
        return view('admin.satuan.index', compact('satuan'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'satuan' => 'required|string|max:100|unique:units,satuan',
        ], [
            'satuan.required' => 'Nama satuan wajib diisi.',
            'satuan.unique'   => 'Nama satuan sudah ada.',
            'satuan.max'      => 'Nama satuan maksimal 100 karakter.',
        ]);

        Satuan::create(['satuan' => $request->satuan]);

        LogAktivitas::catat('satuan', "Menambahkan satuan \"{$request->satuan}\".");

        return redirect()->route('admin.satuan.index')
            ->with('success', "Satuan \"{$request->satuan}\" berhasil ditambahkan!");
    }

    public function update(Request $request, Satuan $satuan)
    {
        $request->validate([
            'satuan' => [
                'required', 'string', 'max:100',
                Rule::unique('units', 'satuan')->ignore($satuan->id_unit, 'id_unit'),
            ],
        ], [
            'satuan.required' => 'Nama satuan wajib diisi.',
            'satuan.unique'   => 'Nama satuan sudah ada.',
        ]);

        $namaLama = $satuan->satuan;
        $satuan->update(['satuan' => $request->satuan]);

        LogAktivitas::catat('satuan', "Mengubah satuan \"{$namaLama}\" menjadi \"{$request->satuan}\".");

        return redirect()->route('admin.satuan.index')
            ->with('success', "Satuan berhasil diperbarui menjadi \"{$request->satuan}\"!");
    }

    public function destroy(Satuan $satuan)
    {
        if ($satuan->hargaProduk()->count() > 0) {
            return redirect()->route('admin.satuan.index')
                ->with('error', "Satuan \"{$satuan->satuan}\" tidak dapat dihapus karena masih digunakan pada data harga produk!");
        }

        $nama = $satuan->satuan;
        $satuan->delete();

        LogAktivitas::catat('satuan', "Menghapus satuan \"{$nama}\".");

        return redirect()->route('admin.satuan.index')
            ->with('success', "Satuan \"{$nama}\" berhasil dihapus!");
    }
}