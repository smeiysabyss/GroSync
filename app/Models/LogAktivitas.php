<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class LogAktivitas extends Model
{
    protected $table      = 'log_aktivitas';
    protected $primaryKey = 'id_log';

    protected $fillable = [
        'id_users',
        'modul',    // ← baru
        'activity',
    ];

    // Relasi ke user
    public function user()
    {
        return $this->belongsTo(\App\Models\User::class, 'id_users', 'id');
    }

    // ============================================================
    // Helper: catat log
    // ============================================================
    public static function catat(string $modul, string $activity): void
    {
        static::create([
            'id_users' => Auth::id(),
            'modul'    => $modul,
            'activity' => $activity,
        ]);
    }
}