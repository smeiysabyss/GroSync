<?php
namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HargaProduk extends Model
{
    use HasFactory;

    protected $table      = 'harga_produk';
    protected $primaryKey = 'id_harga_produk';

    protected $fillable = [
        'id_produk',
        'id_kategori',
        'id_unit',
        'harga',
        'stok',
        'catatan',
    ];

    protected $casts = [
        'harga' => 'decimal:2',
        'stok'  => 'integer',
    ];

    /** Relasi ke Produk */
    public function produk()
    {
        return $this->belongsTo(Produk::class, 'id_produk', 'id_produk');
    }

    /** Relasi ke Satuan (units) */
    public function unit()
    {
        return $this->belongsTo(Satuan::class, 'id_unit', 'id_unit');
    }

    /** Relasi ke Kategori */
    public function kategori()
    {
        return $this->belongsTo(Kategori::class, 'id_kategori', 'id_kategori');
    }

     public function detailTransaksi()
    {
        return $this->hasMany(
            \App\Models\DetailTransaksi::class,
            'id_harga_produk',
            'id_harga_produk'
        );
    }
}