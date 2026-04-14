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
        'harga',        // harga beli awal (untuk stok awal)
        'harga_jual',   // HARGA JUAL ke customer
        'stok',
        'catatan',
    ];

    protected $casts = [
        'harga'      => 'decimal:2',
        'harga_jual' => 'decimal:2',
        'stok'       => 'integer',
    ];

    public function produk()
    {
        return $this->belongsTo(Produk::class, 'id_produk', 'id_produk');
    }

    public function unit()
    {
        return $this->belongsTo(Satuan::class, 'id_unit', 'id_unit');
    }

    public function kategori()
    {
        return $this->belongsTo(Kategori::class, 'id_kategori', 'id_kategori');
    }

    public function detailTransaksi()
    {
        return $this->hasMany(\App\Models\DetailTransaksi::class, 'id_harga_produk', 'id_harga_produk');
    }

    public function stokMasuk()
    {
        return $this->hasMany(StokMasuk::class, 'id_harga_produk', 'id_harga_produk');
    }
}