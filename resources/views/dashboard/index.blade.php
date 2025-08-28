@extends('layouts.app')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

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
            <li><a class="dropdown-item" href="{{ route('dashboard.export') }}">
                <i class="fas fa-download me-2"></i>Export Data
            </a></li>
            <li><a class="dropdown-item" href="{{ route('public.queue') }}" target="_blank">
                <i class="fas fa-tv me-2"></i>Buka Display
            </a></li>
        </ul>
    </div>
</div>
@endsection

@section('content')
<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="card h-100">
            <div class="card-body text-center">
                <div class="d-flex align-items-center justify-content-center mb-3">
                    <div class="bg-primary text-white rounded-circle p-3">
                        <i class="fas fa-list-ol fa-2x"></i>
                    </div>
                </div>
                <h3 class="text-primary" id="totalAntrian">{{ $stats['total_antrian_hari_ini'] }}</h3>
                <p class="text-muted mb-2">Total Antrian Hari Ini</p>
                @if($growth['total_growth'] != 0)
                    <small class="badge bg-{{ $growth['total_growth'] > 0 ? 'success' : 'danger' }}">
                        <i class="fas fa-{{ $growth['total_growth'] > 0 ? 'arrow-up' : 'arrow-down' }}"></i>
                        {{ abs($growth['total_growth']) }}% dari kemarin
                    </small>
                @endif
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="card h-100">
            <div class="card-body text-center">
                <div class="d-flex align-items-center justify-content-center mb-3">
                    <div class="bg-warning text-white rounded-circle p-3">
                        <i class="fas fa-clock fa-2x"></i>
                    </div>
                </div>
                <h3 class="text-warning" id="antrianMenunggu">{{ $stats['antrian_menunggu'] }}</h3>
                <p class="text-muted mb-2">Menunggu</p>
                <small class="text-muted">Sedang menunggu dipanggil</small>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="card h-100">
            <div class="card-body text-center">
                <div class="d-flex align-items-center justify-content-center mb-3">
                    <div class="bg-info text-white rounded-circle p-3">
                        <i class="fas fa-volume-up fa-2x"></i>
                    </div>
                </div>
                <h3 class="text-info" id="antrianDipanggil">{{ $stats['antrian_dipanggil'] }}</h3>
                <p class="text-muted mb-2">Dipanggil</p>
                <small class="text-muted">Sedang dilayani</small>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="card h-100">
            <div class="card-body text-center">
                <div class="d-flex align-items-center justify-content-center mb-3">
                    <div class="bg-success text-white rounded-circle p-3">
                        <i class="fas fa-check fa-2x"></i>
                    </div>
                </div>
                <h3 class="text-success" id="antrianSelesai">{{ $stats['antrian_selesai'] }}</h3>
                <p class="text-muted mb-2">Selesai</p>
                @if($growth['completed_growth'] != 0)
                    <small class="badge bg-{{ $growth['completed_growth'] > 0 ? 'success' : 'danger' }}">
                        <i class="fas fa-{{ $growth['completed_growth'] > 0 ? 'arrow-up' : 'arrow-down' }}"></i>
                        {{ abs($growth['completed_growth']) }}% dari kemarin
                    </small>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Current Queue Alert -->
@if(isset($currentQueue))
<div class="alert alert-info mb-4" id="currentQueueAlert">
    <div class="d-flex align-items-center">
        <i class="fas fa-volume-up fa-2x me-3"></i>
        <div class="flex-grow-1">
            <h5 class="mb-1">Antrian Sedang Dipanggil</h5>
            <p class="mb-0">
                <strong>{{ $currentQueue->nomor_antrian }}</strong> - 
                {{ $currentQueue->pengunjung->nama_pengunjung }} - 
                {{ $currentQueue->layanan->nama_layanan }}
            </p>
        </div>
        <div class="text-end">
            <small class="text-muted">{{ $currentQueue->waktu_dipanggil->format('H:i:s') }}</small>
        </div>
    </div>
</div>
@endif

