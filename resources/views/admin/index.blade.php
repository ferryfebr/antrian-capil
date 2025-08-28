@extends('layouts.app')

@section('title', 'Kelola Admin')
@section('page-title', 'Kelola Admin')

@section('page-actions')
<div class="btn-group">
    <a href="{{ route('admin.create') }}" class="btn btn-primary">
        <i class="fas fa-plus me-2"></i>Tambah Admin Baru
    </a>
    <div class="dropdown">
        <button class="btn btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
            <i class="fas fa-cog me-2"></i>Actions
        </button>
        <ul class="dropdown-menu">
            <li><a class="dropdown-item" href="#" onclick="exportData()">
                <i class="fas fa-download me-2"></i>Export CSV
            </a></li>
            <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#bulkDeleteModal">
                <i class="fas fa-trash me-2"></i>Bulk Delete
            </a></li>
        </ul>
    </div>
</div>
@endsection

@section('content')
<!-- Filter Section -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('admin.index') }}" class="row g-3">
            <div class="col-md-6">
                <label for="search" class="form-label">Pencarian</label>
                <input type="text" class="form-control" id="search" name="search" 
                       value="{{ request('search') }}" placeholder="Cari username, nama, atau email...">
            </div>
            <div class="col-md-3">
                <label class="form-label">&nbsp;</label>
                <div class="d-grid">
                    <button type="submit" class="btn btn-outline-primary">
                        <i class="fas fa-search me-2"></i>Cari
                    </button>
                </div>
            </div>
            <div class="col-md-3">
                <label class="form-label">&nbsp;</label>
                <div class="d-grid">
                    <a href="{{ route('admin.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-times me-2"></i>Reset
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Admin Table -->
<div class="card shadow">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">
            <i class="fas fa-user-shield me-2"></i>Data Admin
        </h5>
        <small class="text-muted">
            Total: {{ $admins->total() }} admin
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
                        <th>Username</th>
                        <th>Nama Admin</th>
                        <th>Email</th>
                        <th>Layanan</th>
                        <th>Antrian</th>
                        <th>Dibuat</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($admins as $admin)
                    <tr>
                        <td>
                            @if($admin->id_admin != Auth::guard('admin')->id())
                                <input type="checkbox" class="form-check-input admin-checkbox" 
                                       value="{{ $admin->id_admin }}">
                            @endif
                        </td>
                        <td>
                            <span class="badge bg-secondary">{{ $admin->id_admin }}</span>
                        </td>
                        <td>
                            <div class="d-flex align-items-center">
                                @if($admin->id_admin == Auth::guard('admin')->id())
                                    <i class="fas fa-user-circle text-primary me-2"></i>
                                @endif
                                <div>
                                    <strong class="text-primary">{{ $admin->username }}</strong>
                                    @if($admin->id_admin == Auth::guard('admin')->id())
                                        <br><small class="badge bg-info">Anda</small>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td>
                            <strong>{{ $admin->nama_admin }}</strong>
                        </td>
                        <td>
                            @if($admin->email)
                                <a href="mailto:{{ $admin->email }}" class="text-decoration-none">
                                    {{ $admin->email }}
                                </a>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            <div class="text-center">
                                <span class="badge bg-primary fs-6">{{ $admin->layanan_count }}</span>
                                <br><small class="text-muted">layanan</small>
                            </div>
                        </td>
                        <td>
                            <div class="text-center">
                                <span class="badge bg-success fs-6">{{ $admin->antrian_count }}</span>
                                <br><small class="text-muted">antrian</small>
                            </div>
                        </td>
                        <td>
                            <small class="text-muted">
                                {{ $admin->created_at->format('d/m/Y') }}
                                <br>{{ $admin->created_at->format('H:i') }}
                            </small>
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm" role="group">
                                <a href="{{ route('admin.show', $admin->id_admin) }}" 
                                   class="btn btn-outline-info" title="Detail">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('admin.edit', $admin->id_admin) }}" 
                                   class="btn btn-outline-primary" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                @if($admin->id_admin != Auth::guard('admin')->id())
                                <form action="{{ route('admin.destroy', $admin->id_admin) }}" method="POST" style="display: inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger" title="Hapus"
                                            onclick="return confirm('Yakin ingin menghapus admin {{ $admin->username }}?')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center py-5">
                            <div class="text-muted">
                                <i class="fas fa-user-shield fa-3x mb-3 opacity-50"></i>
                                <h5>Tidak ada admin ditemukan</h5>
                                @if(request('search'))
                                    <p>Tidak ada hasil untuk pencarian "{{ request('search') }}"</p>
                                    <a href="{{ route('admin.index') }}" class="btn btn-outline-primary">
                                        <i class="fas fa-times me-2"></i>Clear Search
                                    </a>
                                @else
                                    <p>Belum ada data admin lain</p>
                                    <a href="{{ route('admin.create') }}" class="btn btn-primary">
                                        <i class="fas fa-plus me-2"></i>Tambah Admin Pertama
                                    </a>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($admins->hasPages())
            <div class="d-flex justify-content-center mt-4">
                {{ $admins->appends(request()->query())->links() }}
            </div>
        @endif
    </div>
