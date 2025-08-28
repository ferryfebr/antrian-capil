<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
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
        $request->validate([
            'username' => 'required|string|max:50',
            'password' => 'required|string'
        ], [
            'username.required' => 'Username wajib diisi',
            'password.required' => 'Password wajib diisi'
        ]);

        $credentials = $request->only('username', 'password');
        
        // Remember me functionality (optional)
        $remember = $request->boolean('remember');

        if (Auth::guard('admin')->attempt($credentials, $remember)) {
            // Regenerate session for security
            $request->session()->regenerate();
            
            // Get authenticated admin
            $admin = Auth::guard('admin')->user();
            
            // Log login activity (optional)
            \Log::info('Admin login successful', [
                'admin_id' => $admin->id_admin,
                'username' => $admin->username,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);

            return redirect()->intended(route('dashboard'))
                ->with('success', "Selamat datang, {$admin->nama_admin}!");
        }

        // Login failed
        \Log::warning('Admin login failed', [
            'username' => $request->username,
            'ip_address' => $request->ip()
        ]);

        return back()->withErrors([
            'username' => 'Username atau password tidak sesuai.',
        ])->onlyInput('username');
    }

    /**
     * Handle logout
     */
    public function logout(Request $request)
    {
        $admin = Auth::guard('admin')->user();
        
        if ($admin) {
            // Log logout activity
            \Log::info('Admin logout', [
                'admin_id' => $admin->id_admin,
                'username' => $admin->username,
                'ip_address' => $request->ip()
            ]);
        }

        Auth::guard('admin')->logout();

        // Invalidate session
        $request->session()->invalidate();

        // Regenerate CSRF token
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'Anda telah logout.');
    }

    /**
     * Check auth status for AJAX requests
     */
    public function checkAuth()
    {
        $isAuthenticated = Auth::guard('admin')->check();
        
        if ($isAuthenticated) {
            $admin = Auth::guard('admin')->user();
            return response()->json([
                'authenticated' => true,
                'admin' => [
                    'id' => $admin->id_admin,
                    'username' => $admin->username,
                    'nama_admin' => $admin->nama_admin
                ]
            ]);
        }

        return response()->json(['authenticated' => false], 401);
    }

    /**
     * Refresh session (for AJAX requests to prevent timeout)
     */
    public function refreshSession(Request $request)
    {
        if (Auth::guard('admin')->check()) {
            $request->session()->regenerate();
            return response()->json(['success' => true]);
        }

        return response()->json(['success' => false], 401);
    }
}