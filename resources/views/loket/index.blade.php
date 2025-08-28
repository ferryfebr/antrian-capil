@extends('layouts.app')

@section('title', 'Kelola Loket')
@section('page-title', 'Kelola Loket')

@section('page-actions')
<a href="{{ route('loket.create') }}" class="btn btn-primary">
    <i class="fas fa-plus me-2"></i>Tambah Loket
</a>
@endsection

@section('content')
<!-- Filter Section -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('loket.index') }}" class="row g-3">
            <div class="col-md-4">
                <label for="status" class="form-label">Status Loket</label>
                <select class="form-select" id="status" name="status">
                    <option value="">Semua Status</option>
                    <option value="aktif" {{ request('status') == 'aktif' ? 'selected' : '' }}>Aktif</option>
                    <option value="tidak_aktif" {{ request('status') == 'tidak_aktif' ? 'selected' : '' }}>Tidak Aktif</option>
                </select>
            </div>
            <div class="col-md-4">
                <label for="layanan" class="form-label">Layanan</label>
                <select class="form-select" id="layanan" name="layanan">
                    <option value="">Semua Layanan</option>
                    @foreach(\App\Models\Layanan::where('aktif', true)->get() as $layanan)
                        <option value="{{ $layanan->id_layanan }}" {{ request('layanan') == $layanan->id_layanan ? 'selected' : '' }}>
                            {{ $layanan->nama_layanan }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">&nbsp;</label>
                <div class="d-grid">
                    <button type="submit" class="btn btn-outline-primary">
                        <i class="fas fa-search me-2"></i>Filter
                    </button>
                </div>
            </div>
            <div class="col-md-2">
                <label class="form-label">&nbsp;</label>
                <div class="d-grid">
                    <a href="{{ route('loket.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-times me-2"></i>Reset
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Loket Table -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">
            <i class="fas fa-desktop me-2"></i>Data Loket
        </h5>
        <small class="text-muted">
            Total: {{ $lokets->total() }} loket
        </small>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Nama Loket</th>
                        <th>Layanan</th>
                        <th>Status</th>
                        <th>Deskripsi</th>
                        <th>Dibuat</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($lokets as $loket)
                    <tr>
                        <td>
                            <span class="badge bg-secondary">{{ $loket->id_loket }}</span>
                        </td>
                        <td>
                            <strong>{{ $loket->nama_loket }}</strong>
                        </td>
                        <td>
                            @if($loket->layanan)
                                <span class="badge bg-info">{{ $loket->layanan->nama_layanan }}</span>
                                <br><small class="text-muted">{{ $loket->layanan->kode_layanan }}</small>
                            @else
                                <span class="text-muted">Tidak ada</span>
                            @endif
                        </td>
                        <td>
                            @if($loket->status_loket == 'aktif')
                                <span class="badge bg-success">
                                    <i class="fas fa-check me-1"></i>Aktif
                                </span>
                            @else
                                <span class="badge bg-danger">
                                    <i class="fas fa-times me-1"></i>Tidak Aktif
                                </span>
                            @endif
                        </td>
                        <td>
                            @if($loket->deskripsi_loket)
                                <small>{{ Str::limit($loket->deskripsi_loket, 50) }}</small>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            <small class="text-muted">{{ $loket->created_at->format('d/m/Y') }}</small>
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm" role="group">
                                <a href="{{ route('loket.edit', $loket->id_loket) }}" 
                                   class="btn btn-outline-primary" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                
                                <form action="{{ route('loket.destroy', $loket->id_loket) }}" 
                                      method="POST" style="display: inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger" title="Hapus"
                                            onclick="return confirm('Yakin ingin menghapus loket ini?')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-4">
                            <div class="text-muted">
                                <i class="fas fa-desktop fa-3x mb-3"></i>
                                <p>Tidak ada data loket yang ditemukan</p>
                                <a href="{{ route('loket.create') }}" class="btn btn-primary">
                                    <i class="fas fa-plus me-2"></i>Tambah Loket Pertama
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        @if($lokets->hasPages())
            <div class="d-flex justify-content-center mt-4">
                {{ $lokets->appends(request()->query())->links() }}
            </div>
        @endif
    </div>
</div>

<!-- Statistik Loket -->
<div class="row mt-4">
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <div class="h4 text-primary">{{ $lokets->total() }}</div>
                <small>Total Loket</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <div class="h4 text-success">{{ \App\Models\Loket::where('status_loket', 'aktif')->count() }}</div>
                <small>Loket Aktif</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <div class="h4 text-warning">{{ \App\Models\Loket::where('status_loket', 'tidak_aktif')->count() }}</div>
                <small>Loket Tidak Aktif</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <div class="h4 text-info">{{ \App\Models\Loket::whereNull('id_layanan')->count() }}</div>
                <small>Tanpa Layanan</small>
            </div>
        </div>
    </div>
</div>

@endsection