<div class="row">
    <!-- Chart Section -->
    <div class="col-lg-8">
        <!-- Hourly Statistics -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-chart-line me-2"></i>Statistik Per Jam Hari Ini
                </h5>
            </div>
            <div class="card-body">
                <canvas id="hourlyChart" height="100"></canvas>
            </div>
        </div>
        
        <!-- Recent Activity -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="fas fa-history me-2"></i>Antrian Terbaru
                </h5>
                <a href="{{ route('antrian.index') }}" class="btn btn-outline-primary btn-sm">
                    <i class="fas fa-eye me-2"></i>Lihat Semua
                </a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>No. Antrian</th>
                                <th>Nama</th>
                                <th>Layanan</th>
                                <th>Status</th>
                                <th>Waktu</th>
                                <th>Admin</th>
                            </tr>
                        </thead>
                        <tbody id="recentQueueTable">
                            @forelse($antrian_terbaru as $antrian)
                            <tr>
                                <td><strong class="text-primary">{{ $antrian->nomor_antrian }}</strong></td>
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
                                <td>{{ $antrian->waktu_antrian->format('H:i') }}</td>
                                <td>
                                    @if($antrian->admin)
                                        <small class="text-muted">{{ $antrian->admin->nama_admin }}</small>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">
                                    Belum ada antrian hari ini
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Sidebar -->
    <div class="col-lg-4">
        <!-- Service Efficiency -->
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="fas fa-chart-pie me-2"></i>Efisiensi Layanan
                </h6>
            </div>
            <div class="card-body">
                @foreach($service_efficiency->take(5) as $efficiency)
                <div class="mb-3">
                    <div class="d-flex justify-content-between mb-1">
                        <small class="fw-bold">{{ $efficiency['layanan']->kode_layanan }}</small>
                        <small>{{ $efficiency['utilization_rate'] }}%</small>
                    </div>
                    <div class="progress mb-1" style="height: 8px;">
                        <div class="progress-bar bg-{{ $efficiency['utilization_rate'] > 80 ? 'danger' : ($efficiency['utilization_rate'] > 60 ? 'warning' : 'success') }}" 
                             style="width: {{ min(100, $efficiency['utilization_rate']) }}%"></div>
                    </div>
                    <small class="text-muted">{{ $efficiency['completed_today'] }}/{{ $efficiency['total_today'] }} selesai</small>
                </div>
                @endforeach
            </div>
        </div>
        
        <!-- Popular Services -->
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="fas fa-star me-2"></i>Layanan Terpopuler
                </h6>
            </div>
            <div class="card-body">
                @foreach($layanan_populer as $layanan)
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <div>
                        <strong>{{ $layanan->kode_layanan }}</strong><br>
                        <small class="text-muted">{{ $layanan->nama_layanan }}</small>
                    </div>
                    <div class="text-end">
                        <span class="badge bg-primary fs-6">{{ $layanan->antrian_count }}</span>
                    </div>
                </div>
                @if(!$loop->last)<hr class="my-2">@endif
                @endforeach
                
                @if($layanan_populer->count() == 0)
                <div class="text-center text-muted">
                    <i class="fas fa-chart-bar fa-2x mb-2 opacity-50"></i>
                    <p class="mb-0">Belum ada data layanan hari ini</p>
                </div>
                @endif
            </div>
        </div>
        
        <!-- Active Counters -->
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="fas fa-desktop me-2"></i>Loket Aktif
                </h6>
            </div>
            <div class="card-body">
                @foreach($active_lokets as $loket)
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <div>
                        <strong>{{ $loket->nama_loket }}</strong><br>
                        <small class="text-muted">
                            {{ $loket->layanan ? $loket->layanan->nama_layanan : 'Semua Layanan' }}
                        </small>
                    </div>
                    <span class="badge bg-success">
                        <i class="fas fa-circle me-1"></i>Aktif
                    </span>
                </div>
                @if(!$loop->last)<hr class="my-2">@endif
                @endforeach
                
                @if($active_lokets->count() == 0)
                <div class="text-center text-muted">
                    <i class="fas fa-desktop fa-2x mb-2 opacity-50"></i>
                    <p class="mb-0">Tidak ada loket aktif</p>
                    <a href="{{ route('loket.index') }}" class="btn btn-outline-primary btn-sm mt-2">
                        <i class="fas fa-cog me-1"></i>Kelola Loket
                    </a>
                </div>
                @endif
            </div>
        </div>
        
        <!-- Quick Actions -->
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="fas fa-bolt me-2"></i>Quick Actions
                </h6>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="{{ route('antrian.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>Buat Antrian Baru
                    </a>
                    <a href="{{ route('antrian.index', ['status' => 'menunggu']) }}" class="btn btn-outline-warning">
                        <i class="fas fa-clock me-2"></i>Lihat Antrian Menunggu
                    </a>
                    <a href="{{ route('public.queue') }}" class="btn btn-outline-info" target="_blank">
                        <i class="fas fa-tv me-2"></i>Buka Display Antrian
                    </a>
                    <button type="button" class="btn btn-outline-success" onclick="callNextQueue()">
                        <i class="fas fa-volume-up me-2"></i>Panggil Antrian Berikutnya
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Chart initialization
const hourlyData = @json($hourly_data);
const ctx = document.getElementById('hourlyChart').getContext('2d');

