<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin\HargaProduk;
use App\Models\Admin\Produk;
use App\Models\Admin\Satuan;
use App\Models\Admin\StokMasuk;
use App\Models\LogAktivitas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class HargaController extends Controller
{
    public function index()
    {
        $produk = Produk::with(['hargaProduk.unit', 'hargaProduk.stokMasuk' => function($q) {
            $q->orderBy('tanggal_masuk', 'desc');
        }, 'kategori'])
                    ->whereHas('hargaProduk')
                    ->orderBy('nama_produk')
                    ->paginate(9);

        // Tambahkan latest_tanggal_kadaluarsa ke setiap hargaProduk
        foreach ($produk as $item) {
            foreach ($item->hargaProduk as $hp) {
                // Ambil batch pertama (terbaru) dari stokMasuk yang sudah di-order desc
                $latestBatch = $hp->stokMasuk->first();
                
                // Set tanggal kadaluarsa dari batch terbaru
                if ($latestBatch && $latestBatch->tanggal_kadaluarsa) {
                    $hp->latest_tanggal_kadaluarsa = Carbon::parse($latestBatch->tanggal_kadaluarsa)->format('Y-m-d');
                } else {
                    $hp->latest_tanggal_kadaluarsa = null;
                }
            }
        }

        $produkTanpaHarga = Produk::whereDoesntHave('hargaProduk')
                                  ->orderBy('nama_produk')
                                  ->get();

        $satuanList = Satuan::orderBy('satuan')->get();

        return view('admin.harga.index', compact('produk', 'produkTanpaHarga', 'satuanList'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'id_produk'                    => 'required|exists:produk,id_produk',
            'rows'                         => 'required|array|min:1',
            'rows.*.id_unit'               => 'required|exists:units,id_unit',
            'rows.*.stok'                  => 'required|integer|min:1',
            'rows.*.harga'                 => 'required|numeric|min:0',
            'rows.*.harga_jual'            => 'required|numeric|min:0',
            'rows.*.catatan'               => 'nullable|string|max:255',
            'rows.*.tanggal_kadaluarsa'    => 'required|date',
        ]);

        $produk = Produk::findOrFail($request->id_produk);

        DB::beginTransaction();
        try {
            foreach ($request->rows as $row) {
                $exists = HargaProduk::where('id_produk', $request->id_produk)
                                     ->where('id_unit', $row['id_unit'])
                                     ->exists();
                if ($exists) {
                    $satuan = Satuan::find($row['id_unit']);
                    DB::rollBack();
                    return redirect()->route('admin.harga.index')
                        ->with('error', "Satuan \"{$satuan->satuan}\" pada produk ini sudah ada!");
                }

                $hp = HargaProduk::create([
                    'id_produk'   => $request->id_produk,
                    'id_kategori' => $produk->id_kategori,
                    'id_unit'     => $row['id_unit'],
                    'stok'        => $row['stok'],
                    'harga'       => $row['harga'],
                    'harga_jual'  => $row['harga_jual'],
                    'catatan'     => $row['catatan'] ?? null,
                ]);

                StokMasuk::create([
                    'id_harga_produk'    => $hp->id_harga_produk,
                    'id_users'           => Auth::id(),
                    'jenis'              => 'awal',
                    'jumlah'             => $row['stok'],
                    'sisa_stok'          => $row['stok'],
                    'harga_beli'         => $row['harga'],
                    'tanggal_kadaluarsa' => $row['tanggal_kadaluarsa'] ?? null,
                    'tanggal_masuk'      => now()->toDateString(),
                    'catatan'            => null,
                ]);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('admin.harga.index')
                ->with('error', 'Terjadi kesalahan saat menyimpan data: ' . $e->getMessage());
        }

        LogAktivitas::catat('harga', "Menambahkan data harga untuk produk \"{$produk->nama_produk}\".");

        return redirect()->route('admin.harga.index')
            ->with('success', 'Data stok & harga berhasil ditambahkan!');
    }

    public function updateBulk(Request $request, $idProduk)
    {
        $produk = Produk::findOrFail($idProduk);

        $request->validate([
            'rows'                       => 'required|array|min:1',
            'rows.*.id_unit'             => 'required|exists:units,id_unit',
            'rows.*.stok'                => 'required|integer|min:0',
            'rows.*.harga'               => 'required|numeric|min:0',
            'rows.*.harga_jual'          => 'required|numeric|min:0',
            'rows.*.tanggal_kadaluarsa'  => 'required|date',
            'rows.*.catatan'             => 'nullable|string|max:255',
            'rows.*.id_harga_produk'     => 'nullable',
            'deleted'                    => 'nullable|array',
            'deleted.*'                  => 'integer',
        ]);

        // Hapus data yang di-delete
        if ($request->filled('deleted')) {
            foreach ($request->deleted as $idHapus) {
                $hp = HargaProduk::where('id_harga_produk', $idHapus)
                                ->where('id_produk', $idProduk)
                                ->first();
                if ($hp) {
                    if ($hp->detailTransaksi()->exists()) {
                        return redirect()->route('admin.harga.index')
                            ->with('error', 'Salah satu harga tidak bisa dihapus karena sudah dipakai dalam transaksi.');
                    }
                    $hp->delete();
                }
            }
        }

        $updatedCount = 0;
        $createdCount = 0;

        foreach ($request->rows as $row) {
            // Ambil id_harga_produk, pastikan bukan string "null" atau kosong
            $idHarga = $row['id_harga_produk'] ?? null;
            
            // Konversi string "null" atau "" menjadi null
            if ($idHarga === "null" || $idHarga === "" || $idHarga === null) {
                $idHarga = null;
            }
            
            // Cek duplikat satuan
            $dupQuery = HargaProduk::where('id_produk', $idProduk)
                                ->where('id_unit', $row['id_unit']);
            if ($idHarga) {
                $dupQuery->where('id_harga_produk', '!=', $idHarga);
            }
            if ($dupQuery->exists()) {
                $satuan = Satuan::find($row['id_unit']);
                return redirect()->route('admin.harga.index')
                    ->with('error', "Satuan \"{$satuan->satuan}\" sudah ada untuk produk ini.");
            }

            // UPDATE jika $idHarga ada (bukan null)
            if ($idHarga) {
                // Update harga_produk
                HargaProduk::where('id_harga_produk', $idHarga)
                        ->where('id_produk', $idProduk)
                        ->update([
                            'id_unit'    => $row['id_unit'],
                            'stok'       => $row['stok'],
                            'harga'      => $row['harga'],
                            'harga_jual' => $row['harga_jual'],
                            'catatan'    => $row['catatan'] ?? null,
                        ]);
                
                // UPDATE tanggal_kadaluarsa ke batch stok_masuk terbaru (jika ada)
                $latestBatch = StokMasuk::where('id_harga_produk', $idHarga)
                                        ->orderBy('tanggal_masuk', 'desc')
                                        ->first();
                if ($latestBatch && !empty($row['tanggal_kadaluarsa'])) {
                    $latestBatch->tanggal_kadaluarsa = $row['tanggal_kadaluarsa'];
                    $latestBatch->save();
                }
                
                $updatedCount++;
            } else {
                // CREATE jika $idHarga null (satuan baru)
                $hargaBaru = HargaProduk::create([
                    'id_produk'   => $idProduk,
                    'id_kategori' => $produk->id_kategori,
                    'id_unit'     => $row['id_unit'],
                    'stok'        => $row['stok'],
                    'harga'       => $row['harga'],
                    'harga_jual'  => $row['harga_jual'],
                    'catatan'     => $row['catatan'] ?? null,
                ]);
                
                // Catat stok awal ke stok_masuk (jika stok > 0)
                if ($row['stok'] > 0) {
                    StokMasuk::create([
                        'id_harga_produk'    => $hargaBaru->id_harga_produk,
                        'id_users'           => Auth::id(),
                        'jenis'              => 'awal',
                        'jumlah'             => $row['stok'],
                        'sisa_stok'          => $row['stok'],
                        'harga_beli'         => $row['harga'],
                        'tanggal_masuk'      => now(),
                        'tanggal_kadaluarsa' => $row['tanggal_kadaluarsa'] ?? null,
                        'catatan'            => $row['catatan'] ?? null,
                    ]);
                }
                
                $createdCount++;
            }
        }

        LogAktivitas::catat('harga', "Mengubah data harga untuk produk \"{$produk->nama_produk}\".");

        // Tentukan pesan berdasarkan aksi
        if ($createdCount > 0 && $updatedCount == 0) {
            $message = "Data stok & harga produk \"{$produk->nama_produk}\" berhasil ditambahkan!";
        } elseif ($createdCount > 0 && $updatedCount > 0) {
            $message = "Data stok & harga produk \"{$produk->nama_produk}\" berhasil ditambahkan dan diperbarui!";
        } else {
            $message = "Data stok & harga produk \"{$produk->nama_produk}\" berhasil diperbarui!";
        }

        return redirect()->route('admin.harga.index')
            ->with('success', $message);
    }

    public function destroyProduk(Request $request, $idProduk)
    {
        $produk    = Produk::findOrFail($idProduk);
        $hargaList = HargaProduk::where('id_produk', $idProduk)->get();

        foreach ($hargaList as $hp) {
            if ($hp->detailTransaksi()->exists()) {
                return redirect()->route('admin.harga.index')
                    ->with('error', 'Tidak dapat dihapus, salah satu harga sudah pernah digunakan dalam transaksi.');
            }
        }

        HargaProduk::where('id_produk', $idProduk)->delete();

        LogAktivitas::catat('harga', "Menghapus semua data harga untuk produk \"{$produk->nama_produk}\".");

        return redirect()->route('admin.harga.index')
            ->with('success', "Semua data harga produk \"{$produk->nama_produk}\" berhasil dihapus!");
    }

    public function destroy(HargaProduk $harga)
    {
        if ($harga->detailTransaksi()->exists()) {
            return redirect()->route('admin.harga.index')
                ->with('error', 'Data harga tidak dapat dihapus karena sudah pernah digunakan dalam transaksi.');
        }

        $namaProduk = $harga->produk->nama_produk ?? 'unknown';
        $namaSatuan = $harga->unit->satuan ?? 'unknown';

        $harga->delete();

        LogAktivitas::catat('harga', "Menghapus harga satuan \"{$namaSatuan}\" pada produk \"{$namaProduk}\".");

        return redirect()->route('admin.harga.index')
            ->with('success', 'Data harga berhasil dihapus!');
    }

    /**
     * Tambah stok baru (restok) untuk produk yang sudah ada
     */
    public function tambahStok(Request $request, $idHargaProduk)
    {
        $request->validate([
            'jumlah'             => 'required|integer|min:1',
            'harga_beli'         => 'required|numeric|min:0',
            'tanggal_masuk'      => 'required|date',
            'tanggal_kadaluarsa' => 'nullable|date',
        ]);

        $hargaProduk = HargaProduk::findOrFail($idHargaProduk);

        // 1. Tambah stok di harga_produk
        $hargaProduk->stok += $request->jumlah;
        $hargaProduk->save();

        // 2. Catat batch stok masuk (restok)
        StokMasuk::create([
            'id_harga_produk'    => $idHargaProduk,
            'id_users'           => Auth::id(),
            'jenis'              => 'restok',
            'jumlah'             => $request->jumlah,
            'sisa_stok'          => $request->jumlah,
            'harga_beli'         => $request->harga_beli,
            'tanggal_masuk'      => $request->tanggal_masuk,
            'tanggal_kadaluarsa' => $request->tanggal_kadaluarsa,
            'catatan'            => null,
        ]);

        LogAktivitas::catat('stok', "Menambah stok {$request->jumlah} untuk produk {$hargaProduk->produk->nama_produk} (harga beli: Rp " . number_format($request->harga_beli, 0, ',', '.') . ")");

        return redirect()->route('admin.harga.index')->with('success', 'Stok berhasil ditambahkan!');
    }

    public function getBatches($idHargaProduk)
    {
        $batches = StokMasuk::where('id_harga_produk', $idHargaProduk)
                            ->orderBy('tanggal_masuk', 'asc')
                            ->get()
                            ->map(function($batch) {
                                return [
                                    'id' => $batch->id_stok_masuk,
                                    'jumlah' => $batch->jumlah,
                                    'sisa_stok' => $batch->sisa_stok ?? $batch->jumlah,
                                    'harga_beli' => $batch->harga_beli,
                                    'tanggal_masuk' => $batch->tanggal_masuk,
                                    'tanggal_masuk_formatted' => Carbon::parse($batch->tanggal_masuk)->format('d/m/Y'),
                                    'tanggal_kadaluarsa' => $batch->tanggal_kadaluarsa,
                                    'tanggal_kadaluarsa_formatted' => $batch->tanggal_kadaluarsa ? Carbon::parse($batch->tanggal_kadaluarsa)->format('d/m/Y') : null,
                                ];
                            });
        
        return response()->json(['batches' => $batches]);
    }

    /**
     * Update batch stok masuk
     */
    public function updateBatch(Request $request, $idBatch)
    {
        $request->validate([
            'sisa_stok'          => 'required|integer|min:0',
            'harga_beli'         => 'required|numeric|min:0',
            'tanggal_kadaluarsa' => 'nullable|date',
        ]);

        $batch = StokMasuk::findOrFail($idBatch);
        $hargaProduk = $batch->hargaProduk;
        
        // Hitung selisih sisa_stok lama dan baru
        $sisaStokLama = $batch->sisa_stok ?? $batch->jumlah;
        $selisih = $request->sisa_stok - $sisaStokLama;
        
        // Update batch
        $batch->sisa_stok = $request->sisa_stok;
        $batch->harga_beli = $request->harga_beli;
        $batch->tanggal_kadaluarsa = $request->tanggal_kadaluarsa;
        $batch->save();
        
        // Update stok total di harga_produk
        $hargaProduk->stok += $selisih;
        $hargaProduk->save();
        
        LogAktivitas::catat('stok', "Mengedit batch stok ID {$idBatch} untuk produk {$hargaProduk->produk->nama_produk}. Sisa stok berubah dari {$sisaStokLama} menjadi {$request->sisa_stok}");
        
        return response()->json(['success' => true]);
    }
}