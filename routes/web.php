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
use App\Http\Controllers\Owner\ProdukController    as OwnerProdukController;
use App\Http\Controllers\Owner\LaporanController   as OwnerLaporanController;
use App\Http\Controllers\Owner\LogController       as OwnerLogController;





// ===== AUTH =====
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// ===== OWNER =====
Route::prefix('owner')->name('owner.')->middleware(['auth', 'role:owner'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'ownerDashboard'])->name('dashboard');
      Route::get('/produk',    [OwnerProdukController::class,    'index'])->name('produk');
    Route::get('/laporan',   [OwnerLaporanController::class,   'index'])->name('laporan');
    Route::get('/log',       [OwnerLogController::class,       'index'])->name('log');

    Route::get('/laporan/export', [OwnerLaporanController::class, 'export'])->name('laporan.export');
});

// ===== ADMINISTRATOR =====
Route::prefix('admin')->name('admin.')->middleware(['auth', 'role:administrator'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'adminDashboard'])->name('dashboard');

    // Menu Kelola Pengguna
    Route::resource('pengguna', PenggunaController::class)->except(['show', 'create', 'edit']);
    Route::patch('/pengguna/{pengguna}/toggle-status', [PenggunaController::class, 'toggleStatus'])
         ->name('pengguna.toggle-status');

    // Menu Kelola Kategori
    Route::resource('kategori', KategoriController::class)->except(['show', 'create', 'edit']);

    // Menu Kelola Satuan
      Route::resource('satuan', SatuanController::class)->except(['show', 'create', 'edit']);

    // Menu Kelola Harga Produk
     Route::resource('harga', HargaController::class)->except(['show', 'create', 'edit']);

     //Menu Kelola Produk
       Route::resource('produk', ProdukController::class)->except(['show', 'create', 'edit']);
    
});

// ===== KASIR =====
Route::prefix('kasir')->name('kasir.')->middleware(['auth', 'role:kasir'])->group(function () {

    // Dashboard → DashboardController
    Route::get('/dashboard', [DashboardController::class, 'kasirDashboard'])->name('dashboard');

    // --- Search Bar ---
    Route::get('/search-produk', [SearchProdukController::class, 'search'])->name('search.produk');

    // --- Produk ---
    Route::get('/produk/{id_kategori}', [KasirProdukController::class, 'produk'])->name('produk');

    // --- Keranjang ---
    Route::get('/keranjang',           [KasirKeranjangController::class, 'keranjang'])->name('keranjang');
    Route::post('/keranjang/tambah',   [KasirKeranjangController::class, 'tambah'])->name('keranjang.tambah');
    Route::post('/keranjang/update',   [KasirKeranjangController::class, 'update'])->name('keranjang.update');
    Route::post('/keranjang/hapus',    [KasirKeranjangController::class, 'hapus'])->name('keranjang.hapus');

    // --- Transaksi ---
    Route::post('/transaksi/proses',           [KasirTransaksiController::class, 'proses'])->name('transaksi.proses');
    Route::get('/transaksi/{id}/struk',        [KasirTransaksiController::class, 'struk'])->name('transaksi.struk');
    Route::get('/riwayat',                     [KasirTransaksiController::class, 'riwayat'])->name('riwayat');
    Route::post('/transaksi/{id}/batalkan', [KasirTransaksiController::class, 'batalkan'])->name('transaksi.batalkan');
});

// Redirect root ke login
Route::get('/', function () {
    return redirect('/login');
});
















