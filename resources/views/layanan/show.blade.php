@extends('layouts.app')

@section('title', 'Detail Layanan')
@section('page-title', 'Detail Layanan - ' . $layanan->nama_layanan)

@section('page-actions')
<div class="btn-group">
    <a href="{{ route('layanan.edit', $layanan->id_layanan) }}" class="btn btn-primary">
        <i class="fas fa-edit me-2"></i>Edit
    </a>
    <form action="{{ route('layanan.toggle-status', $layanan->id_layanan) }}" method="POST" style="display: inline;">
        @csrf
        @method('PATCH')
        <button type="submit" 
                class="btn btn-{{ $layanan->aktif ? 'warning' : 'success' }}"
                onclick="return confirm('Yakin ingin {{ $layanan->aktif ? 'menonaktifkan' : 'mengaktifkan' }} layanan ini?')">
            <i class="fas fa-{{ $layanan->aktif ? 'toggle-off' : 'toggle-on' }} me-2"></i>
            {{ $layanan->aktif ? 'Nonaktifkan' : 'Aktifkan' }}
        </button>
    </form>
    <a href="{{ route('layanan.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left me-2"></i>Kembali
    </a>
</div>
@endsection

@section('content')
<div class="row">
    <!-- Detail Layanan -->
    <div class="col-md-8">
        <div class="card shadow mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">
                    <i class="fas fa-info-circle me-2"></i>Informasi Layanan
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted">Nama Layanan</label>
                        <div class="h5">{{ $layanan->nama_layanan }}</div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted">Kode Layanan</label>
                        <div>
                            <span class="badge bg-primary fs-5">{{ $layanan->kode_layanan }}</span>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted">Estimasi Durasi</label>
                        <div class="text-info">
                            <i class="fas fa-clock me-2"></i>
                            <strong>{{ $layanan->estimasi_durasi_layanan }} menit</strong>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted">Kapasitas Harian</label>
                        <div class="text-success">
                            <i class="fas fa-users me-2"></i>
                            <strong>{{ $layanan->kapasitas_harian }} orang</strong>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted">Status</label>
                        <div>
                            @if($layanan->aktif)
                                <span class="badge bg-success fs-6">
                                    <i class="fas fa-check me-1"></i>Aktif
                                </span>
                            @else
                                <span class="badge bg-danger fs-6">
                                    <i class="fas fa-times me-1"></i>Tidak Aktif
                                </span>
                            @endif
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted">Admin Penanggung Jawab</label>
                        <div>
                            @if($layanan->admin)
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-user-circle fa-lg text-muted me-2"></i>
                                    <div>
                                        <strong>{{ $layanan->admin->nama_admin }}</strong><br>
                                        <small class="text-muted">{{ $layanan->admin->username }}</small>
                                    </div>
                                </div>
                            @else
                                <span class="text-muted">Tidak ada</span>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Perhitungan Efisiensi -->
                <div class="row mt-3">
                    <div class="col-12">
                        <div class="alert alert-info">
                            <h6><i class="fas fa-calculator me-2"></i>Perhitungan Efisiensi:</h6>
                            @php
                                $totalWaktu = ($layanan->estimasi_durasi_layanan * $layanan->kapasitas_harian) / 60;
                                $jamKerjaEfektif = $totalWaktu / 8;
                            @endphp
                            <div class="row">
                                <div class="col-md-6">
                                    <small>
                                        <strong>Total waktu operasi:</strong> {{ number_format($totalWaktu, 1) }} jam<br>
                                        <strong>Efektif dalam:</strong> {{ number_format($jamKerjaEfektif, 1) }} hari kerja
                                    </small>
                                </div>
                                <div class="col-md-6">
                                    <small>
                                        <strong>Waktu per antrian:</strong> {{ $layanan->estimasi_durasi_layanan }} menit<br>
                                        <strong>Antrian per jam:</strong> {{ number_format(60 / $layanan->estimasi_durasi_layanan, 1) }} orang
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Timestamp -->
                <div class="row mt-3">
                    <div class="col-12">
                        <hr>
                        <small class="text-muted">
                            <strong>Dibuat:</strong> {{ $layanan->created_at->format('d/m/Y H:i') }} 
                            @if($layanan->created_at != $layanan->updated_at)
                                | <strong>Diperbarui:</strong> {{ $layanan->updated_at->format('d/m/Y H:i') }}
                            @endif
                        </small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Antrian Terbaru -->
        <div class="card shadow">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="fas fa-list me-2"></i>Antrian Terbaru Hari Ini
                </h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>No. Antrian</th>
                                <th>Nama</th>
                                <th>Status</th>
                                <th>Waktu</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($antrian_terbaru as $antrian)
                            <tr>
                                <td><strong>{{ $antrian->nomor_antrian }}</strong></td>
                                <td>{{ $antrian->pengunjung->nama_pengunjung }}</td>
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
                                <td>{{ $antrian->waktu_antrian->format('H:i') }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted">Belum ada antrian hari ini</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($antrian_terbaru->count() > 0)
                <div class="mt-2">
                    <a href="{{ route('antrian.index', ['layanan' => $layanan->id_layanan]) }}" class="btn btn-outline-primary btn-sm">
                        <i class="fas fa-eye me-1"></i>Lihat Semua Antrian
                    </a>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Sidebar Statistik -->
    <div class="col-md-4">
        <!-- Statistik Hari Ini -->
        <div class="card shadow mb-4">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="fas fa-chart-pie me-2"></i>Statistik Hari Ini
                </h6>
            </div>
            <div class="card-body">
                <div class="row text-center mb-3">
                    <div class="col-6">
                        <div class="h4 text-primary mb-1">{{ $stats['total_antrian_hari_ini'] }}</div>
                        <small class="text-muted">Total Antrian</small>
                    </div>
                    <div class="col-6">
                        <div class="h4 text-warning mb-1">{{ $stats['antrian_menunggu'] }}</div>
                        <small class="text-muted">Menunggu</small>
                    </div>
                </div>

                <div class="row text-center mb-3">
                    <div class="col-6">
                        <div class="h4 text-success mb-1">{{ $stats['antrian_selesai'] }}</div>
                        <small class="text-muted">Selesai</small>
                    </div>
                    <div class="col-6">
                        <div class="h4 text-danger mb-1">{{ $stats['antrian_batal'] }}</div>
                        <small class="text-muted">Batal</small>
                    </div>
                </div>

                <div class="progress mb-3" style="height: 8px;">
                    @php
                        $total = $stats['total_antrian_hari_ini'];
                        $selesaiPercent = $total > 0 ? ($stats['antrian_selesai'] / $total) * 100 : 0;
                        $menungguPercent = $total > 0 ? ($stats['antrian_menunggu'] / $total) * 100 : 0;
                        $batalPercent = $total > 0 ? ($stats['antrian_batal'] / $total) * 100 : 0;
                    @endphp
                    <div class="progress-bar bg-success" style="width: {{ $selesaiPercent }}%" title="Selesai: {{ number_format($selesaiPercent, 1) }}%"></div>
                    <div class="progress-bar bg-warning" style="width: {{ $menungguPercent }}%" title="Menunggu: {{ number_format($menungguPercent, 1) }}%"></div>
                    <div class="progress-bar bg-danger" style="width: {{ $batalPercent }}%" title="Batal: {{ number_format($batalPercent, 1) }}%"></div>
                </div>

                <small class="text-muted d-block text-center">
                    Tingkat penyelesaian: {{ $total > 0 ? number_format($selesaiPercent, 1) : 0 }}%
                </small>
            </div>
        </div>

        <!-- Info Loket -->
        <div class="card shadow mb-4">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="fas fa-desktop me-2"></i>Informasi Loket
                </h6>
            </div>
            <div class="card-body">
                <div class="row text-center mb-3">
                    <div class="col-6">
                        <div class="h5 text-info mb-1">{{ $stats['total_loket'] }}</div>
                        <small class="text-muted">Total Loket</small>
                    </div>
                    <div class="col-6">
                        <div class="h5 text-success mb-1">{{ $stats['loket_aktif'] }}</div>
                        <small class="text-muted">Loket Aktif</small>
                    </div>
                </div>

                @if($layanan->loket->count() > 0)
                    <hr>
                    <h6 class="mb-2">Daftar Loket:</h6>
                    @foreach($layanan->loket as $loket)
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span>{{ $loket->nama_loket }}</span>
                            <span class="badge bg-{{ $loket->status_loket == 'aktif' ? 'success' : 'secondary' }}">
                                {{ ucfirst($loket->status_loket) }}
                            </span>
                        </div>
                    @endforeach
                @else
                    <div class="text-center text-muted">
                        <i class="fas fa-desktop fa-2x mb-2 opacity-50"></i>
                        <p class="mb-2">Belum ada loket</p>
                        <a href="{{ route('loket.create') }}" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-plus me-1"></i>Tambah Loket
                        </a>
                    </div>
                @endif
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
                    <a href="{{ route('antrian.create') }}?layanan={{ $layanan->id_layanan }}" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>Buat Antrian Baru
                    </a>
                    
                    <a href="{{ route('antrian.index', ['layanan' => $layanan->id_layanan, 'status' => 'menunggu']) }}" 
                       class="btn btn-outline-warning">
                        <i class="fas fa-clock me-2"></i>Lihat Antrian Menunggu
                    </a>
                    
                    @if($layanan->loket->count() > 0)
                        <a href="{{ route('loket.index', ['layanan' => $layanan->id_layanan]) }}" 
                           class="btn btn-outline-info">
                            <i class="fas fa-desktop me-2"></i>Kelola Loket
                        </a>
                    @endif
                    
                    <hr class="my-2">
                    
                    <button type="button" class="btn btn-outline-secondary" onclick="exportLayananData()">
                        <i class="fas fa-download me-2"></i>Export Data
                    </button>
                </div>
            </div>
        </div>

        <!-- Performance Metrics -->
        <div class="card shadow">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="fas fa-tachometer-alt me-2"></i>Performa Layanan
                </h6>
            </div>
            <div class="card-body">
                @php
                    $avgWaitTime = 0;
                    $completionRate = $stats['total_antrian_hari_ini'] > 0 ? 
                        ($stats['antrian_selesai'] / $stats['total_antrian_hari_ini']) * 100 : 0;
                    $utilizationRate = $layanan->kapasitas_harian > 0 ? 
                        ($stats['total_antrian_hari_ini'] / $layanan->kapasitas_harian) * 100 : 0;
                @endphp

                <div class="mb-3">
                    <div class="d-flex justify-content-between mb-1">
                        <span class="small">Tingkat Penyelesaian</span>
                        <span class="small">{{ number_format($completionRate, 1) }}%</span>
                    </div>
                    <div class="progress" style="height: 6px;">
                        <div class="progress-bar bg-success" style="width: {{ min(100, $completionRate) }}%"></div>
                    </div>
                </div>

                <div class="mb-3">
                    <div class="d-flex justify-content-between mb-1">
                        <span class="small">Utilisasi Kapasitas</span>
                        <span class="small">{{ number_format($utilizationRate, 1) }}%</span>
                    </div>
                    <div class="progress" style="height: 6px;">
                        <div class="progress-bar bg-{{ $utilizationRate > 80 ? 'danger' : ($utilizationRate > 60 ? 'warning' : 'info') }}" 
                             style="width: {{ min(100, $utilizationRate) }}%"></div>
                    </div>
                </div>

                <small class="text-muted">
                    <i class="fas fa-info-circle me-1"></i>
                    Sisa kapasitas: {{ max(0, $layanan->kapasitas_harian - $stats['total_antrian_hari_ini']) }} orang
                </small>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
function exportLayananData() {
    const layananId = {{ $layanan->id_layanan }};
    const url = `{{ route('layanan.export') }}?layanan_id=${layananId}`;
    window.open(url, '_blank');
}

// Auto refresh statistik setiap 30 detik
setInterval(function() {
    const statsCards = document.querySelectorAll('[data-refresh="stats"]');
    // Implement AJAX refresh for stats if needed
}, 30000);

// Tooltip untuk progress bars
document.addEventListener('DOMContentLoaded', function() {
    const progressBars = document.querySelectorAll('.progress-bar[title]');
    progressBars.forEach(bar => {
        bar.setAttribute('data-bs-toggle', 'tooltip');
        bar.setAttribute('data-bs-placement', 'top');
    });
    
    // Initialize Bootstrap tooltips if available
    if (typeof bootstrap !== 'undefined') {
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    }
});
</script>
@endpush