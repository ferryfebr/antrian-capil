@extends('layouts.app')

@section('title', 'Detail Antrian')
@section('page-title', 'Detail Antrian - ' . $antrian->nomor_antrian)

@section('page-actions')
<div class="btn-group">
    @if($antrian->status_antrian == 'menunggu')
        <a href="{{ route('antrian.edit', $antrian->id_antrian) }}" class="btn btn-warning">
            <i class="fas fa-edit me-2"></i>Edit
        </a>
    @endif
    
    <div class="dropdown">
        <button class="btn btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
            <i class="fas fa-cog me-2"></i>Actions
        </button>
        <ul class="dropdown-menu">
            <li><a class="dropdown-item" href="{{ route('public.ticket', $antrian->id_antrian) }}" target="_blank">
                <i class="fas fa-print me-2"></i>Cetak Tiket
            </a></li>
            <li><a class="dropdown-item" href="{{ route('pengunjung.show', $antrian->pengunjung->id_pengunjung) }}">
                <i class="fas fa-user me-2"></i>Lihat Pengunjung
            </a></li>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item text-danger" href="#" onclick="deleteAntrian()">
                <i class="fas fa-trash me-2"></i>Hapus Antrian
            </a></li>
        </ul>
    </div>
    
    <a href="{{ route('antrian.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left me-2"></i>Kembali
    </a>
</div>
@endsection

