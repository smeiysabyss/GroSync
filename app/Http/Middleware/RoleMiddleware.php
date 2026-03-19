<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, ...$roles)
    {
        // Belum login → redirect ke login
        if (!Auth::check()) {
            return redirect('/login');
        }

        // Sudah login tapi role tidak sesuai → redirect ke halaman login
        // dengan pesan error, BUKAN abort(403)
        if (!in_array(Auth::user()->role, $roles)) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect('/login')->withErrors([
                'email' => 'Anda tidak memiliki akses ke halaman tersebut.'
            ]);
        }

        return $next($request);
    }
}