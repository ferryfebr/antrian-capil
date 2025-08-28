<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Loket;
use App\Models\Layanan;
use Illuminate\Http\Request;

class LoketController extends Controller
{
    public function index()
    {
        $lokets = Loket::with('layanan')->paginate(10);
        return view('loket.index', compact('lokets'));
    }

    public function create()
    {
        $layanans = Layanan::where('aktif', true)->get();
        return view('loket.create', compact('layanans'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_loket' => 'required|max:50',
            'status_loket' => 'required|in:aktif,tidak_aktif',
            'deskripsi_loket' => 'nullable',
            'id_layanan' => 'nullable|exists:layanan,id_layanan'
        ]);

        Loket::create($request->all());

        return redirect()->route('loket.index')->with('success', 'Loket berhasil ditambahkan!');
    }

    public function edit($id)
    {
        $loket = Loket::findOrFail($id);
        $layanans = Layanan::where('aktif', true)->get();
        return view('loket.edit', compact('loket', 'layanans'));
    }

    public function update(Request $request, $id)
    {
        $loket = Loket::findOrFail($id);
        
        $request->validate([
            'nama_loket' => 'required|max:50',
            'status_loket' => 'required|in:aktif,tidak_aktif',
            'deskripsi_loket' => 'nullable',
            'id_layanan' => 'nullable|exists:layanan,id_layanan'
        ]);

        $loket->update($request->all());

        return redirect()->route('loket.index')->with('success', 'Loket berhasil diupdate!');
    }

    public function destroy($id)
    {
        $loket = Loket::findOrFail($id);
        $loket->delete();

        return redirect()->route('loket.index')->with('success', 'Loket berhasil dihapus!');
    }
}