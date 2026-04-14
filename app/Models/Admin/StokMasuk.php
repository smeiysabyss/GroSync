<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class StokMasuk extends Model
{
    protected $table      = 'stok_masuk';
    protected $primaryKey = 'id_stok_masuk';

    protected $fillable = [
        'id_harga_produk',
        'id_users',
        'jenis',
        'jumlah',
        'sisa_stok',
        'harga_beli',       
        'tanggal_kadaluarsa',
        'tanggal_masuk',
    ];

    protected $casts = [
        'tanggal_kadaluarsa' => 'date',
        'tanggal_masuk'      => 'date',
        'jumlah'             => 'integer',
        'harga_beli'         => 'decimal:2',  
    ];

    public function hargaProduk()
    {
        return $this->belongsTo(HargaProduk::class, 'id_harga_produk', 'id_harga_produk');
    }

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class, 'id_users', 'id');
    }
}