@section('content')
<div class="row">
    <!-- Detail Antrian -->
    <div class="col-md-8">
        <!-- Status Current -->
        <div class="card shadow mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="fas fa-ticket-alt me-2"></i>Status Antrian
                </h5>
                <div class="text-end">
                    <div class="h2 text-primary mb-0">{{ $antrian->nomor_antrian }}</div>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <!-- Current Status -->
                        <div class="text-center mb-4">
                            @if($antrian->status_antrian == 'menunggu')
                                <div class="mb-3">
                                    <i class="fas fa-clock fa-4x text-warning"></i>
                                </div>
                                <h4 class="text-warning">MENUNGGU</h4>
                                @if($queuePosition)
                                    <p class="text-muted">Posisi dalam antrian: <strong>{{ $queuePosition }}</strong></p>
                                @endif
                            @elseif($antrian->status_antrian == 'dipanggil')
                                <div class="mb-3">
                                    <i class="fas fa-volume-up fa-4x text-primary pulse"></i>
                                </div>
                                <h4 class="text-primary">DIPANGGIL</h4>
                                <p class="text-muted">Silakan menuju loket pelayanan</p>
                            @elseif($antrian->status_antrian == 'selesai')
                                <div class="mb-3">
                                    <i class="fas fa-check-circle fa-4x text-success"></i>
                                </div>
                                <h4 class="text-success">SELESAI</h4>
                                <p class="text-muted">Pelayanan telah selesai</p>
                            @else
                                <div class="mb-3">
                                    <i class="fas fa-times-circle fa-4x text-danger"></i>
                                </div>
                                <h4 class="text-danger">BATAL</h4>
                                <p class="text-muted">Antrian telah dibatalkan</p>
                            @endif
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <!-- Action Buttons -->
                        <div class="d-grid gap-2">
                            @if($antrian->status_antrian == 'menunggu')
                                <button type="button" class="btn btn-primary btn-lg" onclick="updateStatus('dipanggil')">
                                    <i class="fas fa-volume-up me-2"></i>Panggil Sekarang
                                </button>
                                <button type="button" class="btn btn-outline-danger" onclick="updateStatus('batal')">
                                    <i class="fas fa-times me-2"></i>Batalkan
                                </button>
                            @elseif($antrian->status_antrian == 'dipanggil')
                                <button type="button" class="btn btn-success btn-lg" onclick="updateStatus('selesai')">
                                    <i class="fas fa-check me-2"></i>Selesai
                                </button>
                                <button type="button" class="btn btn-outline-warning" onclick="updateStatus('menunggu')">
                                    <i class="fas fa-undo me-2"></i>Kembalikan ke Menunggu
                                </button>
                                <button type="button" class="btn btn-outline-danger" onclick="updateStatus('batal')">
                                    <i class="fas fa-times me-2"></i>Batalkan
                                </button>
                            @elseif($antrian->status_antrian == 'batal')
                                <button type="button" class="btn btn-outline-primary" onclick="updateStatus('menunggu')">
                                    <i class="fas fa-redo me-2"></i>Aktifkan Kembali
                                </button>
                            @endif
                        </div>
                        
                        @if(in_array($antrian->status_antrian, ['menunggu', 'dipanggil']))
                        <div class="mt-3">
                            <small class="text-muted">
                                <i class="fas fa-info-circle me-1"></i>
                                Status dapat diubah sesuai kondisi pelayanan
                            </small>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Detail Informasi -->
        <div class="card shadow mb-4">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="fas fa-info-circle me-2"></i>Informasi Detail
                </h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted">Nomor Antrian</label>
                        <div class="h4 text-primary">{{ $antrian->nomor_antrian }}</div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted">Status</label>
                        <div>
                            @if($antrian->status_antrian == 'menunggu')
                                <span class="badge bg-warning text-dark fs-6">
                                    <i class="fas fa-clock me-1"></i>Menunggu
                                </span>
                            @elseif($antrian->status_antrian == 'dipanggil')
                                <span class="badge bg-primary fs-6">
                                    <i class="fas fa-volume-up me-1"></i>Dipanggil
                                </span>
                            @elseif($antrian->status_antrian == 'selesai')
                                <span class="badge bg-success fs-6">
                                    <i class="fas fa-check me-1"></i>Selesai
                                </span>
                            @else
                                <span class="badge bg-danger fs-6">
                                    <i class="fas fa-times me-1"></i>Batal
                                </span>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted">Nama Pengunjung</label>
                        <div>
                            <strong>{{ $antrian->pengunjung->nama_pengunjung }}</strong>
                            <br><small class="text-muted">NIK: {{ $antrian->pengunjung->nik }}</small>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted">No. HP</label>
                        <div>
                            @if($antrian->pengunjung->no_hp)
                                <a href="tel:{{ $antrian->pengunjung->no_hp }}" class="text-decoration-none">
                                    <i class="fas fa-phone me-1"></i>{{ $antrian->pengunjung->no_hp }}
                                </a>
                            @else
                                <span class="text-muted">Tidak ada</span>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted">Layanan</label>
                        <div>
                            <span class="badge bg-info fs-6">{{ $antrian->layanan->kode_layanan }}</span>
                            <br><strong>{{ $antrian->layanan->nama_layanan }}</strong>
                            <br><small class="text-muted">Estimasi: {{ $antrian->layanan->estimasi_durasi_layanan }} menit</small>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted">Admin</label>
                        <div>
                            @if($antrian->admin)
                                <i class="fas fa-user-shield me-1 text-primary"></i>
                                <strong>{{ $antrian->admin->nama_admin }}</strong>
                                <br><small class="text-muted">{{ $antrian->admin->username }}</small>
                            @else
                                <span class="text-muted">Belum ada admin yang menangani</span>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Timeline -->
                <div class="row mt-4">
                    <div class="col-12">
                        <h6 class="text-primary border-bottom pb-2">
                            <i class="fas fa-history me-2"></i>Timeline
                        </h6>
                        <div class="timeline">
                            <div class="timeline-item">
                                <div class="timeline-marker bg-success"></div>
                                <div class="timeline-content">
                                    <h6 class="mb-1">Antrian Dibuat</h6>
                                    <p class="mb-1">{{ $antrian->waktu_antrian->format('d F Y, H:i:s') }}</p>
                                    <small class="text-muted">{{ $antrian->updated_at->diffForHumans() }}</small>
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Riwayat Status -->
        <div class="card shadow">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="fas fa-clipboard-list me-2"></i>Log Aktivitas
                </h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Waktu</th>
                                <th>Status</th>
                                <th>Admin</th>
                                <th>Keterangan</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>{{ $antrian->created_at->format('d/m/Y H:i:s') }}</td>
                                <td><span class="badge bg-success">Dibuat</span></td>
                                <td>-</td>
                                <td>Antrian pertama kali dibuat</td>
                            </tr>
                            @if($antrian->waktu_dipanggil)
                            <tr>
                                <td>{{ $antrian->waktu_dipanggil->format('d/m/Y H:i:s') }}</td>
                                <td><span class="badge bg-primary">Dipanggil</span></td>
                                <td>{{ $antrian->admin ? $antrian->admin->nama_admin : '-' }}</td>
                                <td>Antrian dipanggil untuk dilayani</td>
                            </tr>
                            @endif
                            @if($antrian->status_antrian == 'selesai')
                            <tr>
                                <td>{{ $antrian->updated_at->format('d/m/Y H:i:s') }}</td>
                                <td><span class="badge bg-success">Selesai</span></td>
                                <td>{{ $antrian->admin ? $antrian->admin->nama_admin : '-' }}</td>
                                <td>Pelayanan selesai</td>
                            </tr>
                            @elseif($antrian->status_antrian == 'batal')
                            <tr>
                                <td>{{ $antrian->updated_at->format('d/m/Y H:i:s') }}</td>
                                <td><span class="badge bg-danger">Batal</span></td>
                                <td>{{ $antrian->admin ? $antrian->admin->nama_admin : '-' }}</td>
                                <td>Antrian dibatalkan</td>
                            </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Sidebar Info -->
    <div class="col-md-4">
        <!-- Quick Stats -->
        <div class="card shadow mb-4">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="fas fa-chart-bar me-2"></i>Statistik Waktu
                </h6>
            </div>
            <div class="card-body">
                @if($antrian->waktu_dipanggil && $antrian->waktu_antrian)
                    @php
                        $waktuTunggu = $antrian->waktu_antrian->diffInMinutes($antrian->waktu_dipanggil);
                    @endphp
                    <div class="mb-3">
                        <label class="small text-muted">Waktu Tunggu</label>
                        <div class="h5 text-warning">{{ $waktuTunggu }} menit</div>
                    </div>
                @endif

                @if($antrian->status_antrian == 'selesai' && $antrian->waktu_dipanggil)
                    @php
                        $waktuLayanan = $antrian->waktu_dipanggil->diffInMinutes($antrian->updated_at);
                    @endphp
                    <div class="mb-3">
                        <label class="small text-muted">Waktu Layanan</label>
                        <div class="h5 text-info">{{ $waktuLayanan }} menit</div>
                    </div>
                    
                    @php
                        $totalWaktu = $antrian->waktu_antrian->diffInMinutes($antrian->updated_at);
                    @endphp
                    <div class="mb-3">
                        <label class="small text-muted">Total Waktu</label>
                        <div class="h5 text-primary">{{ $totalWaktu }} menit</div>
                    </div>
                @endif

                <div class="mb-3">
                    <label class="small text-muted">Estimasi Durasi</label>
                    <div class="h6 text-muted">{{ $antrian->layanan->estimasi_durasi_layanan }} menit</div>
                </div>

                @if($queuePosition && $antrian->status_antrian == 'menunggu')
                <div class="mb-3">
                    <label class="small text-muted">Posisi Antrian</label>
                    <div class="h5 text-warning">{{ $queuePosition }}</div>
                </div>
                @endif
            </div>
        </div>

        <!-- Info Pengunjung -->
        <div class="card shadow mb-4">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="fas fa-user me-2"></i>Info Pengunjung
                </h6>
            </div>
            <div class="card-body">
                <div class="d-flex align-items-center mb-3">
                    <div class="bg-primary rounded-circle p-3 text-white me-3">
                        <i class="fas fa-user"></i>
                    </div>
                    <div>
                        <h6 class="mb-1">{{ $antrian->pengunjung->nama_pengunjung }}</h6>
                        <small class="text-muted">{{ $antrian->pengunjung->nik }}</small>
                    </div>
                </div>

                <div class="mb-2">
                    <small class="text-muted">
                        <i class="fas fa-calendar me-1"></i>
                        Terdaftar: {{ $antrian->pengunjung->waktu_daftar->format('d/m/Y') }}
                    </small>
                </div>

                @if($antrian->pengunjung->no_hp)
                <div class="mb-2">
                    <small class="text-muted">
                        <i class="fas fa-phone me-1"></i>
                        {{ $antrian->pengunjung->no_hp }}
                    </small>
                </div>
                @endif

                <div class="mt-3">
                    <a href="{{ route('pengunjung.show', $antrian->pengunjung->id_pengunjung) }}" 
                       class="btn btn-outline-primary btn-sm">
                        <i class="fas fa-eye me-1"></i>Lihat Detail
                    </a>
                </div>
            </div>
        </div>

        <!-- Info Layanan -->
        <div class="card shadow mb-4">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="fas fa-cogs me-2"></i>Info Layanan
                </h6>
            </div>
            <div class="card-body">
                <div class="d-flex align-items-center mb-3">
                    <div class="bg-info rounded-circle p-3 text-white me-3">
                        <i class="fas fa-cogs"></i>
                    </div>
                    <div>
                        <h6 class="mb-1">{{ $antrian->layanan->nama_layanan }}</h6>
                        <small class="text-muted">{{ $antrian->layanan->kode_layanan }}</small>
                    </div>
                </div>

                <div class="mb-2">
                    <small class="text-muted">
                        <i class="fas fa-clock me-1"></i>
                        Estimasi: {{ $antrian->layanan->estimasi_durasi_layanan }} menit
                    </small>
                </div>

                <div class="mb-2">
                    <small class="text-muted">
                        <i class="fas fa-users me-1"></i>
                        Kapasitas: {{ $antrian->layanan->kapasitas_harian }} orang/hari
                    </small>
                </div>

                <div class="mt-3">
                    <a href="{{ route('layanan.show', $antrian->layanan->id_layanan) }}" 
                       class="btn btn-outline-info btn-sm">
                        <i class="fas fa-eye me-1"></i>Lihat Layanan
                    </a>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="card shadow">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="fas fa-bolt me-2"></i>Quick Actions
                </h6>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="{{ route('public.ticket', $antrian->id_antrian) }}" 
                       class="btn btn-outline-primary" target="_blank">
                        <i class="fas fa-print me-2"></i>Cetak Tiket
                    </a>

                    <a href="{{ route('antrian.create') }}?nik={{ $antrian->pengunjung->nik }}" 
                       class="btn btn-outline-success">
                        <i class="fas fa-plus me-2"></i>Buat Antrian Baru
                    </a>

                    @if(in_array($antrian->status_antrian, ['menunggu', 'dipanggil']))
                    <button type="button" class="btn btn-outline-warning" 
                            onclick="sendNotification()">
                        <i class="fas fa-bell me-2"></i>Kirim Notifikasi
                    </button>
                    @endif

                    <a href="{{ route('antrian.index') }}?layanan={{ $antrian->layanan->id_layanan }}" 
                       class="btn btn-outline-info">
                        <i class="fas fa-list me-2"></i>Antrian Layanan Ini
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('styles')
<style>
.timeline {
    position: relative;
    padding-left: 30px;
}

