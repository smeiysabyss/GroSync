<?php
// app/Models/Admin/Produk.php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Produk extends Model
{
    use HasFactory;

    protected $table      = 'produk';
    protected $primaryKey = 'id_produk';

    protected $fillable = [
        'id_kategori',
        'nama_produk',
        'deskripsi',
        'gambar',
        'tanggal_kadaluarsa',
    ];

    protected $casts = [
        'tanggal_kadaluarsa' => 'date',
    ];

    /** Relasi ke Kategori */
    public function kategori()
    {
        return $this->belongsTo(Kategori::class, 'id_kategori', 'id_kategori');
    }

    /** Relasi ke HargaProduk */
    public function hargaProduk()
    {
        return $this->hasMany(HargaProduk::class, 'id_produk', 'id_produk');
    }
}