</div>

<!-- Statistics Cards -->
<div class="row mt-4">
    <div class="col-md-3">
        <div class="card text-center bg-primary text-white">
            <div class="card-body">
                <div class="h3">{{ $admins->total() }}</div>
                <div>Total Admin</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center bg-success text-white">
            <div class="card-body">
                <div class="h3">{{ \App\Models\Admin::has('layanan')->count() }}</div>
                <div>Admin dengan Layanan</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center bg-info text-white">
            <div class="card-body">
                <div class="h3">{{ \App\Models\Admin::has('antrian')->count() }}</div>
                <div>Admin dengan Aktivitas</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center bg-warning text-white">
            <div class="card-body">
                <div class="h3">{{ \App\Models\Admin::whereDate('created_at', today())->count() }}</div>
                <div>Admin Baru Hari Ini</div>
            </div>
        </div>
    </div>
</div>

<!-- Bulk Delete Modal -->
<div class="modal fade" id="bulkDeleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-trash me-2"></i>Bulk Delete Admin
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="bulkDeleteForm" action="{{ route('admin.bulk-delete') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Perhatian:</strong> Aksi ini akan menghapus semua admin yang dipilih secara permanen.
                    </div>
                    <div id="selectedAdminCount" class="alert alert-info d-none">
                        <span id="countText"></span>
                    </div>
                    <p>Admin yang akan dihapus:</p>
                    <ul id="adminList" class="list-unstyled"></ul>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger" id="bulkDeleteSubmit" disabled>
                        <i class="fas fa-trash me-2"></i>Hapus Terpilih
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
    const checkboxes = document.querySelectorAll('.admin-checkbox');
    checkboxes.forEach(checkbox => {
        checkbox.checked = this.checked;
    });
    updateBulkDeleteButton();
});

// Individual checkbox change
document.querySelectorAll('.admin-checkbox').forEach(checkbox => {
    checkbox.addEventListener('change', updateBulkDeleteButton);
});

function updateBulkDeleteButton() {
    const checkedBoxes = document.querySelectorAll('.admin-checkbox:checked');
    const bulkDeleteSubmit = document.getElementById('bulkDeleteSubmit');
    const selectedCount = document.getElementById('selectedAdminCount');
    const countText = document.getElementById('countText');
    const adminList = document.getElementById('adminList');
    
    if (checkedBoxes.length > 0) {
        bulkDeleteSubmit.disabled = false;
        selectedCount.classList.remove('d-none');
        countText.textContent = `${checkedBoxes.length} admin terpilih`;
        
        // Update admin list in modal
        adminList.innerHTML = '';
        checkedBoxes.forEach(checkbox => {
            const row = checkbox.closest('tr');
            const username = row.querySelector('td:nth-child(3) strong').textContent;
            const nama = row.querySelector('td:nth-child(4) strong').textContent;
            
            const li = document.createElement('li');
            li.innerHTML = `<i class="fas fa-user me-2"></i><strong>${username}</strong> - ${nama}`;
            adminList.appendChild(li);
        });
    } else {
        bulkDeleteSubmit.disabled = true;
        selectedCount.classList.add('d-none');
        adminList.innerHTML = '<li class="text-muted">Tidak ada admin yang dipilih</li>';
    }
}

// Bulk delete form submission
document.getElementById('bulkDeleteForm').addEventListener('submit', function(e) {
    const checkedBoxes = document.querySelectorAll('.admin-checkbox:checked');
    
    if (checkedBoxes.length === 0) {
        e.preventDefault();
        alert('Pilih minimal satu admin!');
        return;
    }
    
    // Add selected IDs to form
    checkedBoxes.forEach(checkbox => {
        const hiddenInput = document.createElement('input');
        hiddenInput.type = 'hidden';
        hiddenInput.name = 'admin_ids[]';
        hiddenInput.value = checkbox.value;
        this.appendChild(hiddenInput);
    });
    
    // Final confirmation
    if (!confirm(`Yakin ingin menghapus ${checkedBoxes.length} admin terpilih? Tindakan ini tidak dapat dibatalkan.`)) {
        e.preventDefault();
    }
});

// Export function
function exportData() {
    const currentUrl = new URL(window.location);
    currentUrl.pathname = '{{ route("admin.export") }}';
    window.open(currentUrl.toString(), '_blank');
}

// Auto refresh every 5 minutes
setInterval(function() {
    if (window.location.href.indexOf('page=') === -1) {
        location.reload();
    }
}, 300000);

// Initialize tooltips
document.addEventListener('DOMContentLoaded', function() {
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});

// Update bulk delete modal when opened
document.getElementById('bulkDeleteModal').addEventListener('show.bs.modal', function() {
    updateBulkDeleteButton();
});
</script>
@endpush