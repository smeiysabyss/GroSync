<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stok_masuk', function (Blueprint $table) {
            $table->id('id_stok_masuk');
            $table->unsignedBigInteger('id_harga_produk');
            $table->unsignedBigInteger('id_users');
            $table->integer('jumlah');
            $table->date('tanggal_kadaluarsa')->nullable();
            $table->date('tanggal_masuk');
            $table->string('catatan', 255)->nullable();
            $table->timestamps();

            $table->foreign('id_harga_produk')
                  ->references('id_harga_produk')
                  ->on('harga_produk')
                  ->onDelete('cascade');

            $table->foreign('id_users')
                  ->references('id')
                  ->on('users')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stok_masuk');
    }
};