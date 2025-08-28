@extends('layouts.app')

@section('title', 'Kelola Antrian')
@section('page-title', 'Kelola Antrian')

@section('page-actions')
<div class="btn-group">
    <a href="{{ route('antrian.create') }}" class="btn btn-primary">
        <i class="fas fa-plus me-2"></i>Buat Antrian
    </a>
    <div class="dropdown">
        <button class="btn btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
            <i class="fas fa-cog me-2"></i>Actions
        </button>
        <ul class="dropdown-menu">
            <li><a class="dropdown-item" href="#" onclick="callNextQueue()">
                <i class="fas fa-volume-up me-2"></i>Panggil Berikutnya
            </a></li>
            <li><a class="dropdown-item" href="#" onclick="exportData()">
                <i class="fas fa-download me-2"></i>Export Data
            </a></li>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item" href="{{ route('public.queue') }}" target="_blank">
                <i class="fas fa-tv me-2"></i>Buka Display
            </a></li>
        </ul>
    </div>
</div>
@endsection

@section('content')
<!-- Filter Section -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('antrian.index') }}" class="row g-3" id="filterForm">
            <div class="col-md-2">
                <label for="tanggal" class="form-label">Tanggal</label>
                <input type="date" class="form-control" id="tanggal" name="tanggal" 
                       value="{{ request('tanggal', today()->format('Y-m-d')) }}">
            </div>
            <div class="col-md-2">
                <label for="status" class="form-label">Status</label>
                <select class="form-select" id="status" name="status">
                    <option value="">Semua Status</option>
                    <option value="menunggu" {{ request('status') == 'menunggu' ? 'selected' : '' }}>Menunggu</option>
                    <option value="dipanggil" {{ request('status') == 'dipanggil' ? 'selected' : '' }}>Dipanggil</option>
                    <option value="selesai" {{ request('status') == 'selesai' ? 'selected' : '' }}>Selesai</option>
                    <option value="batal" {{ request('status') == 'batal' ? 'selected' : '' }}>Batal</option>
                </select>
            </div>
            <div class="col-md-2">
                <label for="layanan" class="form-label">Layanan</label>
                <select class="form-select" id="layanan" name="layanan">
                    <option value="">Semua Layanan</option>
                    @foreach($layanans as $layanan)
                        <option value="{{ $layanan->id_layanan }}" {{ request('layanan') == $layanan->id_layanan ? 'selected' : '' }}>
                            {{ $layanan->nama_layanan }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label for="search" class="form-label">Pencarian</label>
                <input type="text" class="form-control" id="search" name="search" 
                       value="{{ request('search') }}" placeholder="No. antrian atau nama pengunjung">
            </div>
            <div class="col-md-3">
                <label class="form-label">&nbsp;</label>
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-outline-primary">
                        <i class="fas fa-search me-1"></i>Filter
                    </button>
                    <a href="{{ route('antrian.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-times me-1"></i>Reset
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Quick Stats -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card text-center bg-primary text-white">
            <div class="card-body py-3">
                <div class="h4" id="totalAntrian">{{ $antrians->total() }}</div>
                <div class="small">Total Antrian</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center bg-warning text-white">
            <div class="card-body py-3">
                <div class="h4" id="menungguCount">
                    {{ $antrians->where('status_antrian', 'menunggu')->count() }}
                </div>
                <div class="small">Menunggu</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center bg-info text-white">
            <div class="card-body py-3">
                <div class="h4" id="dipanggilCount">
                    {{ $antrians->where('status_antrian', 'dipanggil')->count() }}
                </div>
                <div class="small">Dipanggil</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center bg-success text-white">
            <div class="card-body py-3">
                <div class="h4" id="selesaiCount">
                    {{ $antrians->where('status_antrian', 'selesai')->count() }}
                </div>
                <div class="small">Selesai</div>
            </div>
        </div>
    </div>
</div>

<!-- Current Queue Alert -->
@php
    $currentQueue = $antrians->where('status_antrian', 'dipanggil')->first();
@endphp
@if($currentQueue)
<div class="alert alert-info mb-4" id="currentQueueAlert">
    <div class="d-flex align-items-center">
        <i class="fas fa-volume-up fa-2x me-3 text-primary"></i>
        <div class="flex-grow-1">
            <h5 class="mb-1">Antrian Sedang Dipanggil</h5>
            <p class="mb-0">
                <strong class="text-primary">{{ $currentQueue->nomor_antrian }}</strong> - 
                {{ $currentQueue->pengunjung->nama_pengunjung }} - 
                <span class="badge bg-primary">{{ $currentQueue->layanan->nama_layanan }}</span>
            </p>
        </div>
        <div class="text-end">
            <small class="text-muted">
                {{ $currentQueue->waktu_dipanggil ? $currentQueue->waktu_dipanggil->format('H:i:s') : 'Baru dipanggil' }}
            </small>
        </div>
    </div>
</div>
@endif

<!-- Antrian Table -->
<div class="card shadow">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">
            <i class="fas fa-list-ol me-2"></i>Data Antrian
            @if(request('tanggal'))
                - {{ \Carbon\Carbon::parse(request('tanggal'))->format('d F Y') }}
            @else
                - Hari Ini
            @endif
        </h5>
        <div class="d-flex align-items-center gap-3">
            <small class="text-muted">
                Total: {{ $antrians->total() }} antrian
            </small>
            <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" id="autoRefresh" checked>
                <label class="form-check-label" for="autoRefresh">
                    <small>Auto Refresh</small>
                </label>
            </div>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>No. Antrian</th>
                        <th>Pengunjung</th>
                        <th>Layanan</th>
                        <th>Status</th>
                        <th>Waktu Antri</th>
                        <th>Estimasi</th>
                        <th>Admin</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody id="antrianTableBody">
                    @forelse($antrians as $antrian)
                    <tr data-id="{{ $antrian->id_antrian }}" class="antrian-row">
                        <td>
                            <strong class="text-primary fs-5">{{ $antrian->nomor_antrian }}</strong>
                            @if($antrian->status_antrian == 'menunggu')
                                @php
                                    $position = \App\Models\Antrian::where('id_layanan', $antrian->id_layanan)
                                        ->whereDate('waktu_antrian', $antrian->waktu_antrian->toDateString())
                                        ->where('status_antrian', 'menunggu')
                                        ->where('waktu_antrian', '<', $antrian->waktu_antrian)
                                        ->count() + 1;
                                @endphp
                                <br><small class="text-muted">Posisi: {{ $position }}</small>
                            @endif
                        </td>
                        <td>
                            <div>
                                <strong>{{ $antrian->pengunjung->nama_pengunjung }}</strong>
                                <br><small class="text-muted">{{ $antrian->pengunjung->nik }}</small>
                            </div>
                        </td>
                        <td>
                            <span class="badge bg-info fs-6">{{ $antrian->layanan->kode_layanan }}</span>
                            <br><small class="text-muted">{{ $antrian->layanan->nama_layanan }}</small>
                        </td>
                        <td>
                            @if($antrian->status_antrian == 'menunggu')
                                <span class="badge bg-warning text-dark">
                                    <i class="fas fa-clock me-1"></i>Menunggu
                                </span>
                            @elseif($antrian->status_antrian == 'dipanggil')
                                <span class="badge bg-primary pulse">
                                    <i class="fas fa-volume-up me-1"></i>Dipanggil
                                </span>
                            @elseif($antrian->status_antrian == 'selesai')
                                <span class="badge bg-success">
                                    <i class="fas fa-check me-1"></i>Selesai
                                </span>
                            @else
                                <span class="badge bg-danger">
                                    <i class="fas fa-times me-1"></i>Batal
                                </span>
                            @endif
                        </td>
                        <td>
                            <small>
                                {{ $antrian->waktu_antrian->format('d/m/Y') }}<br>
                                <strong>{{ $antrian->waktu_antrian->format('H:i:s') }}</strong>
                            </small>
                        </td>
                        <td>
                            @if($antrian->waktu_estimasi)
                                <small class="text-info">
                                    <i class="fas fa-hourglass-half me-1"></i>
                                    {{ $antrian->waktu_estimasi->format('H:i') }}
                                </small>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            @if($antrian->admin)
                                <small>
                                    <i class="fas fa-user me-1"></i>
                                    {{ $antrian->admin->nama_admin }}
                                </small>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm" role="group">
                                <a href="{{ route('antrian.show', $antrian->id_antrian) }}" 
                                   class="btn btn-outline-info" title="Detail">
                                    <i class="fas fa-eye"></i>
                                </a>
                                
                                @if($antrian->status_antrian == 'menunggu')
                                    <button type="button" class="btn btn-outline-primary" 
                                            onclick="updateStatus({{ $antrian->id_antrian }}, 'dipanggil')" 
                                            title="Panggil">
                                        <i class="fas fa-volume-up"></i>
                                    </button>
                                @elseif($antrian->status_antrian == 'dipanggil')
                                    <button type="button" class="btn btn-outline-success" 
                                            onclick="updateStatus({{ $antrian->id_antrian }}, 'selesai')" 
                                            title="Selesai">
                                        <i class="fas fa-check"></i>
                                    </button>
                                @endif
                                
                                @if(in_array($antrian->status_antrian, ['menunggu', 'dipanggil']))
                                    <button type="button" class="btn btn-outline-danger" 
                                            onclick="updateStatus({{ $antrian->id_antrian }}, 'batal')" 
                                            title="Batalkan">
                                        <i class="fas fa-times"></i>
                                    </button>
                                @endif
                                
                                @if($antrian->status_antrian == 'menunggu')
                                    <a href="{{ route('antrian.edit', $antrian->id_antrian) }}" 
                                       class="btn btn-outline-warning" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-5">
                            <div class="text-muted">
                                <i class="fas fa-list-ol fa-3x mb-3 opacity-50"></i>
                                <h5>Tidak ada antrian ditemukan</h5>
                                @if(request()->hasAny(['tanggal', 'status', 'layanan', 'search']))
                                    <p>Tidak ada hasil untuk filter yang dipilih</p>
                                    <a href="{{ route('antrian.index') }}" class="btn btn-outline-primary">
                                        <i class="fas fa-times me-2"></i>Clear Filter
                                    </a>
                                @else
                                    <p>Belum ada antrian hari ini</p>
                                    <a href="{{ route('antrian.create') }}" class="btn btn-primary">
                                        <i class="fas fa-plus me-2"></i>Buat Antrian Pertama
                                    </a>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($antrians->hasPages())
            <div class="d-flex justify-content-center mt-4">
                {{ $antrians->appends(request()->query())->links() }}
            </div>
        @endif
    </div>
</div>

@endsection

@push('styles')
<style>
.pulse {
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0% { opacity: 1; }
    50% { opacity: 0.5; }
    100% { opacity: 1; }
}

.antrian-row {
    transition: all 0.3s ease;
}

.antrian-row:hover {
    background-color: rgba(0,123,255,0.1) !important;
}
</style>
@endpush

@push('scripts')
<script>
let autoRefreshInterval;

// Update status function
function updateStatus(antrianId, newStatus) {
    const confirmMessages = {
        'dipanggil': 'Panggil antrian ini?',
        'selesai': 'Tandai antrian ini sebagai selesai?',
        'batal': 'Batalkan antrian ini?'
    };
    
    if (!confirm(confirmMessages[newStatus])) return;
    
    const formData = new FormData();
    formData.append('_token', '{{ csrf_token() }}');
    formData.append('_method', 'PATCH');
    formData.append('status_antrian', newStatus);
    
    fetch(`{{ route('antrian.index') }}/${antrianId}/update-status`, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('success', data.message || `Status berhasil diubah ke ${newStatus}`);
            refreshTable();
        } else {
            showAlert('error', data.message || 'Gagal mengubah status');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('error', 'Terjadi kesalahan saat mengubah status');
    });
}

