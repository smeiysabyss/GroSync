<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\LogAktivitas;
use App\Models\User;
use Illuminate\Http\Request;

class LogController extends Controller
{
    public function index(Request $request)
    {
        $query = LogAktivitas::with('user')->latest();

        if ($request->filled('dari')) {
            $query->whereDate('created_at', '>=', $request->dari);
        }

        if ($request->filled('sampai')) {
            $query->whereDate('created_at', '<=', $request->sampai);
        }

        if ($request->filled('modul')) {
            $query->where('modul', $request->modul);
        }

        if ($request->filled('user')) {
            $query->where('id_users', $request->user);
        }

        $logs   = $query->paginate(25);
        $moduls = LogAktivitas::distinct()->pluck('modul')->filter()->sort()->values();
        $users  = User::orderBy('username')->get();

        return view('owner.log.index', compact('logs', 'moduls', 'users'));
    }
}