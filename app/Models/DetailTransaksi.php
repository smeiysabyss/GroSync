<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DetailTransaksi extends Model
{
    protected $table      = 'detail_transaksi';
    protected $primaryKey = 'id_detail';

    protected $fillable = [
        'id_transaksi',
        'id_harga_produk',
        'jumlah',
        'subtotal',
        'hrg_jual',
    ];

    protected $casts = [
        'subtotal' => 'float',
        'jumlah'   => 'integer',
    ];

    // Relasi ke transaksi
    public function transaksi()
    {
        return $this->belongsTo(Transaksi::class, 'id_transaksi', 'id_transaksi');
    }

    // Relasi ke harga produk (beserta produk & unit)
    public function hargaProduk()
    {
        return $this->belongsTo(
            \App\Models\Admin\HargaProduk::class,
            'id_harga_produk',
            'id_harga_produk'
        );
    }
}