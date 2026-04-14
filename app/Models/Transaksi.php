<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaksi extends Model
{
    protected $table      = 'transaksi';
    protected $primaryKey = 'id_transaksi';

    protected $fillable = [
        'id_users',
        'nomor_unik',
        'nama_pelanggan',
        'uang_bayar',
        'total',        
        'kembalian',    
        'status',      
    ];

    protected $casts = [
        'uang_bayar' => 'float',
        'total'      => 'float',
        'kembalian'  => 'float',
    ];

    // ============================================================
    // Relasi
    // ============================================================

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class, 'id_users', 'id');
    }

    public function detail()
    {
        return $this->hasMany(DetailTransaksi::class, 'id_transaksi', 'id_transaksi');
    }

    // ============================================================
    // Helper
    // ============================================================

    // Generate nomor unik: TRX-YYYYMMDD-XXXXX
    public static function generateNomorUnik(): string
    {
        $prefix = 'TRX-' . now()->format('Ymd') . '-';
        $last   = static::where('nomor_unik', 'like', $prefix . '%')
                        ->orderByDesc('id_transaksi')
                        ->value('nomor_unik');

        $urutan = $last ? ((int) substr($last, -5)) + 1 : 1;
        return $prefix . str_pad($urutan, 5, '0', STR_PAD_LEFT);
    }

    // Scope: hanya transaksi selesai
    public function scopeSelesai($query)
    {
        return $query->where('status', 'selesai');
    }

    // Scope: hanya transaksi dibatalkan
    public function scopeDibatalkan($query)
    {
        return $query->where('status', 'dibatalkan');
    }

    
}