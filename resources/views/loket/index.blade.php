@extends('layouts.app')

@section('title', 'Kelola Loket')
@section('page-title', 'Kelola Loket')

@section('page-actions')
<div class="btn-group">
    <a href="{{ route('loket.create') }}" class="btn btn-primary">
        <i class="fas fa-plus me-2"></i>Tambah Loket
    </a>
    <div class="dropdown">
        <button class="btn btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
            <i class="fas fa-cog me-2"></i>Actions
        </button>
        <ul class="dropdown-menu">
            <li><a class="dropdown-item" href="#" onclick="exportData()">
                <i class="fas fa-download me-2"></i>Export CSV
            </a></li>
            <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#bulkActionModal">
                <i class="fas fa-tasks me-2"></i>Bulk Action
            </a></li>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item" href="{{ route('layanan.index') }}">
                <i class="fas fa-cogs me-2"></i>Kelola Layanan
            </a></li>
        </ul>
    </div>
</div>
@endsection

@section('content')
<!-- Filter Section -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('loket.index') }}" class="row g-3">
            <div class="col-md-3">
                <label for="status" class="form-label">Status Loket</label>
                <select class="form-select" id="status" name="status">
                    <option value="">Semua Status</option>
                    <option value="aktif" {{ request('status') == 'aktif' ? 'selected' : '' }}>Aktif</option>
                    <option value="tidak_aktif" {{ request('status') == 'tidak_aktif' ? 'selected' : '' }}>Tidak Aktif</option>
                </select>
            </div>
            <div class="col-md-3">
                <label for="layanan" class="form-label">Layanan</label>
                <select class="form-select" id="layanan" name="layanan">
                    <option value="">Semua Layanan</option>
                    <option value="null" {{ request('layanan') == 'null' ? 'selected' : '' }}>Tanpa Layanan (Umum)</option>
                    @foreach(\App\Models\Layanan::where('aktif', true)->get() as $layanan)
                        <option value="{{ $layanan->id_layanan }}" {{ request('layanan') == $layanan->id_layanan ? 'selected' : '' }}>
                            {{ $layanan->nama_layanan }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label for="search" class="form-label">Pencarian</label>
                <input type="text" class="form-control" id="search" name="search" 
                       value="{{ request('search') }}" placeholder="Cari nama loket...">
            </div>
            <div class="col-md-3">
                <label class="form-label">&nbsp;</label>
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-outline-primary">
                        <i class="fas fa-search me-1"></i>Filter
                    </button>
                    <a href="{{ route('loket.index') }}" class="btn btn-outline-secondary">
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
                <div class="h4">{{ $lokets->total() }}</div>
                <div class="small">Total Loket</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center bg-success text-white">
            <div class="card-body py-3">
                <div class="h4">{{ \App\Models\Loket::where('status_loket', 'aktif')->count() }}</div>
                <div class="small">Loket Aktif</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center bg-warning text-white">
            <div class="card-body py-3">
                <div class="h4">{{ \App\Models\Loket::where('status_loket', 'tidak_aktif')->count() }}</div>
                <div class="small">Loket Tidak Aktif</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center bg-info text-white">
            <div class="card-body py-3">
                <div class="h4">{{ \App\Models\Loket::whereNull('id_layanan')->count() }}</div>
                <div class="small">Loket Umum</div>
            </div>
        </div>
    </div>
</div>

<!-- Loket Table -->
<div class="card shadow">
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
                        <th>
                            <input type="checkbox" id="selectAll" class="form-check-input">
                        </th>
                        <th>ID</th>
                        <th>Nama Loket</th>
                        <th>Status</th>
                        <th>Layanan</th>
                        <th>Efisiensi</th>
                        <th>Deskripsi</th>
                        <th>Dibuat</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($lokets as $loket)
                    <tr>
                        <td>
                            <input type="checkbox" class="form-check-input loket-checkbox" 
                                   value="{{ $loket->id_loket }}">
                        </td>
                        <td>
                            <span class="badge bg-secondary fs-6">{{ $loket->id_loket }}</span>
                        </td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="bg-primary rounded-circle p-2 text-white me-2">
                                    <i class="fas fa-desktop"></i>
                                </div>
                                <div>
                                    <strong>{{ $loket->nama_loket }}</strong>
                                    @if($loket->layanan)
                                        <br><small class="text-muted">Spesialis {{ $loket->layanan->kode_layanan }}</small>
                                    @else
                                        <br><small class="text-muted">Layanan Umum</small>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td>
                            @if($loket->status_loket == 'aktif')
                                <span class="badge bg-success position-relative">
                                    <i class="fas fa-circle me-1"></i>Aktif
                                    <span class="position-absolute top-0 start-100 translate-middle p-1 bg-success border border-light rounded-circle">
                                        <span class="visually-hidden">Online</span>
                                    </span>
                                </span>
                            @else
                                <span class="badge bg-danger">
                                    <i class="fas fa-times me-1"></i>Tidak Aktif
                                </span>
                            @endif
                        </td>
                        <td>
                            @if($loket->layanan)
                                <div class="d-flex align-items-center">
                                    <span class="badge bg-info me-2">{{ $loket->layanan->kode_layanan }}</span>
                                    <div>
                                        <strong>{{ $loket->layanan->nama_layanan }}</strong>
                                        <br><small class="text-muted">{{ $loket->layanan->estimasi_durasi_layanan }} menit</small>
                                    </div>
                                </div>
                            @else
                                <div class="text-center">
                                    <span class="badge bg-secondary">UMUM</span>
                                    <br><small class="text-muted">Semua layanan</small>
                                </div>
                            @endif
                        </td>
                        <td>
                            @if($loket->layanan)
                                @php
                                    $todayCount = \App\Models\Antrian::whereHas('layanan', function($q) use ($loket) {
                                        $q->where('id_layanan', $loket->id_layanan);
                                    })->whereDate('created_at', today())->where('status_antrian', 'selesai')->count();
                                    
                                    $efficiency = $loket->layanan->kapasitas_harian > 0 ? 
                                        ($todayCount / $loket->layanan->kapasitas_harian) * 100 : 0;
                                @endphp
                                <div class="text-center">
                                    <div class="small text-muted">{{ $todayCount }}/{{ $loket->layanan->kapasitas_harian }}</div>
                                    <div class="progress mt-1" style="height: 6px;">
                                        <div class="progress-bar bg-{{ $efficiency > 80 ? 'danger' : ($efficiency > 60 ? 'warning' : 'success') }}" 
                                             style="width: {{ min(100, $efficiency) }}%"></div>
                                    </div>
                                    <small class="text-muted">{{ number_format($efficiency, 1) }}%</small>
                                </div>
                            @else
                                <div class="text-center text-muted">
                                    <i class="fas fa-infinity"></i>
                                    <br><small>Unlimited</small>
                                </div>
                            @endif
                        </td>
                        <td>
                            @if($loket->deskripsi_loket)
                                <div class="position-relative">
                                    <span data-bs-toggle="tooltip" data-bs-placement="top" 
                                          title="{{ $loket->deskripsi_loket }}">
                                        {{ Str::limit($loket->deskripsi_loket, 30) }}
                                    </span>
                                </div>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            <small class="text-muted">
                                {{ $loket->created_at->format('d/m/Y') }}
                                <br>{{ $loket->created_at->format('H:i') }}
                            </small>
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm" role="group">
                                <a href="{{ route('loket.edit', $loket->id_loket) }}" 
                                   class="btn btn-outline-primary" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                
                                <button type="button" class="btn btn-outline-{{ $loket->status_loket == 'aktif' ? 'warning' : 'success' }}" 
                                        onclick="toggleStatus({{ $loket->id_loket }}, '{{ $loket->status_loket }}')" 
                                        title="{{ $loket->status_loket == 'aktif' ? 'Nonaktifkan' : 'Aktifkan' }}">
                                    <i class="fas fa-{{ $loket->status_loket == 'aktif' ? 'pause' : 'play' }}"></i>
                                </button>
                                
                                @if($loket->layanan)
                                    <a href="{{ route('layanan.show', $loket->layanan->id_layanan) }}" 
                                       class="btn btn-outline-info" title="Lihat Layanan">
                                        <i class="fas fa-cogs"></i>
                                    </a>
                                @endif
                                
                                <form action="{{ route('loket.destroy', $loket->id_loket) }}" 
                                      method="POST" style="display: inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger" title="Hapus"
                                            onclick="return confirm('Yakin ingin menghapus loket {{ $loket->nama_loket }}?')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center py-5">
                            <div class="text-muted">
                                <i class="fas fa-desktop fa-3x mb-3 opacity-50"></i>
                                <h5>Tidak ada loket ditemukan</h5>
                                @if(request()->hasAny(['status', 'layanan', 'search']))
                                    <p>Tidak ada hasil untuk filter yang dipilih</p>
                                    <a href="{{ route('loket.index') }}" class="btn btn-outline-primary">
                                        <i class="fas fa-times me-2"></i>Clear Filter
                                    </a>
                                @else
                                    <p>Belum ada loket yang dibuat</p>
                                    <a href="{{ route('loket.create') }}" class="btn btn-primary">
                                        <i class="fas fa-plus me-2"></i>Tambah Loket Pertama
                                    </a>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($lokets->hasPages())
            <div class="d-flex justify-content-center mt-4">
                {{ $lokets->appends(request()->query())->links() }}
            </div>
        @endif
    </div>
</div>

<!-- Loket Layout Visualization -->
<div class="card mt-4">
    <div class="card-header">
        <h6 class="mb-0">
            <i class="fas fa-th-large me-2"></i>Layout Loket
        </h6>
    </div>
    <div class="card-body">
        <div class="row g-3">
            @foreach($lokets->take(12) as $loket)
            <div class="col-md-2 col-4">
                <div class="card border-{{ $loket->status_loket == 'aktif' ? 'success' : 'danger' }} h-100">
                    <div class="card-body text-center p-2">
                        <div class="mb-2">
                            <i class="fas fa-desktop fa-2x text-{{ $loket->status_loket == 'aktif' ? 'success' : 'danger' }}"></i>
                        </div>
                        <h6 class="card-title mb-1 small">{{ $loket->nama_loket }}</h6>
                        @if($loket->layanan)
                            <span class="badge bg-info small">{{ $loket->layanan->kode_layanan }}</span>
                        @else
                            <span class="badge bg-secondary small">UMUM</span>
                        @endif
                        <div class="mt-2">
                            <span class="badge bg-{{ $loket->status_loket == 'aktif' ? 'success' : 'danger' }} small">
                                {{ $loket->status_loket == 'aktif' ? 'AKTIF' : 'OFF' }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>

<!-- Bulk Action Modal -->
<div class="modal fade" id="bulkActionModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-tasks me-2"></i>Bulk Action Loket
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="bulkActionForm" action="{{ route('loket.bulk-action') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="bulkAction" class="form-label">Pilih Aksi:</label>
                        <select class="form-select" id="bulkAction" name="action" required>
                            <option value="">-- Pilih Aksi --</option>
                            <option value="activate">Aktifkan Semua</option>
                            <option value="deactivate">Nonaktifkan Semua</option>
                            <option value="clear_service">Hapus Assignment Layanan</option>
                            <option value="delete">Hapus Semua</option>
                        </select>
                    </div>
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Perhatian:</strong> Aksi ini akan diterapkan pada semua loket yang dipilih.
                    </div>
                    <div id="selectedLoketCount" class="alert alert-info d-none">
                        <span id="countText"></span>
                    </div>
                    <div id="loketList"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary" id="bulkSubmit" disabled>
                        <i class="fas fa-play me-2"></i>Eksekusi
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
// Select All functionality
document.getElementById('selectAll').addEventListener('change', function() {
    const checkboxes = document.querySelectorAll('.loket-checkbox');
    checkboxes.forEach(checkbox => {
        checkbox.checked = this.checked;
    });
    updateBulkActionButton();
});

// Individual checkbox change
document.querySelectorAll('.loket-checkbox').forEach(checkbox => {
    checkbox.addEventListener('change', updateBulkActionButton);
});

function updateBulkActionButton() {
    const checkedBoxes = document.querySelectorAll('.loket-checkbox:checked');
    const bulkSubmit = document.getElementById('bulkSubmit');
    const selectedCount = document.getElementById('selectedLoketCount');
    const countText = document.getElementById('countText');
    const loketList = document.getElementById('loketList');
    
    if (checkedBoxes.length > 0) {
        bulkSubmit.disabled = false;
        selectedCount.classList.remove('d-none');
        countText.textContent = `${checkedBoxes.length} loket terpilih`;
        
        // Update loket list
        let listHtml = '<p><strong>Loket yang akan diproses:</strong></p><ul class="list-unstyled">';
        checkedBoxes.forEach(checkbox => {
            const row = checkbox.closest('tr');
            const nama = row.querySelector('td:nth-child(3) strong').textContent;
            const status = row.querySelector('td:nth-child(4) .badge').textContent;
            
            listHtml += `<li><i class="fas fa-desktop me-2"></i><strong>${nama}</strong> - ${status}</li>`;
        });
        listHtml += '</ul>';
        loketList.innerHTML = listHtml;
    } else {
        bulkSubmit.disabled = true;
        selectedCount.classList.add('d-none');
        loketList.innerHTML = '<p class="text-muted">Tidak ada loket yang dipilih</p>';
    }
}

// Bulk action form submission
document.getElementById('bulkActionForm').addEventListener('submit', function(e) {
    const checkedBoxes = document.querySelectorAll('.loket-checkbox:checked');
    const action = document.getElementById('bulkAction').value;
    
    if (checkedBoxes.length === 0) {
        e.preventDefault();
        alert('Pilih minimal satu loket!');
        return;
    }
    
    if (!action) {
        e.preventDefault();
        alert('Pilih aksi yang ingin dilakukan!');
        return;
    }
    
    // Add selected IDs to form
    checkedBoxes.forEach(checkbox => {
        const hiddenInput = document.createElement('input');
        hiddenInput.type = 'hidden';
        hiddenInput.name = 'loket_ids[]';
        hiddenInput.value = checkbox.value;
        this.appendChild(hiddenInput);
    });
    
    // Confirmation based on action
    const actionText = {
        'activate': 'mengaktifkan',
        'deactivate': 'menonaktifkan',
        'clear_service': 'menghapus assignment layanan dari',
        'delete': 'menghapus'
    };
    
    const confirmMessage = `Yakin ingin ${actionText[action]} ${checkedBoxes.length} loket terpilih?`;
    if (!confirm(confirmMessage)) {
        e.preventDefault();
    }
});

// Toggle status function
function toggleStatus(loketId, currentStatus) {
    const newStatus = currentStatus === 'aktif' ? 'tidak_aktif' : 'aktif';
    const action = newStatus === 'aktif' ? 'mengaktifkan' : 'menonaktifkan';
    
    if (!confirm(`Yakin ingin ${action} loket ini?`)) return;
    
    const formData = new FormData();
    formData.append('_token', '{{ csrf_token() }}');
    formData.append('_method', 'PATCH');
    formData.append('status', newStatus);
    
    fetch(`{{ route('loket.index') }}/${loketId}/toggle-status`, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('success', data.message || `Loket berhasil ${action}`);
            setTimeout(() => location.reload(), 1000);
        } else {
            showAlert('error', data.message || 'Gagal mengubah status');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('error', 'Terjadi kesalahan saat mengubah status');
    });
}

// Export function
function exportData() {
    const currentUrl = new URL(window.location);
    currentUrl.pathname = '{{ route("loket.export") }}';
    window.open(currentUrl.toString(), '_blank');
}

// Show alert function
function showAlert(type, message) {
    const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
    
    const alertHtml = `
        <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
            <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'} me-2"></i>
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

// Initialize tooltips
document.addEventListener('DOMContentLoaded', function() {
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});

// Auto refresh every 2 minutes
setInterval(function() {
    if (window.location.href.indexOf('page=') === -1) {
        location.reload();
    }
}, 120000);
</script>
@endpush