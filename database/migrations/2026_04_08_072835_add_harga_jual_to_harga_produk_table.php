<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('harga_produk', function (Blueprint $table) {
            $table->decimal('harga_jual', 15, 2)->after('harga')->default(0);
        });
    }

    public function down()
    {
        Schema::table('harga_produk', function (Blueprint $table) {
            $table->dropColumn('harga_jual');
        });
    }
};