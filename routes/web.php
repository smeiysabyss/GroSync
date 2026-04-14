<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Admin\PenggunaController;
use App\Http\Controllers\Admin\KategoriController;
use App\Http\Controllers\Admin\SatuanController;
use App\Http\Controllers\Admin\HargaController;
use App\Http\Controllers\Admin\ProdukController;
use App\Http\Controllers\Kasir\SearchProdukController;
use App\Http\Controllers\Kasir\ProdukController as KasirProdukController;
use App\Http\Controllers\Kasir\KeranjangController as KasirKeranjangController;
use App\Http\Controllers\Kasir\TransaksiController as KasirTransaksiController;
use App\Http\Controllers\Owner\ProdukController        as OwnerProdukController;
use App\Http\Controllers\Owner\LaporanController       as OwnerLaporanController;
use App\Http\Controllers\Owner\LaporanLabaController   as OwnerLaporanLabaController;
use App\Http\Controllers\Owner\LogController           as OwnerLogController;
use App\Http\Controllers\Admin\StokMasukController;




// ===== AUTH =====
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// ===== OWNER =====
Route::prefix('owner')->name('owner.')->middleware(['auth', 'role:owner'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'ownerDashboard'])->name('dashboard');

    // --- Menu Daftar Produk
    Route::get('/produk',    [OwnerProdukController::class,    'index'])->name('produk');

    // --- Menu Laporan Transaksi
    Route::get('/laporan',   [OwnerLaporanController::class,   'index'])->name('laporan');

    // --- Menu Laporan Laba/Margin
    Route::get('/laporan/laba', [OwnerLaporanLabaController::class, 'index'])->name('laporan.laba');
    Route::get('/laporan/laba/export', [OwnerLaporanLabaController::class, 'export'])->name('laporan.laba.export');

    // --- Menu Log Aktivitas
    Route::get('/log',       [OwnerLogController::class,       'index'])->name('log');

    // --- Export Laporan Transaksi
    Route::get('/laporan/export', [OwnerLaporanController::class, 'export'])->name('laporan.export');

    
});

// ===== ADMINISTRATOR =====
Route::prefix('admin')->name('admin.')->middleware(['auth', 'role:administrator'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'adminDashboard'])->name('dashboard');

    // --- Menu Kelola Pengguna
    Route::resource('pengguna', PenggunaController::class)->except(['show', 'create', 'edit']);
    Route::patch('/pengguna/{pengguna}/toggle-status', [PenggunaController::class, 'toggleStatus'])
         ->name('pengguna.toggle-status');

    // --- Menu Kelola Kategori
    Route::resource('kategori', KategoriController::class)->except(['show', 'create', 'edit']);

    // --- Menu Kelola Satuan
      Route::resource('satuan', SatuanController::class)->except(['show', 'create', 'edit']);

    // --- Menu Kelola Harga Produk
     Route::resource('harga', HargaController::class)->except(['show', 'create', 'edit']);
     Route::post('/harga/{idProduk}/update-bulk', [HargaController::class, 'updateBulk'])->name('harga.updateBulk');
     Route::post('/harga/{idProduk}/destroy-produk', [HargaController::class, 'destroyProduk'])->name('harga.destroyProduk');
     Route::post('/harga/{idHargaProduk}/tambah-stok', [HargaController::class, 'tambahStok'])->name('harga.tambahStok');
     Route::get('/harga/{idHargaProduk}/batches', [HargaController::class, 'getBatches'])->name('harga.batches');
     Route::post('/harga/batch/{idBatch}/update', [HargaController::class, 'updateBatch'])->name('harga.updateBatch');


    // --- Menu Kelola Produk
        Route::resource('produk', ProdukController::class)->except(['show', 'create', 'edit']);
        Route::get('/produk/{id}/detail', [ProdukController::class, 'getDetail'])->name('produk.detail');
        Route::get('/produk/{id}/edit-data', [ProdukController::class, 'getEditData'])->name('produk.edit-data');
        
    // --- Tambah Stok Masuk
        Route::resource('stok_masuk', StokMasukController::class)->except(['show', 'create', 'edit']);
});

// ===== KASIR =====
Route::prefix('kasir')->name('kasir.')->middleware(['auth', 'role:kasir'])->group(function () {

    // Dashboard → DashboardController
    Route::get('/dashboard', [DashboardController::class, 'kasirDashboard'])->name('dashboard');

    // --- Search Bar 
    Route::get('/search-produk', [SearchProdukController::class, 'search'])->name('search.produk');

    // --- Produk 
    Route::get('/produk/{id_kategori}', [KasirProdukController::class, 'produk'])->name('produk');

    // --- Keranjang 
    Route::get('/keranjang',           [KasirKeranjangController::class, 'keranjang'])->name('keranjang');
    Route::post('/keranjang/tambah',   [KasirKeranjangController::class, 'tambah'])->name('keranjang.tambah');
    Route::post('/keranjang/update',   [KasirKeranjangController::class, 'update'])->name('keranjang.update');
    Route::post('/keranjang/hapus',    [KasirKeranjangController::class, 'hapus'])->name('keranjang.hapus');

    // --- Transaksi
    Route::post('/transaksi/proses',           [KasirTransaksiController::class, 'proses'])->name('transaksi.proses');
    Route::get('/transaksi/{id}/struk',        [KasirTransaksiController::class, 'struk'])->name('transaksi.struk');
    Route::get('/riwayat',                     [KasirTransaksiController::class, 'riwayat'])->name('riwayat');
    Route::post('/transaksi/{id}/batalkan', [KasirTransaksiController::class, 'batalkan'])->name('transaksi.batalkan');
});

// Redirect root ke login
Route::get('/', function () {
    return redirect('/login');
});
