.timeline::before {
    content: '';
    position: absolute;
    left: 15px;
    top: 0;
    bottom: 0;
    width: 2px;
    background-color: #dee2e6;
}

.timeline-item {
    position: relative;
    margin-bottom: 30px;
}

.timeline-marker {
    position: absolute;
    left: -8px;
    top: 0;
    width: 16px;
    height: 16px;
    border-radius: 50%;
    border: 2px solid #fff;
    box-shadow: 0 0 0 2px #dee2e6;
}

.timeline-content {
    padding-left: 20px;
}

.pulse {
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0% { opacity: 1; }
    50% { opacity: 0.5; }
    100% { opacity: 1; }
}
</style>
@endpush

@push('scripts')
<script>
// Update status function
function updateStatus(newStatus) {
    const confirmMessages = {
        'dipanggil': 'Panggil antrian ini sekarang?',
        'selesai': 'Tandai antrian ini sebagai selesai?',
        'batal': 'Batalkan antrian ini?',
        'menunggu': 'Kembalikan antrian ke status menunggu?'
    };
    
    if (!confirm(confirmMessages[newStatus])) return;
    
    const formData = new FormData();
    formData.append('_token', '{{ csrf_token() }}');
    formData.append('_method', 'PATCH');
    formData.append('status_antrian', newStatus);
    
    // Show loading state
    const buttons = document.querySelectorAll('.btn');
    buttons.forEach(btn => btn.disabled = true);
    
    fetch('{{ route("antrian.update-status", $antrian->id_antrian) }}', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('success', data.message || `Status berhasil diubah ke ${newStatus}`);
            setTimeout(() => location.reload(), 1500);
        } else {
            showAlert('error', data.message || 'Gagal mengubah status');
            buttons.forEach(btn => btn.disabled = false);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('error', 'Terjadi kesalahan saat mengubah status');
        buttons.forEach(btn => btn.disabled = false);
    });
}

