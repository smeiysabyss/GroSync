<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kategori extends Model
{
    use HasFactory;

    protected $table      = 'kategori_produk';
    protected $primaryKey = 'id_kategori';

    protected $fillable = [
        'nama_kategori',
        'gambar',           
    ];

    public function produk()
    {
        return $this->hasMany(
            Produk::class,
            'id_kategori',
            'id_kategori'
        );
    }
}