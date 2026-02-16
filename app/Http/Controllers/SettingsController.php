<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class SettingsController extends Controller
{
    // Menampilkan halaman pengaturan
    public function index()
    {
        $user = Auth::user();
        return view('settings.index', compact('user'));
    }

    // Mengubah PIN pengguna
    public function changePin(Request $request)
    {
        $request->validate([
            'current_pin' => ['required', 'string'],
            'new_pin' => ['required', 'string', 'min:4', 'max:6', 'confirmed'],
        ]);

        $user = Auth::user();

        // Verifikasi PIN saat ini
        if (!Hash::check($request->current_pin, $user->pin)) {
            return back()->withErrors(['current_pin' => 'PIN saat ini salah.'])->withInput();
        }

        // Update PIN baru
        $user->pin = Hash::make($request->new_pin);
        $user->save();

        return redirect()->route('settings.index')
            ->with('success', 'PIN berhasil diubah.');
    }
}