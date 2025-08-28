@extends('layouts.app')

@section('title', 'Detail Pengunjung')
@section('page-title', 'Detail Pengunjung - ' . $pengunjung->nama_pengunjung)

@section('page-actions')
<div class="btn-group">
    <a href="{{ route('antrian.create') }}?nik={{ $pengunjung->nik }}" class="btn btn-primary">
        <i class="fas fa-plus me-2"></i>Buat Antrian
    </a>
    <div class="dropdown">
        <button class="btn btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
            <i class="fas fa-cog me-2"></i>Actions
        </button>
        <ul class="dropdown-menu">
            @if($pengunjung->no_hp)
            <li><a class="dropdown-item" href="#" onclick="sendWhatsApp('{{ $pengunjung->no_hp }}')">
                <i class="fab fa-whatsapp me-2"></i>WhatsApp
            </a></li>
            <li><a class="dropdown-item" href="tel:{{ $pengunjung->no_hp }}">
                <i class="fas fa-phone me-2"></i>Telepon
            </a></li>
            <li><hr class="dropdown-divider"></li>
            @endif
            <li><a class="dropdown-item" href="#" onclick="exportPengunjungData()">
                <i class="fas fa-download me-2"></i>Export Data
            </a></li>
            @if(!$pengunjung->antrian()->whereIn('status_antrian', ['menunggu', 'dipanggil'])->exists())
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item text-danger" href="#" onclick="deletePengunjung()">
                <i class="fas fa-trash me-2"></i>Hapus Pengunjung
            </a></li>
            @endif
        </ul>
    </div>
    <a href="{{ route('pengunjung.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left me-2"></i>Kembali
    </a>
</div>
@endsection

