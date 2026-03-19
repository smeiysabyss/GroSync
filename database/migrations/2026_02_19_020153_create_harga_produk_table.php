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
    Schema::create('harga_produk', function (Blueprint $table) {
        $table->id('id_harga_produk');
        $table->foreignId('id_produk')->constrained('produk', 'id_produk');
        $table->foreignId('id_kategori')->constrained('kategori_produk', 'id_kategori');
        $table->foreignId('id_unit')->constrained('units', 'id_unit');
        $table->decimal('harga', 15, 2);
        $table->integer('stok')->default(0);
        $table->timestamps();
    });
}
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('harga_produk');
    }
};
