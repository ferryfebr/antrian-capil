@extends('layouts.app')

@section('title', 'Kelola Layanan')
@section('page-title', 'Kelola Layanan')

@section('page-actions')
<div class="btn-group">
    <a href="{{ route('layanan.create') }}" class="btn btn-primary">
        <i class="fas fa-plus me-2"></i>Tambah Layanan
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
        </ul>
    </div>
</div>
@endsection

@section('content')
<!-- Filter Section -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('layanan.index') }}" class="row g-3">
            <div class="col-md-4">
                <label for="search" class="form-label">Pencarian</label>
                <input type="text" class="form-control" id="search" name="search" 
                       value="{{ request('search') }}" placeholder="Cari nama atau kode layanan...">
            </div>
            <div class="col-md-3">
                <label for="status" class="form-label">Status</label>
                <select class="form-select" id="status" name="status">
                    <option value="">Semua Status</option>
                    <option value="aktif" {{ request('status') == 'aktif' ? 'selected' : '' }}>Aktif</option>
                    <option value="tidak_aktif" {{ request('status') == 'tidak_aktif' ? 'selected' : '' }}>Tidak Aktif</option>
                </select>
            </div>
            <div class="col-md-3">
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
                    <a href="{{ route('layanan.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-times me-2"></i>Reset
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Layanan Table -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">
            <i class="fas fa-cogs me-2"></i>Data Layanan
        </h5>
        <small class="text-muted">
            Total: {{ $layanans->total() }} layanan
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
                        <th>Kode</th>
                        <th>Nama Layanan</th>
                        <th>Durasi</th>
                        <th>Kapasitas</th>
                        <th>Status</th>
                        <th>Admin</th>
                        <th>Dibuat</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($layanans as $layanan)
                    <tr>
                        <td>
                            <input type="checkbox" class="form-check-input layanan-checkbox" 
                                   value="{{ $layanan->id_layanan }}">
                        </td>
                        <td>
                            <span class="badge bg-primary fs-6">{{ $layanan->kode_layanan }}</span>
                        </td>
                        <td>
                            <div>
                                <strong>{{ $layanan->nama_layanan }}</strong>
                            </div>
                        </td>
                        <td>
                            <span class="text-info">
                                <i class="fas fa-clock me-1"></i>
                                {{ $layanan->estimasi_durasi_layanan }} menit
                            </span>
                        </td>
                        <td>
                            <span class="text-success">
                                <i class="fas fa-users me-1"></i>
                                {{ $layanan->kapasitas_harian }} orang
                            </span>
                        </td>
                        <td>
                            @if($layanan->aktif)
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
                            @if($layanan->admin)
                                <small>{{ $layanan->admin->nama_admin }}</small>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            <small class="text-muted">{{ $layanan->created_at->format('d/m/Y') }}</small>
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm" role="group">
                                <a href="{{ route('layanan.show', $layanan->id_layanan) }}" 
                                   class="btn btn-outline-info" title="Detail">
                                    <i class="fas fa-eye"></i>
                                </a>
                                
                                <a href="{{ route('layanan.edit', $layanan->id_layanan) }}" 
                                   class="btn btn-outline-primary" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                
                                <form action="{{ route('layanan.toggle-status', $layanan->id_layanan) }}" 
                                      method="POST" style="display: inline;">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" 
                                            class="btn btn-outline-{{ $layanan->aktif ? 'warning' : 'success' }}" 
                                            title="{{ $layanan->aktif ? 'Nonaktifkan' : 'Aktifkan' }}"
                                            onclick="return confirm('Yakin ingin {{ $layanan->aktif ? 'menonaktifkan' : 'mengaktifkan' }} layanan ini?')">
                                        <i class="fas fa-{{ $layanan->aktif ? 'toggle-off' : 'toggle-on' }}"></i>
                                    </button>
                                </form>
                                
                                <form action="{{ route('layanan.destroy', $layanan->id_layanan) }}" 
                                      method="POST" style="display: inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger" title="Hapus"
                                            onclick="return confirm('Yakin ingin menghapus layanan ini? Data yang terhubung akan ikut terhapus!')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center py-4">
                            <div class="text-muted">
                                <i class="fas fa-cogs fa-3x mb-3"></i>
                                <p>Tidak ada data layanan yang ditemukan</p>
                                <a href="{{ route('layanan.create') }}" class="btn btn-primary">
                                    <i class="fas fa-plus me-2"></i>Tambah Layanan Pertama
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        @if($layanans->hasPages())
            <div class="d-flex justify-content-center mt-4">
                {{ $layanans->appends(request()->query())->links() }}
            </div>
        @endif
    </div>
