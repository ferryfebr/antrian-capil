@extends('layouts.app')

@section('title', 'Data Pengunjung')
@section('page-title', 'Data Pengunjung')

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
            <li><a class="dropdown-item" href="#" onclick="exportData()">
                <i class="fas fa-download me-2"></i>Export CSV
            </a></li>
            <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#statsModal">
                <i class="fas fa-chart-bar me-2"></i>Statistik Pengunjung
            </a></li>
            <li><hr class="dropdown-divider"></li>
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
        <form method="GET" action="{{ route('pengunjung.index') }}" class="row g-3" id="filterForm">
            <div class="col-md-3">
                <label for="search" class="form-label">Pencarian</label>
                <input type="text" class="form-control" id="search" name="search" 
                       value="{{ request('search') }}" placeholder="NIK, nama, atau no HP...">
            </div>
            <div class="col-md-2">
                <label for="tanggal_daftar" class="form-label">Tanggal Daftar</label>
                <input type="date" class="form-control" id="tanggal_daftar" name="tanggal_daftar" 
                       value="{{ request('tanggal_daftar') }}">
            </div>
            <div class="col-md-2">
                <label for="has_contact" class="form-label">Kontak</label>
                <select class="form-select" id="has_contact" name="has_contact">
                    <option value="">Semua</option>
                    <option value="1" {{ request('has_contact') == '1' ? 'selected' : '' }}>Ada No HP</option>
                    <option value="0" {{ request('has_contact') == '0' ? 'selected' : '' }}>Tanpa No HP</option>
                </select>
            </div>
            <div class="col-md-2">
                <label for="has_queue" class="form-label">Status Antrian</label>
                <select class="form-select" id="has_queue" name="has_queue">
                    <option value="">Semua</option>
                    <option value="active" {{ request('has_queue') == 'active' ? 'selected' : '' }}>Punya Antrian Aktif</option>
                    <option value="completed" {{ request('has_queue') == 'completed' ? 'selected' : '' }}>Pernah Antri</option>
                    <option value="none" {{ request('has_queue') == 'none' ? 'selected' : '' }}>Belum Pernah Antri</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">&nbsp;</label>
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-outline-primary">
                        <i class="fas fa-search me-1"></i>Filter
                    </button>
                    <a href="{{ route('pengunjung.index') }}" class="btn btn-outline-secondary">
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
                <div class="h4">{{ $pengunjungs->total() }}</div>
                <div class="small">Total Pengunjung</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center bg-success text-white">
            <div class="card-body py-3">
                <div class="h4">
                    {{ \App\Models\Pengunjung::whereDate('waktu_daftar', today())->count() }}
                </div>
                <div class="small">Terdaftar Hari Ini</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center bg-info text-white">
            <div class="card-body py-3">
                <div class="h4">
                    {{ \App\Models\Pengunjung::whereNotNull('no_hp')->count() }}
                </div>
                <div class="small">Punya Kontak</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center bg-warning text-white">
            <div class="card-body py-3">
                <div class="h4">
                    {{ \App\Models\Pengunjung::has('antrian')->count() }}
                </div>
                <div class="small">Pernah Antri</div>
            </div>
        </div>
    </div>
</div>

