<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class EmployeeController extends Controller
{
    // Menampilkan daftar karyawan
    public function index()
    {
        $employees = User::where('role', 'employee')->latest()->get();
        return view('employee.index', compact('employees'));
    }

    // Menampilkan form tambah karyawan
    public function create()
    {
        return view('employee.create');
    }

    // Menyimpan karyawan baru
    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', 'unique:users'],
            'pin' => ['required', 'string', 'min:4', 'max:6', 'confirmed'],
        ]);

        User::create([
            'name' => $request->name,
            'username' => $request->username,
            'pin' => Hash::make($request->pin),
            'role' => 'employee',
        ]);

        return redirect()->route('employee.index')
            ->with('success', 'Karyawan berhasil ditambahkan.');
    }

    // Menampilkan form edit karyawan
    public function edit(User $user)
    {
        // Pastikan hanya karyawan yang bisa diedit
        if ($user->role !== 'employee') {
            abort(403, 'Hanya karyawan yang bisa diedit.');
        }

        return view('employee.edit', compact('user'));
    }

    // Mengupdate data karyawan
    public function update(Request $request, User $user)
    {
        // Pastikan hanya karyawan yang bisa diupdate
        if ($user->role !== 'employee') {
            abort(403, 'Hanya karyawan yang bisa diupdate.');
        }

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', 'unique:users,username,' . $user->id],
            'pin' => ['nullable', 'string', 'min:4', 'max:6', 'confirmed'],
        ]);

        $data = [
            'name' => $request->name,
            'username' => $request->username,
        ];

        // Update PIN jika diisi
        if ($request->filled('pin')) {
            $data['pin'] = Hash::make($request->pin);
        }

        $user->update($data);

        return redirect()->route('employee.index')
            ->with('success', 'Data karyawan berhasil diperbarui.');
    }

    // Menghapus karyawan
    public function destroy(User $user)
    {
        // Pastikan hanya karyawan yang bisa dihapus
        if ($user->role !== 'employee') {
            abort(403, 'Hanya karyawan yang bisa dihapus.');
        }

        $user->delete();

        return redirect()->route('employee.index')
            ->with('success', 'Karyawan berhasil dihapus.');
    }
}