@section('content')
<div class="row">
    <!-- Detail Pengunjung -->
    <div class="col-md-8">
        <!-- Informasi Dasar -->
        <div class="card shadow mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">
                    <i class="fas fa-user me-2"></i>Informasi Pengunjung
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3 text-center mb-4">
                        <div class="bg-primary rounded-circle p-4 text-white d-inline-flex align-items-center justify-content-center" style="width: 100px; height: 100px;">
                            <i class="fas fa-user fa-3x"></i>
                        </div>
                        <div class="mt-3">
                            @php
                                $activeQueue = $pengunjung->antrian()
                                    ->whereIn('status_antrian', ['menunggu', 'dipanggil'])
                                    ->first();
                            @endphp
                            @if($activeQueue)
                                <span class="badge bg-warning text-dark fs-6">
                                    <i class="fas fa-clock me-1"></i>Sedang Antri
                                </span>
                            @elseif($pengunjung->antrian()->count() > 0)
                                <span class="badge bg-info fs-6">
                                    <i class="fas fa-history me-1"></i>Member
                                </span>
                            @else
                                <span class="badge bg-light text-dark fs-6">
                                    <i class="fas fa-user-plus me-1"></i>Pengunjung Baru
                                </span>
                            @endif
                        </div>
                    </div>
                    
                    <div class="col-md-9">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-muted">Nama Lengkap</label>
                                <div class="h5">{{ $pengunjung->nama_pengunjung }}</div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-muted">NIK</label>
                                <div class="font-monospace h5 text-primary">{{ $pengunjung->nik }}</div>
                                <small class="text-muted">ID: {{ $pengunjung->id_pengunjung }}</small>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-muted">No. HP</label>
                                <div>
                                    @if($pengunjung->no_hp)
                                        <div class="d-flex align-items-center gap-2">
                                            <i class="fas fa-phone text-success"></i>
                                            <a href="tel:{{ $pengunjung->no_hp }}" class="text-decoration-none h6 mb-0">
                                                {{ $pengunjung->no_hp }}
                                            </a>
                                            <button class="btn btn-success btn-sm" onclick="sendWhatsApp('{{ $pengunjung->no_hp }}')">
                                                <i class="fab fa-whatsapp"></i>
                                            </button>
                                        </div>
                                    @else
                                        <span class="text-muted">
                                            <i class="fas fa-phone-slash me-1"></i>Tidak ada kontak
                                        </span>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-muted">Terdaftar</label>
                                <div>
                                    <strong class="text-success">{{ $pengunjung->waktu_daftar->format('d F Y') }}</strong>
                                    <br><small class="text-muted">{{ $pengunjung->waktu_daftar->format('H:i:s') }}</small>
                                    <br><small class="text-muted">{{ $pengunjung->waktu_daftar->diffForHumans() }}</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Antrian Aktif (jika ada) -->
        @if($activeQueue)
        <div class="card shadow mb-4 border-warning">
            <div class="card-header bg-warning text-dark">
                <h6 class="mb-0">
                    <i class="fas fa-clock me-2"></i>Antrian Aktif
                </h6>
            </div>
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-md-4 text-center">
                        <div class="h2 text-primary">{{ $activeQueue->nomor_antrian }}</div>
                        <span class="badge bg-{{ $activeQueue->status_antrian == 'menunggu' ? 'warning text-dark' : 'primary' }} fs-6">
                            {{ ucfirst($activeQueue->status_antrian) }}
                        </span>
                    </div>
                    <div class="col-md-8">
                        <div class="row">
                            <div class="col-6">
                                <small class="text-muted">Layanan:</small>
                                <br><strong>{{ $activeQueue->layanan->nama_layanan }}</strong>
                            </div>
                            <div class="col-6">
                                <small class="text-muted">Waktu Antri:</small>
                                <br><strong>{{ $activeQueue->waktu_antrian->format('H:i') }}</strong>
                            </div>
                        </div>
                        <div class="mt-3">
                            <a href="{{ route('antrian.show', $activeQueue->id_antrian) }}" class="btn btn-primary btn-sm">
                                <i class="fas fa-eye me-1"></i>Lihat Detail Antrian
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Riwayat Antrian -->
        <div class="card shadow">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0">
                    <i class="fas fa-history me-2"></i>Riwayat Antrian
                </h6>
                <small class="text-muted">Total: {{ $pengunjung->antrian()->count() }} antrian</small>
            </div>
            <div class="card-body">
                @if($pengunjung->antrian()->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Tanggal</th>
                                    <th>No. Antrian</th>
                                    <th>Layanan</th>
                                    <th>Status</th>
                                    <th>Waktu</th>
                                    <th>Admin</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($pengunjung->antrian()->with(['layanan', 'admin'])->orderBy('waktu_antrian', 'desc')->get() as $antrian)
                                <tr>
                                    <td>{{ $antrian->waktu_antrian->format('d/m/Y') }}</td>
                                    <td>
                                        <strong class="text-primary">{{ $antrian->nomor_antrian }}</strong>
                                    </td>
                                    <td>
                                        <span class="badge bg-info">{{ $antrian->layanan->kode_layanan }}</span>
                                        <br><small class="text-muted">{{ $antrian->layanan->nama_layanan }}</small>
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
                                    <td>
                                        <small>
                                            Antri: {{ $antrian->waktu_antrian->format('H:i') }}
                                            @if($antrian->waktu_dipanggil)
                                                <br>Panggil: {{ $antrian->waktu_dipanggil->format('H:i') }}
                                            @endif
                                        </small>
                                    </td>
                                    <td>
                                        @if($antrian->admin)
                                            <small>{{ $antrian->admin->nama_admin }}</small>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('antrian.show', $antrian->id_antrian) }}" 
                                           class="btn btn-outline-primary btn-sm">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-4">
                        <i class="fas fa-list-ol fa-3x text-muted mb-3"></i>
                        <h6 class="text-muted">Belum Ada Riwayat Antrian</h6>
                        <p class="text-muted">Pengunjung ini belum pernah mengambil antrian</p>
                        <a href="{{ route('antrian.create') }}?nik={{ $pengunjung->nik }}" class="btn btn-primary">
                            <i class="fas fa-plus me-2"></i>Buat Antrian Pertama
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Sidebar Statistik -->
    <div class="col-md-4">
        <!-- Statistik Pengunjung -->
        <div class="card shadow mb-4">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="fas fa-chart-bar me-2"></i>Statistik
                </h6>
            </div>
            <div class="card-body">
                @php
                    $stats = [
                        'total_antrian' => $pengunjung->antrian()->count(),
                        'selesai' => $pengunjung->antrian()->where('status_antrian', 'selesai')->count(),
                        'batal' => $pengunjung->antrian()->where('status_antrian', 'batal')->count(),
                        'aktif' => $pengunjung->antrian()->whereIn('status_antrian', ['menunggu', 'dipanggil'])->count(),
                    ];
                    $completionRate = $stats['total_antrian'] > 0 ? ($stats['selesai'] / $stats['total_antrian']) * 100 : 0;
                @endphp

                <div class="row text-center mb-3">
                    <div class="col-6">
                        <div class="h4 text-primary mb-1">{{ $stats['total_antrian'] }}</div>
                        <small class="text-muted">Total Antrian</small>
                    </div>
                    <div class="col-6">
                        <div class="h4 text-success mb-1">{{ $stats['selesai'] }}</div>
                        <small class="text-muted">Selesai</small>
                    </div>
                </div>

                <div class="row text-center mb-3">
                    <div class="col-6">
                        <div class="h4 text-warning mb-1">{{ $stats['aktif'] }}</div>
                        <small class="text-muted">Aktif</small>
                    </div>
                    <div class="col-6">
                        <div class="h4 text-danger mb-1">{{ $stats['batal'] }}</div>
                        <small class="text-muted">Batal</small>
                    </div>
                </div>

                <div class="mb-3">
                    <div class="d-flex justify-content-between mb-1">
                        <span class="small">Tingkat Penyelesaian</span>
                        <span class="small">{{ number_format($completionRate, 1) }}%</span>
                    </div>
                    <div class="progress" style="height: 8px;">
                        <div class="progress-bar bg-success" style="width: {{ $completionRate }}%"></div>
                    </div>
                </div>

                @if($stats['total_antrian'] > 0)
                <hr>
                <div class="small text-muted">
                    <div class="d-flex justify-content-between">
                        <span>Kunjungan pertama:</span>
                        <strong>{{ $pengunjung->antrian()->oldest('waktu_antrian')->first()?->waktu_antrian->format('d/m/Y') }}</strong>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span>Kunjungan terakhir:</span>
                        <strong>{{ $pengunjung->antrian()->latest('waktu_antrian')->first()?->waktu_antrian->format('d/m/Y') }}</strong>
                    </div>
                </div>
                @endif
            </div>
        </div>

        <!-- Layanan Favorit -->
        <div class="card shadow mb-4">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="fas fa-heart me-2"></i>Layanan Favorit
                </h6>
            </div>
            <div class="card-body">
                @php
                    $favoriteServices = $pengunjung->antrian()
                        ->select('id_layanan', \DB::raw('count(*) as total'))
                        ->with('layanan')
                        ->groupBy('id_layanan')
                        ->orderBy('total', 'desc')
                        ->get();
                @endphp

                @if($favoriteServices->count() > 0)
                    @foreach($favoriteServices as $service)
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div>
                            <span class="badge bg-info">{{ $service->layanan->kode_layanan }}</span>
                            <br><small class="text-muted">{{ $service->layanan->nama_layanan }}</small>
                        </div>
                        <div class="text-end">
                            <div class="h6 text-primary mb-0">{{ $service->total }}</div>
                            <small class="text-muted">kali</small>
                        </div>
                    </div>
                    @if(!$loop->last)<hr class="my-2">@endif
                    @endforeach
                @else
                    <div class="text-center text-muted py-3">
                        <i class="fas fa-heart fa-2x mb-2 opacity-50"></i>
                        <p class="mb-0">Belum ada layanan favorit</p>
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
                    <a href="{{ route('antrian.create') }}?nik={{ $pengunjung->nik }}" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>Buat Antrian Baru
                    </a>
                    
                    @if($pengunjung->no_hp)
                    <button type="button" class="btn btn-success" onclick="sendWhatsApp('{{ $pengunjung->no_hp }}')">
                        <i class="fab fa-whatsapp me-2"></i>Kirim WhatsApp
                    </button>
                    @endif

                    <a href="{{ route('pengunjung.index') }}?search={{ $pengunjung->nik }}" class="btn btn-outline-info">
                        <i class="fas fa-search me-2"></i>Cari Serupa
                    </a>

                    <button type="button" class="btn btn-outline-secondary" onclick="exportPengunjungData()">
                        <i class="fas fa-download me-2"></i>Export Data
                    </button>
                </div>
            </div>
        </div>

        <!-- Info Keamanan -->
        <div class="card shadow">
            <div class="card-header">
                <h6 class="mb-0 text-info">
                    <i class="fas fa-shield-alt me-2"></i>Info Keamanan
                </h6>
            </div>
            <div class="card-body">
                <small class="text-muted">
                    <i class="fas fa-user-check me-1"></i>
                    <strong>NIK:</strong> Terverifikasi 16 digit
                    <br><br>
                    <i class="fas fa-calendar me-1"></i>
                    <strong>Terdaftar:</strong> {{ $pengunjung->waktu_daftar->format('d/m/Y H:i') }}
                    <br><br>
                    <i class="fas fa-database me-1"></i>
                    <strong>Data:</strong> Tersimpan aman di sistem
                    <br><br>
                    @if($pengunjung->no_hp)
                        <i class="fas fa-phone me-1"></i>
                        <strong>Kontak:</strong> Dapat dihubungi
                    @else
                        <i class="fas fa-phone-slash me-1"></i>
                        <strong>Kontak:</strong> Tidak tersedia
                    @endif
                </small>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
