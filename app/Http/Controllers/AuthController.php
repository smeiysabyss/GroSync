<?php
// app/Http/Controllers/AuthController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLogin()
    {
        // Jika sudah login, redirect ke dashboard sesuai role
        if (Auth::check()) {
            return $this->redirectByRole(Auth::user()->role);
        }

        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
            $request->session()->regenerate();

            // BUG FIX 2: Cek status akun setelah berhasil login
            if (Auth::user()->status !== 'aktif') {
                // Langsung logout jika akun nonaktif
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return back()->withErrors([
                    'email' => 'Akun Anda telah dinonaktifkan. Hubungi administrator.'
                ]);
            }

            return $this->redirectByRole(Auth::user()->role);
        }

        return back()->withErrors(['email' => 'Email atau password salah!']);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login');
    }

    /**
     * Redirect berdasarkan role user
     */
    private function redirectByRole(string $role)
    {
        return match($role) {
            'owner'         => redirect('/owner/dashboard'),
            'administrator' => redirect('/admin/dashboard'),
            default         => redirect('/kasir/dashboard'),
        };
    }
}