// Call next queue
function callNextQueue() {
    if (!confirm('Panggil antrian berikutnya?')) return;
    
    fetch('{{ route("antrian.call-next") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('success', data.message);
            refreshTable();
        } else {
            showAlert('warning', data.error || 'Tidak ada antrian yang menunggu');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('error', 'Gagal memanggil antrian');
    });
}

// Export data
function exportData() {
    const url = new URL('{{ route("antrian.export") }}', window.location.origin);
    const params = new URLSearchParams(window.location.search);
    
    // Add current filters to export
    ['tanggal', 'status', 'layanan', 'search'].forEach(param => {
        const value = params.get(param);
        if (value) url.searchParams.append(param, value);
    });
    
    window.open(url.toString(), '_blank');
}

// Refresh table
function refreshTable() {
    const currentUrl = new URL(window.location);
    fetch(currentUrl.toString(), {
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.text())
    .then(html => {
        const parser = new DOMParser();
        const doc = parser.parseFromString(html, 'text/html');
        
        // Update table body
        const newTableBody = doc.querySelector('#antrianTableBody');
        if (newTableBody) {
            document.getElementById('antrianTableBody').innerHTML = newTableBody.innerHTML;
        }
        
        // Update stats
        updateStats();
    })
    .catch(error => console.error('Refresh failed:', error));
}

// Update statistics
function updateStats() {
    const date = document.getElementById('tanggal').value || '{{ today()->format('Y-m-d') }}';
    
    fetch(`{{ route('antrian.stats') }}?date=${date}`)
        .then(response => response.json())
        .then(data => {
            document.getElementById('totalAntrian').textContent = data.stats.total;
            document.getElementById('menungguCount').textContent = data.stats.menunggu;
            document.getElementById('dipanggilCount').textContent = data.stats.dipanggil;
            document.getElementById('selesaiCount').textContent = data.stats.selesai;
        })
        .catch(error => console.log('Stats update failed:', error));
}

// Show alert
function showAlert(type, message) {
    const alertClass = type === 'success' ? 'alert-success' : 
                      type === 'warning' ? 'alert-warning' : 'alert-danger';
    
    const alertHtml = `
        <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
            <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'warning' ? 'exclamation-triangle' : 'exclamation-circle'} me-2"></i>
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
    
    const container = document.querySelector('.container');
    const firstCard = container.querySelector('.card');
    firstCard.insertAdjacentHTML('beforebegin', alertHtml);
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        const alert = container.querySelector('.alert');
        if (alert) {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        }
    }, 5000);
}

// Auto refresh functionality
document.getElementById('autoRefresh').addEventListener('change', function() {
    if (this.checked) {
        autoRefreshInterval = setInterval(refreshTable, 30000); // 30 seconds
    } else {
        clearInterval(autoRefreshInterval);
    }
});

// Initialize auto refresh
document.addEventListener('DOMContentLoaded', function() {
    if (document.getElementById('autoRefresh').checked) {
        autoRefreshInterval = setInterval(refreshTable, 30000);
    }
    
    // Auto submit filter form when date changes
    document.getElementById('tanggal').addEventListener('change', function() {
        document.getElementById('filterForm').submit();
    });
});

// Cleanup interval on page unload
window.addEventListener('beforeunload', function() {
    if (autoRefreshInterval) {
        clearInterval(autoRefreshInterval);
    }
});
</script>
@endpush    