// Delete antrian function
function deleteAntrian() {
    if (!confirm('Yakin ingin menghapus antrian ini? Tindakan ini tidak dapat dibatalkan.')) return;
    
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '{{ route("antrian.destroy", $antrian->id_antrian) }}';
    form.innerHTML = `
        @csrf
        @method('DELETE')
    `;
    
    document.body.appendChild(form);
    form.submit();
}

// Send notification function
function sendNotification() {
    if (!confirm('Kirim notifikasi ke pengunjung?')) return;
    
    const formData = new FormData();
    formData.append('_token', '{{ csrf_token() }}');
    
    fetch('{{ route("api.antrian.send-notification", $antrian->id_antrian) }}', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('success', 'Notifikasi berhasil dikirim');
        } else {
            showAlert('warning', data.message || 'Gagal mengirim notifikasi');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('error', 'Gagal mengirim notifikasi');
    });
}

// Show alert function
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

// Auto refresh if status is active
@if(in_array($antrian->status_antrian, ['menunggu', 'dipanggil']))
let refreshInterval = setInterval(() => {
    // Check for status updates
    fetch('{{ route("api.antrian.check-status", $antrian->id_antrian) }}')
        .then(response => response.json())
        .then(data => {
            if (data.status !== '{{ $antrian->status_antrian }}') {
                location.reload();
            }
        })
        .catch(error => console.log('Status check failed:', error));
}, 30000); // 30 seconds

