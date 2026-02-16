<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    // Tampilkan Halaman Login
    public function showLogin()
    {
        return view('auth.login');
    }

    // Proses Login
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'username' => ['required'],
            'pin'      => ['required'],
        ]);

        // Auth::attempt akan otomatis mengecek username & mencocokkan pin (yang sudah di-hash)
        // Kita mapping input 'pin' ke key 'password' karena Auth::attempt butuh key 'password' secara default
        if (Auth::attempt(['username' => $request->username, 'password' => $request->pin])) {
            $request->session()->regenerate();

            return redirect()->intended('dashboard');
        }

        return back()->withErrors([
            'username' => 'Username atau PIN salah.',
        ]);
    }

    // Proses Logout
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login');
    }
}
