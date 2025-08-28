@extends('layouts.app')

@section('title', 'Detail Admin')
@section('page-title', 'Detail Admin - ' . $admin->nama_admin)

@section('page-actions')
<div class="btn-group">
    <a href="{{ route('admin.edit', $admin->id_admin) }}" class="btn btn-primary">
        <i class="fas fa-edit me-2"></i>Edit
    </a>
    @if($admin->id_admin != Auth::guard('admin')->id())
    <form action="{{ route('admin.destroy', $admin->id_admin) }}" method="POST" style="display: inline;">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn btn-danger" 
                onclick="return confirm('Yakin ingin menghapus admin ini?')">
            <i class="fas fa-trash me-2"></i>Hapus
        </button>
    </form>
    @endif
    <a href="{{ route('admin.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left me-2"></i>Kembali
    </a>
</div>
@endsection

@section('content')
<div class="row">
    <!-- Detail Admin -->
    <div class="col-md-8">
        <div class="card shadow mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">
                    <i class="fas fa-user-shield me-2"></i>Informasi Admin
                </h5>
            </div>
            <div class="card-body">
                <!-- Alert jika akun sendiri -->
                @if($admin->id_admin == Auth::guard('admin')->id())
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>Info:</strong> Ini adalah akun Anda sendiri.
                </div>
                @endif

                <div class="row">
                    <div class="col-md-6 mb-4">
                        <label class="form-label text-muted">Username</label>
                        <div class="h5 text-primary">
                            <i class="fas fa-user me-2"></i>{{ $admin->username }}
                        </div>
                    </div>
                    <div class="col-md-6 mb-4">
                        <label class="form-label text-muted">Nama Lengkap</label>
                        <div class="h5">{{ $admin->nama_admin }}</div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-4">
                        <label class="form-label text-muted">Email</label>
                        <div>
                            @if($admin->email)
                                <i class="fas fa-envelope me-2 text-info"></i>
                                <a href="mailto:{{ $admin->email }}" class="text-decoration-none">
                                    {{ $admin->email }}
                                </a>
                            @else
                                <span class="text-muted">Tidak ada email</span>
                            @endif
                        </div>
                    </div>
                    <div class="col-md-6 mb-4">
                        <label class="form-label text-muted">ID Admin</label>
                        <div>
                            <span class="badge bg-secondary fs-6">{{ $admin->id_admin }}</span>
                        </div>
                    </div>
                </div>

                <!-- Timestamp -->
                <div class="row mt-3">
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted">Bergabung</label>
                        <div class="text-success">
                            <i class="fas fa-calendar-plus me-2"></i>
                            <strong>{{ $admin->created_at->format('d/m/Y H:i') }}</strong>
                            <br><small class="text-muted">{{ $admin->created_at->diffForHumans() }}</small>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted">Terakhir Update</label>
                        <div class="text-info">
                            <i class="fas fa-calendar-edit me-2"></i>
                            <strong>{{ $admin->updated_at->format('d/m/Y H:i') }}</strong>
                            <br><small class="text-muted">{{ $admin->updated_at->diffForHumans() }}</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Layanan yang Dikelola -->
        <div class="card shadow mb-4">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="fas fa-cogs me-2"></i>Layanan yang Dikelola
                </h6>
            </div>
            <div class="card-body">
                @if($admin->layanan->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Kode</th>
                                    <th>Nama Layanan</th>
                                    <th>Status</th>
                                    <th>Kapasitas</th>
                                    <th>Dibuat</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($admin->layanan->sortBy('created_at') as $layanan)
                                <tr>
                                    <td>
                                        <span class="badge bg-primary">{{ $layanan->kode_layanan }}</span>
                                    </td>
                                    <td>
                                        <a href="{{ route('layanan.show', $layanan->id_layanan) }}" class="text-decoration-none">
                                            {{ $layanan->nama_layanan }}
                                        </a>
                                    </td>
                                    <td>
                                        @if($layanan->aktif)
                                            <span class="badge bg-success">Aktif</span>
                                        @else
                                            <span class="badge bg-danger">Tidak Aktif</span>
                                        @endif
                                    </td>
                                    <td>{{ $layanan->kapasitas_harian }} orang</td>
                                    <td>{{ $layanan->created_at->format('d/m/Y') }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center text-muted py-4">
                        <i class="fas fa-cogs fa-3x mb-3 opacity-50"></i>
                        <p>Belum ada layanan yang dikelola</p>
                        <a href="{{ route('layanan.create') }}" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-plus me-1"></i>Tambah Layanan
                        </a>
                    </div>
                @endif
            </div>
        </div>

        <!-- Aktivitas Antrian Terbaru -->
        <div class="card shadow">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="fas fa-history me-2"></i>Aktivitas Antrian Terbaru
                </h6>
            </div>
            <div class="card-body">
                @if($recent_activities->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>No. Antrian</th>
                                    <th>Pengunjung</th>
                                    <th>Layanan</th>
                                    <th>Status</th>
                                    <th>Waktu</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recent_activities as $antrian)
                                <tr>
                                    <td>
                                        <a href="{{ route('antrian.show', $antrian->id_antrian) }}" class="text-decoration-none">
                                            <strong>{{ $antrian->nomor_antrian }}</strong>
                                        </a>
                                    </td>
                                    <td>{{ $antrian->pengunjung->nama_pengunjung }}</td>
                                    <td>
                                        <span class="badge bg-info">{{ $antrian->layanan->kode_layanan }}</span>
                                    </td>
                                    <td>
                                        @if($antrian->status_antrian == 'menunggu')
                                            <span class="badge bg-warning text-dark">Menunggu</span>
                                        @elseif($antrian->status_antrian == 'dipanggil')
                                            <span class="badge bg-primary">Dipanggil</span>
                                        @elseif($antrian->status_antrian == 'selesai')
                                            <span class="badge bg-success">Selesai</span>
                                        @else
                                            <span class="badge bg-danger">Batal</span>
                                        @endif
                                    </td>
                                    <td>{{ $antrian->updated_at->format('d/m H:i') }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-2">
                        <a href="{{ route('antrian.index', ['admin' => $admin->id_admin]) }}" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-eye me-1"></i>Lihat Semua Aktivitas
                        </a>
                    </div>
                @else
                    <div class="text-center text-muted py-4">
                        <i class="fas fa-history fa-3x mb-3 opacity-50"></i>
                        <p>Belum ada aktivitas antrian</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Sidebar Statistik -->
    <div class="col-md-4">
        <!-- Statistik Utama -->
        <div class="card shadow mb-4">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="fas fa-chart-pie me-2"></i>Statistik Admin
                </h6>
            </div>
            <div class="card-body">
                <div class="row text-center mb-3">
                    <div class="col-6">
                        <div class="h4 text-primary mb-1">{{ $admin->layanan_count }}</div>
                        <small class="text-muted">Total Layanan</small>
                    </div>
                    <div class="col-6">
                        <div class="h4 text-success mb-1">{{ $admin->layanan()->where('aktif', true)->count() }}</div>
                        <small class="text-muted">Layanan Aktif</small>
                    </div>
                </div>

                <div class="row text-center mb-3">
                    <div class="col-6">
                        <div class="h4 text-warning mb-1">{{ $admin->antrian_count }}</div>
                        <small class="text-muted">Total Antrian</small>
                    </div>
                    <div class="col-6">
                        <div class="h4 text-info mb-1">{{ $admin->antrian()->whereDate('created_at', today())->count() }}</div>
                        <small class="text-muted">Hari Ini</small>
                    </div>
                </div>

                <!-- Progress Bar Aktivitas -->
                @php
                    $totalAntrian = $admin->antrian_count;
                    $antrianHariIni = $admin->antrian()->whereDate('created_at', today())->count();
                    $percentageToday = $totalAntrian > 0 ? ($antrianHariIni / $totalAntrian) * 100 : 0;
                @endphp

                <div class="mt-3">
                    <div class="d-flex justify-content-between mb-1">
                        <span class="small">Aktivitas Hari Ini</span>
                        <span class="small">{{ number_format($percentageToday, 1) }}%</span>
                    </div>
                    <div class="progress" style="height: 8px;">
                        <div class="progress-bar bg-info" style="width: {{ min(100, $percentageToday) }}%"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Performa Bulanan -->
        <div class="card shadow mb-4">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="fas fa-calendar-alt me-2"></i>Performa Bulan Ini
                </h6>
            </div>
            <div class="card-body">
                @php
                    $thisMonth = now()->startOfMonth();
                    $antrianBulanIni = $admin->antrian()->where('created_at', '>=', $thisMonth)->count();
                    $antrianSelesaiBulanIni = $admin->antrian()->where('created_at', '>=', $thisMonth)->where('status_antrian', 'selesai')->count();
                    $completionRate = $antrianBulanIni > 0 ? ($antrianSelesaiBulanIni / $antrianBulanIni) * 100 : 0;
                @endphp

                <div class="row text-center mb-3">
                    <div class="col-6">
                        <div class="h5 text-primary mb-1">{{ $antrianBulanIni }}</div>
                        <small class="text-muted">Total Antrian</small>
                    </div>
                    <div class="col-6">
                        <div class="h5 text-success mb-1">{{ $antrianSelesaiBulanIni }}</div>
                        <small class="text-muted">Selesai</small>
                    </div>
                </div>

                <div class="mb-3">
                    <div class="d-flex justify-content-between mb-1">
                        <span class="small">Tingkat Penyelesaian</span>
                        <span class="small">{{ number_format($completionRate, 1) }}%</span>
                    </div>
                    <div class="progress" style="height: 8px;">
                        <div class="progress-bar bg-success" style="width: {{ min(100, $completionRate) }}%"></div>
                    </div>
                </div>

                <small class="text-muted d-block text-center">
                    Periode: {{ $thisMonth->format('F Y') }}
                </small>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="card shadow mb-4">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="fas fa-bolt me-2"></i>Quick Actions
                </h6>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="{{ route('admin.edit', $admin->id_admin) }}" class="btn btn-primary">
                        <i class="fas fa-edit me-2"></i>Edit Admin
                    </a>
                    
                    @if($admin->id_admin == Auth::guard('admin')->id())
                    <button type="button" class="btn btn-outline-warning" data-bs-toggle="modal" data-bs-target="#changePasswordModal">
                        <i class="fas fa-key me-2"></i>Ubah Password
                    </button>
                    @endif
                    
                    @if($admin->layanan->count() > 0)
                    <a href="{{ route('layanan.index') }}?admin={{ $admin->id_admin }}" class="btn btn-outline-info">
                        <i class="fas fa-cogs me-2"></i>Lihat Layanan
                    </a>
                    @endif
                    
                    <a href="{{ route('antrian.index') }}?admin={{ $admin->id_admin }}" class="btn btn-outline-success">
                        <i class="fas fa-list me-2"></i>Lihat Antrian
                    </a>
                </div>
            </div>
        </div>

        <!-- Info Keamanan -->
        <div class="card shadow">
            <div class="card-header">
                <h6 class="mb-0 text-warning">
                    <i class="fas fa-shield-alt me-2"></i>Info Keamanan
                </h6>
            </div>
            <div class="card-body">
                <small class="text-muted">
                    <i class="fas fa-clock me-1"></i>
                    <strong>Login terakhir:</strong> {{ $admin->updated_at->format('d/m/Y H:i') }}
                    <br><br>
                    <i class="fas fa-key me-1"></i>
                    <strong>Password:</strong> Terenkripsi dengan aman
                    <br><br>
                    <i class="fas fa-user-check me-1"></i>
                    <strong>Status:</strong> Admin Aktif
                </small>
            </div>
        </div>
    </div>
</div>

<!-- Modal Ubah Password -->
@if($admin->id_admin == Auth::guard('admin')->id())
<div class="modal fade" id="changePasswordModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-key me-2"></i>Ubah Password
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="changePasswordForm">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="current_password" class="form-label">Password Saat Ini <span class="text-danger">*</span></label>
                        <input type="password" class="form-control" id="current_password" name="current_password" required>
                    </div>
                    <div class="mb-3">
                        <label for="new_password" class="form-label">Password Baru <span class="text-danger">*</span></label>
                        <input type="password" class="form-control" id="new_password" name="new_password" required minlength="6">
                        <div class="form-text">Minimal 6 karakter</div>
                    </div>
                    <div class="mb-3">
                        <label for="new_password_confirmation" class="form-label">Konfirmasi Password Baru <span class="text-danger">*</span></label>
                        <input type="password" class="form-control" id="new_password_confirmation" name="new_password_confirmation" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Ubah Password
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

@endsection

@push('scripts')
<script>
// Handle change password form
@if($admin->id_admin == Auth::guard('admin')->id())
document.getElementById('changePasswordForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const submitBtn = this.querySelector('button[type="submit"]');
    
    // Validate password confirmation
    const newPassword = document.getElementById('new_password').value;
    const confirmPassword = document.getElementById('new_password_confirmation').value;
    
    if (newPassword !== confirmPassword) {
        alert('Konfirmasi password tidak sesuai!');
        return;
    }
    
    // Show loading state
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Mengubah...';
    submitBtn.disabled = true;
    
    // Send AJAX request
    fetch('{{ route("admin.change-password", $admin->id_admin) }}', {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Password berhasil diubah!');
            this.reset();
            bootstrap.Modal.getInstance(document.getElementById('changePasswordModal')).hide();
        } else {
            alert(data.error || 'Gagal mengubah password');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Terjadi kesalahan. Silakan coba lagi.');
    })
    .finally(() => {
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    });
});
@endif

// Auto refresh statistics every 30 seconds
setInterval(function() {
    fetch('{{ route("admin.stats", $admin->id_admin) }}')
        .then(response => response.json())
        .then(data => {
            // Update statistics if needed
            console.log('Stats updated:', data);
        })
        .catch(error => console.log('Stats update failed:', error));
}, 30000);

// Initialize tooltips
document.addEventListener('DOMContentLoaded', function() {
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});
</script>
@endpush