const hourlyChart = new Chart(ctx, {
    type: 'line',
    data: {
        labels: hourlyData.map(item => item.hour),
        datasets: [{
            label: 'Total Antrian',
            data: hourlyData.map(item => item.total),
            borderColor: 'rgb(75, 192, 192)',
            backgroundColor: 'rgba(75, 192, 192, 0.2)',
            tension: 0.4
        }, {
            label: 'Selesai',
            data: hourlyData.map(item => item.completed),
            borderColor: 'rgb(54, 162, 235)',
            backgroundColor: 'rgba(54, 162, 235, 0.2)',
            tension: 0.4
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    stepSize: 1
                }
            }
        }
    }
});

// Real-time updates every 30 seconds
function updateDashboard() {
    fetch('{{ route("dashboard.realtime") }}')
        .then(response => response.json())
        .then(data => {
            // Update statistics
            document.getElementById('totalAntrian').textContent = data.stats.total_antrian_hari_ini;
            document.getElementById('antrianMenunggu').textContent = data.stats.antrian_menunggu;
            document.getElementById('antrianDipanggil').textContent = data.stats.antrian_dipanggil;
            document.getElementById('antrianSelesai').textContent = data.stats.antrian_selesai;
            
            // Update current queue alert
            updateCurrentQueueAlert(data.current_queue);
        })
        .catch(error => console.log('Update failed:', error));
}

function updateCurrentQueueAlert(currentQueue) {
    const alertElement = document.getElementById('currentQueueAlert');
    
    if (currentQueue) {
        if (!alertElement) {
            // Create alert if doesn't exist
            const newAlert = document.createElement('div');
            newAlert.className = 'alert alert-info mb-4';
            newAlert.id = 'currentQueueAlert';
            newAlert.innerHTML = `
                <div class="d-flex align-items-center">
                    <i class="fas fa-volume-up fa-2x me-3"></i>
                    <div class="flex-grow-1">
                        <h5 class="mb-1">Antrian Sedang Dipanggil</h5>
                        <p class="mb-0">
                            <strong>${currentQueue.nomor_antrian}</strong> - 
                            ${currentQueue.pengunjung.nama_pengunjung} - 
                            ${currentQueue.layanan.nama_layanan}
                        </p>
                    </div>
                    <div class="text-end">
                        <small class="text-muted">${new Date(currentQueue.waktu_dipanggil).toLocaleTimeString()}</small>
                    </div>
                </div>
            `;
            document.querySelector('.row').before(newAlert);
        } else {
            // Update existing alert
            alertElement.querySelector('strong').textContent = currentQueue.nomor_antrian;
            alertElement.querySelector('p').innerHTML = `
                <strong>${currentQueue.nomor_antrian}</strong> - 
                ${currentQueue.pengunjung.nama_pengunjung} - 
                ${currentQueue.layanan.nama_layanan}
            `;
        }
    } else if (alertElement) {
        alertElement.remove();
    }
}

// Call next queue function
function callNextQueue() {
    if (confirm('Panggil antrian berikutnya?')) {
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
                alert(`Antrian ${data.antrian.nomor_antrian} berhasil dipanggil!`);
                updateDashboard();
            } else {
                alert(data.message || 'Tidak ada antrian yang menunggu');
            }
        })
        .catch(error => {
            alert('Gagal memanggil antrian');
            console.error('Error:', error);
        });
    }
}

// Start real-time updates
setInterval(updateDashboard, 30000);

// Initial load
document.addEventListener('DOMContentLoaded', function() {
    // Any initialization code here
});
</script>
@endpush