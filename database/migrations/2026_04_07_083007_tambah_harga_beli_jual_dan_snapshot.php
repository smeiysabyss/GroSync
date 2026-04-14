<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Modifikasi tabel harga_produk
        Schema::table('harga_produk', function (Blueprint $table) {
            // Rename kolom harga → harga_jual
            $table->renameColumn('harga', 'harga_jual');
            // Tambah harga_beli setelah harga_jual
            $table->decimal('harga_beli', 15, 2)->default(0)->after('harga_jual');
        });

        // 2. Modifikasi tabel detail_transaksi
        Schema::table('detail_transaksi', function (Blueprint $table) {
            // Snapshot harga saat transaksi terjadi
            $table->decimal('harga_jual_saat_transaksi', 15, 2)->default(0)->after('jumlah');
            $table->decimal('harga_beli_saat_transaksi', 15, 2)->default(0)->after('harga_jual_saat_transaksi');
        });
    }

    public function down(): void
    {
        Schema::table('harga_produk', function (Blueprint $table) {
            $table->renameColumn('harga_jual', 'harga');
            $table->dropColumn('harga_beli');
        });

        Schema::table('detail_transaksi', function (Blueprint $table) {
            $table->dropColumn(['harga_jual_saat_transaksi', 'harga_beli_saat_transaksi']);
        });
    }
};