<!-- Pengunjung Table -->
<div class="card shadow">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">
            <i class="fas fa-users me-2"></i>Data Pengunjung
        </h5>
        <div class="d-flex align-items-center gap-3">
            <small class="text-muted">
                Total: {{ $pengunjungs->total() }} pengunjung
            </small>
            <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" id="compactView">
                <label class="form-check-label" for="compactView">
                    <small>Compact</small>
                </label>
            </div>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover" id="pengunjungTable">
                <thead class="table-dark">
                    <tr>
                        <th>
                            <input type="checkbox" id="selectAll" class="form-check-input">
                        </th>
                        <th>NIK</th>
                        <th>Nama Pengunjung</th>
                        <th>Kontak</th>
                        <th>Terdaftar</th>
                        <th>Statistik</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pengunjungs as $pengunjung)
                    <tr>
                        <td>
                            <input type="checkbox" class="form-check-input pengunjung-checkbox" 
                                   value="{{ $pengunjung->id_pengunjung }}">
                        </td>
                        <td>
                            <div class="font-monospace">
                                <strong class="text-primary">{{ $pengunjung->nik }}</strong>
                            </div>
                            <small class="text-muted d-block">ID: {{ $pengunjung->id_pengunjung }}</small>
                        </td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="bg-primary rounded-circle p-2 text-white me-2 flex-shrink-0">
                                    <i class="fas fa-user"></i>
                                </div>
                                <div>
                                    <strong>{{ $pengunjung->nama_pengunjung }}</strong>
                                    @php
                                        $activeQueue = $pengunjung->antrian()
                                            ->whereIn('status_antrian', ['menunggu', 'dipanggil'])
                                            ->first();
                                    @endphp
                                    @if($activeQueue)
                                        <br><span class="badge bg-warning text-dark">
                                            <i class="fas fa-clock me-1"></i>{{ $activeQueue->nomor_antrian }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td>
                            @if($pengunjung->no_hp)
                                <div>
                                    <i class="fas fa-phone text-success me-1"></i>
                                    <a href="tel:{{ $pengunjung->no_hp }}" class="text-decoration-none">
                                        {{ $pengunjung->no_hp }}
                                    </a>
                                </div>
                            @else
                                <span class="text-muted">
                                    <i class="fas fa-phone-slash me-1"></i>Tidak ada
                                </span>
                            @endif
                        </td>
                        <td>
                            <div>
                                <strong>{{ $pengunjung->waktu_daftar->format('d/m/Y') }}</strong>
                                <br><small class="text-muted">{{ $pengunjung->waktu_daftar->format('H:i') }}</small>
                            </div>
                            <small class="text-muted">{{ $pengunjung->waktu_daftar->diffForHumans() }}</small>
                        </td>
                        <td>
                            @php
                                $totalAntrian = $pengunjung->antrian()->count();
                                $selesai = $pengunjung->antrian()->where('status_antrian', 'selesai')->count();
                                $aktif = $pengunjung->antrian()->whereIn('status_antrian', ['menunggu', 'dipanggil'])->count();
                            @endphp
                            <div class="small">
                                <div class="d-flex justify-content-between">
                                    <span class="text-muted">Total:</span>
                                    <strong class="text-primary">{{ $totalAntrian }}</strong>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span class="text-muted">Selesai:</span>
                                    <strong class="text-success">{{ $selesai }}</strong>
                                </div>
                                @if($aktif > 0)
                                <div class="d-flex justify-content-between">
                                    <span class="text-muted">Aktif:</span>
                                    <strong class="text-warning">{{ $aktif }}</strong>
                                </div>
                                @endif
                            </div>
                        </td>
                        <td>
                            @if($activeQueue)
                                <span class="badge bg-warning text-dark">
                                    <i class="fas fa-clock me-1"></i>Antri
                                </span>
                            @elseif($totalAntrian > 0)
                                <span class="badge bg-info">
                                    <i class="fas fa-history me-1"></i>Member
                                </span>
                            @else
                                <span class="badge bg-light text-dark">
                                    <i class="fas fa-user me-1"></i>Baru
                                </span>
                            @endif
                            
                            @if($pengunjung->no_hp)
                                <br><span class="badge bg-success mt-1">
                                    <i class="fas fa-phone me-1"></i>Kontak
                                </span>
                            @endif
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm" role="group">
                                <a href="{{ route('pengunjung.show', $pengunjung->id_pengunjung) }}" 
                                   class="btn btn-outline-info" title="Detail">
                                    <i class="fas fa-eye"></i>
                                </a>
                                
                                <a href="{{ route('antrian.create') }}?nik={{ $pengunjung->nik }}" 
                                   class="btn btn-outline-success" title="Buat Antrian">
                                    <i class="fas fa-plus"></i>
                                </a>
                                
                                @if($pengunjung->no_hp)
                                <button type="button" class="btn btn-outline-primary" 
                                        onclick="sendWhatsApp('{{ $pengunjung->no_hp }}')" 
                                        title="WhatsApp">
                                    <i class="fab fa-whatsapp"></i>
                                </button>
                                @endif
                                
                                @if(!$pengunjung->antrian()->whereIn('status_antrian', ['menunggu', 'dipanggil'])->exists())
                                <form action="{{ route('pengunjung.destroy', $pengunjung->id_pengunjung) }}" 
                                      method="POST" style="display: inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger" title="Hapus"
                                            onclick="return confirm('Yakin ingin menghapus data pengunjung {{ $pengunjung->nama_pengunjung }}?\n\nData antrian yang sudah selesai juga akan terhapus.')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-5">
                            <div class="text-muted">
                                <i class="fas fa-users fa-3x mb-3 opacity-50"></i>
                                <h5>Tidak ada pengunjung ditemukan</h5>
                                @if(request()->hasAny(['search', 'tanggal_daftar', 'has_contact', 'has_queue']))
                                    <p>Tidak ada hasil untuk filter yang dipilih</p>
                                    <a href="{{ route('pengunjung.index') }}" class="btn btn-outline-primary">
                                        <i class="fas fa-times me-2"></i>Clear Filter
                                    </a>
                                @else
                                    <p>Belum ada pengunjung yang terdaftar</p>
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
        
        @if($pengunjungs->hasPages())
            <div class="d-flex justify-content-center mt-4">
                {{ $pengunjungs->appends(request()->query())->links() }}
            </div>
        @endif
    </div>
</div>

<!-- Statistik Detail Modal -->
<div class="modal fade" id="statsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-chart-bar me-2"></i>Statistik Pengunjung
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6>Registrasi Pengunjung</h6>
                        <canvas id="registrationChart"></canvas>
                    </div>
                    <div class="col-md-6">
                        <h6>Status Kontak</h6>
                        <canvas id="contactChart"></canvas>
                    </div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-md-6">
                        <h6>Aktivitas Antrian</h6>
                        <canvas id="activityChart"></canvas>
                    </div>
                    <div class="col-md-6">
                        <h6>Top Pengunjung</h6>
                        <div class="list-group list-group-flush">
                            @php
                                $topVisitors = \App\Models\Pengunjung::withCount('antrian')
                                    ->orderBy('antrian_count', 'desc')
                                    ->limit(5)
                                    ->get();
                            @endphp
                            @foreach($topVisitors as $visitor)
                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <strong>{{ $visitor->nama_pengunjung }}</strong>
                                    <br><small class="text-muted">{{ substr($visitor->nik, 0, 6) }}****{{ substr($visitor->nik, -4) }}</small>
                                </div>
                                <span class="badge bg-primary rounded-pill">{{ $visitor->antrian_count }}</span>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
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
                    <i class="fas fa-trash me-2"></i>Bulk Delete Pengunjung
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="bulkDeleteForm" action="{{ route('pengunjung.bulk-delete') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Peringatan:</strong> Tindakan ini akan menghapus data pengunjung dan semua riwayat antriannya secara permanen.
                    </div>
                    <div id="selectedCount" class="alert alert-info d-none">
                        <span id="countText"></span>
                    </div>
                    <div id="selectedList"></div>
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
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Select All functionality
document.getElementById('selectAll').addEventListener('change', function() {
    const checkboxes = document.querySelectorAll('.pengunjung-checkbox');
    checkboxes.forEach(checkbox => {
        checkbox.checked = this.checked;
    });
    updateBulkDeleteButton();
});

// Individual checkbox change
document.querySelectorAll('.pengunjung-checkbox').forEach(checkbox => {
    checkbox.addEventListener('change', updateBulkDeleteButton);
});

function updateBulkDeleteButton() {
    const checkedBoxes = document.querySelectorAll('.pengunjung-checkbox:checked');
    const bulkDeleteSubmit = document.getElementById('bulkDeleteSubmit');
    const selectedCount = document.getElementById('selectedCount');
    const countText = document.getElementById('countText');
    const selectedList = document.getElementById('selectedList');
    
    if (checkedBoxes.length > 0) {
        bulkDeleteSubmit.disabled = false;
        selectedCount.classList.remove('d-none');
        countText.textContent = `${checkedBoxes.length} pengunjung terpilih`;
        
        // Update selected list
        let listHtml = '<p><strong>Pengunjung yang akan dihapus:</strong></p><ul class="list-unstyled">';
        checkedBoxes.forEach(checkbox => {
            const row = checkbox.closest('tr');
            const nama = row.querySelector('td:nth-child(3) strong').textContent;
            const nik = row.querySelector('td:nth-child(2) strong').textContent;
            
            listHtml += `<li><i class="fas fa-user me-2"></i><strong>${nama}</strong> (${nik.substr(0,6)}****${nik.substr(-4)})</li>`;
        });
        listHtml += '</ul>';
        selectedList.innerHTML = listHtml;
    } else {
        bulkDeleteSubmit.disabled = true;
        selectedCount.classList.add('d-none');
        selectedList.innerHTML = '<p class="text-muted">Tidak ada pengunjung yang dipilih</p>';
    }
}

// Bulk delete form submission
document.getElementById('bulkDeleteForm').addEventListener('submit', function(e) {
    const checkedBoxes = document.querySelectorAll('.pengunjung-checkbox:checked');
    
    if (checkedBoxes.length === 0) {
        e.preventDefault();
        alert('Pilih minimal satu pengunjung!');
        return;
    }
    
    // Add selected IDs to form
    checkedBoxes.forEach(checkbox => {
        const hiddenInput = document.createElement('input');
        hiddenInput.type = 'hidden';
        hiddenInput.name = 'pengunjung_ids[]';
        hiddenInput.value = checkbox.value;
        this.appendChild(hiddenInput);
    });
    
    // Final confirmation
    if (!confirm(`Yakin ingin menghapus ${checkedBoxes.length} pengunjung terpilih?\n\nSemua data antrian mereka juga akan terhapus.\n\nTindakan ini tidak dapat dibatalkan.`)) {
        e.preventDefault();
    }
});

// Compact view toggle
document.getElementById('compactView').addEventListener('change', function() {
    const table = document.getElementById('pengunjungTable');
    if (this.checked) {
        table.classList.add('table-sm');
    } else {
        table.classList.remove('table-sm');
    }
});

// Export function
function exportData() {
    const currentUrl = new URL(window.location);
    currentUrl.pathname = '{{ route("pengunjung.export") }}';
    window.open(currentUrl.toString(), '_blank');
}

// WhatsApp function
function sendWhatsApp(phoneNumber) {
    const message = encodeURIComponent('Halo, ini adalah notifikasi dari Disdukcapil mengenai layanan antrian.');
    const whatsappUrl = `https://wa.me/${phoneNumber.replace(/[^0-9]/g, '')}?text=${message}`;
    window.open(whatsappUrl, '_blank');
}

// Charts initialization
document.getElementById('statsModal').addEventListener('shown.bs.modal', function() {
    initializeCharts();
});

function initializeCharts() {
    // Registration Chart
    const regCtx = document.getElementById('registrationChart').getContext('2d');
    new Chart(regCtx, {
        type: 'line',
        data: {
            labels: @json(collect(range(6, 0))->map(fn($i) => now()->subDays($i)->format('d/m'))),
            datasets: [{
                label: 'Pengunjung Baru',
                data: @json(collect(range(6, 0))->map(fn($i) => \App\Models\Pengunjung::whereDate('waktu_daftar', now()->subDays($i))->count())),
                borderColor: 'rgb(75, 192, 192)',
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: { beginAtZero: true }
            }
        }
    });

    // Contact Chart
    const contactCtx = document.getElementById('contactChart').getContext('2d');
    const withContact = {{ \App\Models\Pengunjung::whereNotNull('no_hp')->count() }};
    const withoutContact = {{ \App\Models\Pengunjung::whereNull('no_hp')->count() }};
    
    new Chart(contactCtx, {
        type: 'doughnut',
        data: {
            labels: ['Punya Kontak', 'Tanpa Kontak'],
            datasets: [{
                data: [withContact, withoutContact],
                backgroundColor: ['#28a745', '#dc3545']
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false
        }
    });

    // Activity Chart
    const activityCtx = document.getElementById('activityChart').getContext('2d');
    const hasQueue = {{ \App\Models\Pengunjung::has('antrian')->count() }};
    const noQueue = {{ \App\Models\Pengunjung::doesntHave('antrian')->count() }};
    
    new Chart(activityCtx, {
        type: 'bar',
        data: {
            labels: ['Pernah Antri', 'Belum Antri'],
            datasets: [{
                data: [hasQueue, noQueue],
                backgroundColor: ['#007bff', '#6c757d']
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: { beginAtZero: true }
            }
        }
    });
}

// Auto refresh every 5 minutes
setInterval(function() {
    if (window.location.href.indexOf('page=') === -1) {
        location.reload();
    }
}, 300000);
</script>
@endpush