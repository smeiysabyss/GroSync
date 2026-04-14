<?php
// app/Http/Controllers/Admin/PenggunaController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\LogAktivitas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class PenggunaController extends Controller
{
    public function index()
    {
        $pengguna = User::orderBy('created_at', 'asc')->paginate(10);
        return view('admin.pengguna.index', compact('pengguna'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'email'    => 'required|email|unique:users,email',
            'username' => 'required|string|max:100|unique:users,username',
            'password' => 'required|string|min:8',
            'role'     => 'required|in:administrator,kasir,owner',
        ], [
            'email.unique'    => 'Email sudah digunakan.',
            'username.unique' => 'Username sudah digunakan.',
            'password.min'    => 'Password minimal 8 karakter.',
        ]);

        User::create([
            'email'    => $request->email,
            'username' => $request->username,
            'password' => Hash::make($request->password),
            'role'     => $request->role,
            'status'   => 'aktif',
        ]);

        LogAktivitas::catat('pengguna', "Menambahkan pengguna \"{$request->username}\" dengan role {$request->role}.");

        return redirect()->route('admin.pengguna.index')
            ->with('success', 'Pengguna berhasil ditambahkan!');
    }

    public function update(Request $request, User $pengguna)
    {
        $request->validate([
            'email'    => ['required', 'email', Rule::unique('users', 'email')->ignore($pengguna->id)],
            'username' => ['required', 'string', 'max:100', Rule::unique('users', 'username')->ignore($pengguna->id)],
            'password' => 'nullable|string|min:8',
            'role'     => 'required|in:administrator,kasir,owner',
        ], [
            'email.unique'    => 'Email sudah digunakan.',
            'username.unique' => 'Username sudah digunakan.',
            'password.min'    => 'Password minimal 8 karakter.',
        ]);

        $data = [
            'email'    => $request->email,
            'username' => $request->username,
            'role'     => $request->role,
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $pengguna->update($data);

        LogAktivitas::catat('pengguna', "Mengubah data pengguna \"{$pengguna->username}\".");

        return redirect()->route('admin.pengguna.index')
            ->with('success', 'Pengguna berhasil diperbarui!');
    }

    public function toggleStatus(User $pengguna)
    {
        if ($pengguna->id === Auth::id()) {
            return redirect()->route('admin.pengguna.index')
                ->with('error', 'Anda tidak dapat menonaktifkan akun Anda sendiri!');
        }

        $statusLama = $pengguna->status;

        $pengguna->update([
            'status' => $statusLama === 'aktif' ? 'nonaktif' : 'aktif',
        ]);

        $statusLabel = $statusLama === 'aktif' ? 'dinonaktifkan' : 'diaktifkan';

        LogAktivitas::catat('pengguna', "Pengguna \"{$pengguna->username}\" {$statusLabel}.");

        return redirect()->route('admin.pengguna.index')
            ->with('success', "Pengguna {$pengguna->username} berhasil {$statusLabel}!");
    }

    public function destroy(User $pengguna)
    {
        if ($pengguna->id === Auth::id()) {
            return redirect()->route('admin.pengguna.index')
                ->with('error', 'Anda tidak dapat menghapus akun Anda sendiri!');
        }

        $nama = $pengguna->username;
        $pengguna->delete();

        LogAktivitas::catat('pengguna', "Menghapus pengguna \"{$nama}\".");

        return redirect()->route('admin.pengguna.index')
            ->with('success', "Pengguna {$nama} berhasil dihapus!");
    }
}