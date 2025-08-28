<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class AdminController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Admin::query();

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('username', 'like', "%{$search}%")
                  ->orWhere('nama_admin', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $admins = $query->withCount(['layanan', 'antrian'])
                       ->orderBy('created_at', 'desc')
                       ->paginate(10);

        return view('admin.index', compact('admins'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'username' => [
                'required',
                'string',
                'max:50',
                'unique:admin,username',
                'regex:/^[a-zA-Z0-9_]+$/' // Only alphanumeric and underscore
            ],
            'password' => [
                'required',
                'string',
                'min:6',
                'confirmed'
            ],
            'nama_admin' => [
                'required',
                'string',
                'max:100'
            ],
            'email' => [
                'nullable',
                'email',
                'max:100',
                'unique:admin,email'
            ]
        ], [
            'username.required' => 'Username wajib diisi',
            'username.unique' => 'Username sudah digunakan',
            'username.regex' => 'Username hanya boleh mengandung huruf, angka, dan underscore',
            'password.required' => 'Password wajib diisi',
            'password.min' => 'Password minimal 6 karakter',
            'password.confirmed' => 'Konfirmasi password tidak sesuai',
            'nama_admin.required' => 'Nama admin wajib diisi',
            'email.unique' => 'Email sudah digunakan'
        ]);

        try {
            $admin = Admin::create([
                'username' => $request->username,
                'password' => Hash::make($request->password),
                'nama_admin' => $request->nama_admin,
                'email' => $request->email,
            ]);

            // Log activity
            \Log::info('New admin created', [
                'created_by' => Auth::guard('admin')->id(),
                'new_admin_id' => $admin->id_admin,
                'username' => $admin->username
            ]);

            return redirect()->route('admin.index')
                ->with('success', 'Admin baru berhasil ditambahkan!');

        } catch (\Exception $e) {
            \Log::error('Failed to create admin', [
                'error' => $e->getMessage(),
                'data' => $request->except('password')
            ]);

            return back()->withErrors(['error' => 'Gagal membuat admin baru. Silakan coba lagi.'])
                        ->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $admin = Admin::withCount(['layanan', 'antrian'])
                    ->with(['layanan' => function($query) {
                        $query->orderBy('created_at', 'desc');
                    }])
                    ->findOrFail($id);

        // Get recent activities
        $recent_activities = $admin->antrian()
            ->with(['pengunjung', 'layanan'])
            ->orderBy('updated_at', 'desc')
            ->limit(10)
            ->get();

        return view('admin.show', compact('admin', 'recent_activities'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $admin = Admin::findOrFail($id);
        
        // Check if trying to edit own account
        $isOwnAccount = Auth::guard('admin')->id() == $admin->id_admin;

        return view('admin.edit', compact('admin', 'isOwnAccount'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $admin = Admin::findOrFail($id);
        
        $request->validate([
            'username' => [
                'required',
                'string',
                'max:50',
                Rule::unique('admin', 'username')->ignore($admin->id_admin, 'id_admin'),
                'regex:/^[a-zA-Z0-9_]+$/'
            ],
            'password' => [
                'nullable',
                'string',
                'min:6',
                'confirmed'
            ],
            'nama_admin' => [
                'required',
                'string',
                'max:100'
            ],
            'email' => [
                'nullable',
                'email',
                'max:100',
                Rule::unique('admin', 'email')->ignore($admin->id_admin, 'id_admin')
            ]
        ], [
            'username.required' => 'Username wajib diisi',
            'username.unique' => 'Username sudah digunakan',
            'username.regex' => 'Username hanya boleh mengandung huruf, angka, dan underscore',
            'password.min' => 'Password minimal 6 karakter',
            'password.confirmed' => 'Konfirmasi password tidak sesuai',
            'nama_admin.required' => 'Nama admin wajib diisi',
            'email.unique' => 'Email sudah digunakan'
        ]);

        try {
            $data = [
                'username' => $request->username,
                'nama_admin' => $request->nama_admin,
                'email' => $request->email,
            ];

            // Only update password if provided
            if ($request->filled('password')) {
                $data['password'] = Hash::make($request->password);
            }

            $admin->update($data);

            // Log activity
            \Log::info('Admin updated', [
                'updated_by' => Auth::guard('admin')->id(),
                'admin_id' => $admin->id_admin,
                'changes' => $request->except(['password', 'password_confirmation'])
            ]);

            $message = 'Admin berhasil diperbarui!';
            
            // Additional message if updating own account
            if (Auth::guard('admin')->id() == $admin->id_admin) {
                $message .= ' Perubahan pada akun Anda telah disimpan.';
            }

            return redirect()->route('admin.index')->with('success', $message);

        } catch (\Exception $e) {
            \Log::error('Failed to update admin', [
                'error' => $e->getMessage(),
                'admin_id' => $id,
                'data' => $request->except(['password', 'password_confirmation'])
            ]);

            return back()->withErrors(['error' => 'Gagal memperbarui admin. Silakan coba lagi.'])
                        ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $admin = Admin::findOrFail($id);
        
        // Prevent self-deletion
        if (Auth::guard('admin')->id() == $admin->id_admin) {
            return redirect()->route('admin.index')
                ->with('error', 'Anda tidak dapat menghapus akun Anda sendiri!');
        }

        // Check if admin has responsibilities
        if ($admin->hasActiveResponsibilities()) {
            return redirect()->route('admin.index')
                ->with('error', 'Admin ini masih memiliki tanggung jawab aktif (layanan atau antrian). Tidak dapat dihapus!');
        }

        try {
            $username = $admin->username;
            $admin->delete();

            // Log activity
            \Log::info('Admin deleted', [
                'deleted_by' => Auth::guard('admin')->id(),
                'deleted_admin_username' => $username,
                'deleted_admin_id' => $id
            ]);

            return redirect()->route('admin.index')
                ->with('success', "Admin {$username} berhasil dihapus!");

        } catch (\Exception $e) {
            \Log::error('Failed to delete admin', [
                'error' => $e->getMessage(),
                'admin_id' => $id
            ]);

            return redirect()->route('admin.index')
                ->with('error', 'Gagal menghapus admin. Silakan coba lagi.');
        }
    }

    /**
     * Change admin password
     */
    public function changePassword(Request $request, $id)
    {
        $admin = Admin::findOrFail($id);
        
        // Only allow changing own password or by other admin
        if (Auth::guard('admin')->id() != $admin->id_admin && !Auth::guard('admin')->check()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $request->validate([
            'current_password' => 'required_if:self,true',
            'new_password' => 'required|min:6|confirmed',
        ]);

        // Verify current password if changing own password
        if ($request->boolean('self') && !Hash::check($request->current_password, $admin->password)) {
            return response()->json(['error' => 'Password saat ini tidak sesuai'], 400);
        }

        try {
            $admin->update(['password' => Hash::make($request->new_password)]);

            \Log::info('Admin password changed', [
                'changed_by' => Auth::guard('admin')->id(),
                'admin_id' => $admin->id_admin
            ]);

            return response()->json(['success' => 'Password berhasil diubah']);

        } catch (\Exception $e) {
            \Log::error('Failed to change admin password', [
                'error' => $e->getMessage(),
                'admin_id' => $id
            ]);

            return response()->json(['error' => 'Gagal mengubah password'], 500);
        }
    }

    /**
     * Get admin statistics
     */
    public function getStats($id)
    {
        $admin = Admin::withCount(['layanan', 'antrian'])->findOrFail($id);
        
        $stats = [
            'total_layanan' => $admin->layanan_count,
            'layanan_aktif' => $admin->layanan()->where('aktif', true)->count(),
            'total_antrian' => $admin->antrian_count,
            'antrian_hari_ini' => $admin->antrian()->whereDate('created_at', today())->count(),
            'antrian_selesai' => $admin->antrian()->where('status_antrian', 'selesai')->count(),
        ];

        return response()->json($stats);
    }

    /**
     * Toggle admin status (if implementing status feature)
     */
    public function toggleStatus($id)
    {
        $admin = Admin::findOrFail($id);
        
        // Prevent disabling own account
        if (Auth::guard('admin')->id() == $admin->id_admin) {
            return back()->with('error', 'Anda tidak dapat menonaktifkan akun Anda sendiri!');
        }

        // Implementation would depend on if you add status field to admin table
        // For now, this is just a placeholder
        
        return back()->with('success', 'Status admin berhasil diubah!');
    }

    /**
     * Export admin data
     */
    public function export()
    {
        $admins = Admin::withCount(['layanan', 'antrian'])->get();

        $filename = 'admin_data_' . date('Y-m-d_H-i-s') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function() use ($admins) {
            $file = fopen('php://output', 'w');
            
            // Header CSV
            fputcsv($file, [
                'ID Admin',
                'Username',
                'Nama Admin',
                'Email',
                'Jumlah Layanan',
                'Jumlah Antrian',
                'Tanggal Bergabung',
                'Terakhir Update'
            ]);
            
            // Data
            foreach ($admins as $admin) {
                fputcsv($file, [
                    $admin->id_admin,
                    $admin->username,
                    $admin->nama_admin,
                    $admin->email ?? '-',
                    $admin->layanan_count,
                    $admin->antrian_count,
                    $admin->created_at->format('d/m/Y H:i'),
                    $admin->updated_at->format('d/m/Y H:i')
                ]);
            }
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Bulk delete admins
     */
    public function bulkDelete(Request $request)
    {
        $request->validate([
            'admin_ids' => 'required|array',
            'admin_ids.*' => 'exists:admin,id_admin'
        ]);

        $adminIds = $request->admin_ids;
        $currentAdminId = Auth::guard('admin')->id();
        
        // Remove current admin from deletion list
        $adminIds = array_filter($adminIds, function($id) use ($currentAdminId) {
            return $id != $currentAdminId;
        });

        if (empty($adminIds)) {
            return back()->with('error', 'Tidak ada admin yang dapat dihapus.');
        }

        try {
            $deleted = 0;
            $errors = [];

            foreach ($adminIds as $id) {
                $admin = Admin::find($id);
                if ($admin && !$admin->hasActiveResponsibilities()) {
                    $admin->delete();
                    $deleted++;
                } else {
                    $errors[] = "Admin {$admin->username} masih memiliki tanggung jawab aktif";
                }
            }

            $message = "Berhasil menghapus {$deleted} admin.";
            if (!empty($errors)) {
                $message .= " Gagal: " . implode(', ', $errors);
            }

            return back()->with($deleted > 0 ? 'success' : 'warning', $message);

        } catch (\Exception $e) {
            \Log::error('Bulk delete admin failed', ['error' => $e->getMessage()]);
            return back()->with('error', 'Gagal melakukan penghapusan massal.');
        }
    }
}