</div>

<!-- Bulk Action Modal -->
<div class="modal fade" id="bulkActionModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Bulk Action</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="bulkActionForm" action="{{ route('layanan.bulk-action') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="bulkAction" class="form-label">Pilih Aksi:</label>
                        <select class="form-select" id="bulkAction" name="action" required>
                            <option value="">-- Pilih Aksi --</option>
                            <option value="activate">Aktifkan Semua</option>
                            <option value="deactivate">Nonaktifkan Semua</option>
                            <option value="delete">Hapus Semua</option>
                        </select>
                    </div>
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Perhatian:</strong> Aksi ini akan diterapkan pada semua layanan yang dipilih.
                    </div>
                    <div id="selectedCount" class="alert alert-info d-none">
                        <span id="countText"></span>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary" id="bulkSubmit" disabled>Eksekusi</button>
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
    const checkboxes = document.querySelectorAll('.layanan-checkbox');
    checkboxes.forEach(checkbox => {
        checkbox.checked = this.checked;
    });
    updateBulkActionButton();
});

// Individual checkbox change
document.querySelectorAll('.layanan-checkbox').forEach(checkbox => {
    checkbox.addEventListener('change', updateBulkActionButton);
});

function updateBulkActionButton() {
    const checkedBoxes = document.querySelectorAll('.layanan-checkbox:checked');
    const bulkSubmit = document.getElementById('bulkSubmit');
    const selectedCount = document.getElementById('selectedCount');
    const countText = document.getElementById('countText');
    
    if (checkedBoxes.length > 0) {
        bulkSubmit.disabled = false;
        selectedCount.classList.remove('d-none');
        countText.textContent = `${checkedBoxes.length} layanan terpilih`;
    } else {
        bulkSubmit.disabled = true;
        selectedCount.classList.add('d-none');
    }
}

// Bulk action form submission
document.getElementById('bulkActionForm').addEventListener('submit', function(e) {
    const checkedBoxes = document.querySelectorAll('.layanan-checkbox:checked');
    const action = document.getElementById('bulkAction').value;
    
    if (checkedBoxes.length === 0) {
        e.preventDefault();
        alert('Pilih minimal satu layanan!');
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
        hiddenInput.name = 'layanan_ids[]';
        hiddenInput.value = checkbox.value;
        this.appendChild(hiddenInput);
    });
    
    // Confirmation
    const actionText = {
        'activate': 'mengaktifkan',
        'deactivate': 'menonaktifkan', 
        'delete': 'menghapus'
    };
    
    const confirmMessage = `Yakin ingin ${actionText[action]} ${checkedBoxes.length} layanan terpilih?`;
    if (!confirm(confirmMessage)) {
        e.preventDefault();
    }
});

// Export function
function exportData() {
    const currentUrl = new URL(window.location);
    currentUrl.pathname = '{{ route("layanan.export") }}';
    window.open(currentUrl.toString(), '_blank');
}

// Auto refresh setiap 5 menit
setInterval(function() {
    if (window.location.href.indexOf('page=') === -1) {
        location.reload();
    }
}, 300000);
</script>
@endpush