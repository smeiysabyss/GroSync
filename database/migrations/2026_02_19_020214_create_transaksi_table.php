<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
  public function up(): void
{
    Schema::create('transaksi', function (Blueprint $table) {
        $table->id('id_transaksi');
        $table->foreignId('id_users')->constrained('users', 'id');
        $table->string('nomor_unik')->unique();
        $table->string('nama_pelanggan');
        $table->decimal('uang_bayar', 15, 2);
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaksi');
    }
};
