<?php
// app/Models/Admin/Satuan.php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Satuan extends Model
{
    use HasFactory;

    protected $table      = 'units';
    protected $primaryKey = 'id_unit';

    protected $fillable = ['satuan'];

    /** Relasi ke HargaProduk (untuk proteksi hapus) */
    public function hargaProduk()
    {
        return $this->hasMany(HargaProduk::class, 'id_unit', 'id_unit');
    }
}