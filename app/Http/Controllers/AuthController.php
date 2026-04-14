<?php
// app/Http/Controllers/AuthController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\LogAktivitas;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showLogin()
    {
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

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return back()->withErrors([
                'email' => 'Email tidak terdaftar dalam sistem.',
            ])->withInput($request->only('email'));
        }

        if (!Hash::check($request->password, $user->password)) {
            return back()->withErrors([
                'password' => 'Password yang kamu masukkan salah.',
            ])->withInput($request->only('email'));
        }

        if ($user->status !== 'aktif') {
            return back()->withErrors([
                'email' => 'Akun Anda telah dinonaktifkan. Hubungi administrator.',
            ])->withInput($request->only('email'));
        }

        Auth::login($user);
        $request->session()->regenerate();

        LogAktivitas::catat('auth', "Login sebagai {$user->role} — {$user->username}.");

        return $this->redirectByRole($user->role);
    }

    public function logout(Request $request)
    {
        $username = Auth::user()->username ?? '-';
        $role     = Auth::user()->role ?? '-';

        LogAktivitas::catat('auth', "Logout — {$username} ({$role}).");

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login');
    }

    private function redirectByRole(string $role)
    {
        return match($role) {
            'owner'         => redirect('/owner/dashboard'),
            'administrator' => redirect('/admin/dashboard'),
            default         => redirect('/kasir/dashboard'),
        };
    }
}