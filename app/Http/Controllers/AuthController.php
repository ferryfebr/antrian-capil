<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Admin;

class AuthController extends Controller
{
    /**
     * Show login form
     */
    public function showLoginForm()
    {
        // Redirect if already authenticated
        if (Auth::guard('admin')->check()) {
            return redirect()->route('dashboard');
        }

        return view('auth.login');
    }

    /**
     * Handle login
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        if (Auth::guard('admin')->attempt($credentials)) {
            // Regenerate session for security
            $request->session()->regenerate();

            return redirect()->intended(route('dashboard'));
        }

        return back()
            ->withErrors([
                'username' => 'Username atau password salah.',
            ])->withInput($request->only('username'));
    }

    /**
     * Handle logout
     */
    public function logout(Request $request)
    {
        Auth::guard('admin')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}