// Cleanup on page unload
window.addEventListener('beforeunload', () => {
    if (refreshInterval) clearInterval(refreshInterval);
});
@endif

// Initialize tooltips
document.addEventListener('DOMContentLoaded', function() {
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});
</script>
@endpushuted">{{ $antrian->waktu_antrian->diffForHumans() }}</small>
                                </div>
                            </div>
                            
                            @if($antrian->waktu_estimasi)
                            <div class="timeline-item">
                                <div class="timeline-marker bg-info"></div>
                                <div class="timeline-content">
                                    <h6 class="mb-1">Estimasi Dipanggil</h6>
                                    <p class="mb-1">{{ $antrian->waktu_estimasi->format('d F Y, H:i') }}</p>
                                    <small class="text-muted">
                                        @if($antrian->waktu_estimasi > now())
                                            {{ $antrian->waktu_estimasi->diffForHumans() }}
                                        @else
                                            {{ $antrian->waktu_estimasi->diffForHumans() }} (terlewat)
                                        @endif
                                    </small>
                                </div>
                            </div>
                            @endif
                            
                            @if($antrian->waktu_dipanggil)
                            <div class="timeline-item">
                                <div class="timeline-marker bg-primary"></div>
                                <div class="timeline-content">
                                    <h6 class="mb-1">Dipanggil</h6>
                                    <p class="mb-1">{{ $antrian->waktu_dipanggil->format('d F Y, H:i:s') }}</p>
                                    <small class="text-muted">{{ $antrian->waktu_dipanggil->diffForHumans() }}</small>
                                    @if($antrian->admin)
                                        <br><small class="text-muted">oleh {{ $antrian->admin->nama_admin }}</small>
                                    @endif
                                </div>
                            </div>
                            @endif
                            
                            @if($antrian->status_antrian == 'selesai')
                            <div class="timeline-item">
                                <div class="timeline-marker bg-success"></div>
                                <div class="timeline-content">
                                    <h6 class="mb-1">Selesai</h6>
                                    <p class="mb-1">{{ $antrian->updated_at->format('d F Y, H:i:s') }}</p>
                                    <small class="text-muted">{{ $antrian->updated_at->diffForHumans() }}</small>
                                </div>
                            </div>
                            @elseif($antrian->status_antrian == 'batal')
                            <div class="timeline-item">
                                <div class="timeline-marker bg-danger"></div>
                                <div class="timeline-content">
                                    <h6 class="mb-1">Dibatalkan</h6>
                                    <p class="mb-1">{{ $antrian->updated_at->format('d F Y, H:i:s') }}</p>
                                    <small class="text-m