// WhatsApp function
function sendWhatsApp(phoneNumber) {
    const message = encodeURIComponent(`Halo ${pengunjung.nama_pengunjung}, ini adalah notifikasi dari Disdukcapil mengenai layanan antrian Anda.`);
    const whatsappUrl = `https://wa.me/${phoneNumber.replace(/[^0-9]/g, '')}?text=${message}`;
    window.open(whatsappUrl, '_blank');
}

// Export function
function exportPengunjungData() {
    const url = `{{ route('pengunjung.export-single', $pengunjung->id_pengunjung) }}`;
    window.open(url, '_blank');
}

// Delete function
function deletePengunjung() {
    if (!confirm(`Yakin ingin menghapus data pengunjung {{ $pengunjung->nama_pengunjung }}?\n\nSemua riwayat antrian juga akan terhapus.\n\nTindakan ini tidak dapat dibatalkan.`)) {
        return;
    }
    
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '{{ route("pengunjung.destroy", $pengunjung->id_pengunjung) }}';
    form.innerHTML = `
        @csrf
        @method('DELETE')
    `;
    
    document.body.appendChild(form);
    form.submit();
}

// Auto refresh if has active queue
@if($activeQueue)
setInterval(() => {
    // Check for queue status updates
    fetch(`{{ route('api.antrian.check-status', $activeQueue->id_antrian) }}`)
        .then(response => response.json())
        .then(data => {
            if (data.status !== '{{ $activeQueue->status_antrian }}') {
                location.reload();
            }
        })
        .catch(error => console.log('Status check failed:', error));
}, 30000); // 30 seconds
@endif

// Initialize tooltips
document.addEventListener('DOMContentLoaded', function() {
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});
</script>
@endpush