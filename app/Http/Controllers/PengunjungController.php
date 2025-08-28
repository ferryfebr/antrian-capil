<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Pengunjung;
use Illuminate\Http\Request;

class PengunjungController extends Controller
{
    public function index(Request $request)
    {
        $query = Pengunjung::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nik', 'like', "%{$search}%")
                  ->orWhere('nama_pengunjung', 'like', "%{$search}%")
                  ->orWhere('no_hp', 'like', "%{$search}%");
            });
        }

        $pengunjungs = $query->orderBy('waktu_daftar', 'desc')->paginate(15);
        
        return view('pengunjung.index', compact('pengunjungs'));
    }

    public function show($id)
    {
        $pengunjung = Pengunjung::with(['antrian.layanan', 'antrian.admin'])
            ->findOrFail($id);
        return view('pengunjung.show', compact('pengunjung'));
    }

    public function destroy($id)
    {
        $pengunjung = Pengunjung::findOrFail($id);
        
        // Check if has active queues
        $hasActiveQueue = $pengunjung->antrian()
            ->whereIn('status_antrian', ['menunggu', 'dipanggil'])
            ->exists();
            
        if ($hasActiveQueue) {
            return redirect()->route('pengunjung.index')
                ->with('error', 'Tidak dapat menghapus pengunjung yang masih memiliki antrian aktif!');
        }

        $pengunjung->delete();

        return redirect()->route('pengunjung.index')->with('success', 'Data pengunjung berhasil dihapus!');
    }
}