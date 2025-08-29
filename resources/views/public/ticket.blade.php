<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tiket Antrian - {{ $antrian->nomor_antrian }}</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .ticket-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.2);
            overflow: hidden;
            max-width: 500px;
            margin: 0 auto;
        }
        
        .ticket-header {
            background: linear-gradient(135deg, #dc3545, #c82333);
            color: white;
            padding: 2rem;
            text-align: center;
            position: relative;
        }
        
        .ticket-number {
            font-size: 4rem;
            font-weight: bold;
            margin: 1rem 0;
            text-shadow: 0 2px 10px rgba(0,0,0,0.3);
        }
        
        .ticket-body {
            padding: 2rem;
        }
        
        .info-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.8rem 0;
            border-bottom: 1px solid #eee;
        }
        
        .info-label {
            font-weight: 600;
            color: #495057;
        }
        
        .info-value {
            color: #212529;
            font-weight: 500;
        }
        
        .status-badge {
            font-size: 1.1rem;
            padding: 0.5rem 1rem;
            border-radius: 20px;
        }
        
        .btn-action {
            border-radius: 25px;
            padding: 12px 30px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn-action:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        
        @media print {
            body {
                background: white;
            }
            .btn, .no-print {
                display: none !important;
            }
            .ticket-container {
                box-shadow: none;
                border: 2px solid #ddd;
            }
        }
        
        .pulse-animation {
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }
    </style>
</head>
<body>
    <div class="container py-5">
        <div class="ticket-container">
            <!-- Header -->
            <div class="ticket-header">
                <div class="d-flex align-items-center justify-content-center mb-3">
                    <i class="fas fa-building fa-2x me-3"></i>
                    <div>
                        <h3 class="mb-0">DISDUKCAPIL</h3>
                        <small>Sistem Antrian Online</small>
                    </div>
                </div>
                
                <div class="ticket-number pulse-animation">
                    {{ $antrian->nomor_antrian }}
                </div>
                
                <div class="h5 mb-0">
                    Antrian Anda:
                    <div class="badge bg-light text-dark fs-6 mt-2">
                        {{ $antrian->layanan->nama_layanan }}
                    </div>
                </div>
            </div>
            
            <!-- Body -->
            <div class="ticket-body">
                <div class="text-center mb-4">
                    @if($antrian->status_antrian == 'menunggu')
                        <span class="badge bg-warning text-dark status-badge pulse-animation">
                            <i class="fas fa-clock me-2"></i>Menunggu
                        </span>
                    @elseif($antrian->status_antrian == 'dipanggil')
                        <span class="badge bg-primary status-badge pulse-animation">
                            <i class="fas fa-volume-up me-2"></i>Dipanggil
                        </span>
                    @elseif($antrian->status_antrian == 'selesai')
                        <span class="badge bg-success status-badge">
                            <i class="fas fa-check me-2"></i>Selesai
                        </span>
                    @else
                        <span class="badge bg-danger status-badge">
                            <i class="fas fa-times me-2"></i>Batal
                        </span>
                    @endif
                </div>
                
                <!-- Info Details -->
                <div class="info-row">
                    <span class="info-label">
                        <i class="fas fa-user me-2 text-primary"></i>Nama
                    </span>
                    <span class="info-value">{{ $antrian->pengunjung->nama_pengunjung }}</span>
                </div>
                
                <div class="info-row">
                    <span class="info-label">
                        <i class="fas fa-id-card me-2 text-primary"></i>NIK
                    </span>
                    <span class="info-value">{{ $antrian->pengunjung->nik }}</span>
                </div>
                
                <div class="info-row">
                    <span class="info-label">
                        <i class="fas fa-cogs me-2 text-primary"></i>Layanan
                    </span>
                    <span class="info-value">{{ $antrian->layanan->nama_layanan }}</span>
                </div>
                
                <div class="info-row">
                    <span class="info-label">
                        <i class="fas fa-clock me-2 text-primary"></i>Waktu Antri
                    </span>
                    <span class="info-value">{{ Carbon\Carbon::parse($antrian->waktu_antrian)->format('d/m/Y H:i') }}</span>
                </div>
                
                @if($antrian->waktu_estimasi)
                <div class="info-row">
                    <span class="info-label">
                        <i class="fas fa-hourglass-half me-2 text-warning"></i>Estimasi
                    </span>
                    <span class="info-value">{{ Carbon\Carbon::parse($antrian->waktu_estimasi)->format('H:i') }}</span>
                </div>
                @endif
                
                <!-- Statistics -->
                <div class="row mt-4 text-center">
                    @php
                        $today = \Carbon\Carbon::today();
                        $antrianSebelum = \App\Models\Antrian::where('id_layanan', $antrian->id_layanan)
                            ->whereDate('waktu_antrian', $today)
                            ->where('waktu_antrian', '<', $antrian->waktu_antrian)
                            ->where('status_antrian', 'menunggu')
                            ->count();
                    @endphp
                    
                    <div class="col-6">
                        <div class="h4 text-primary mb-1">{{ $antrianSebelum }}</div>
                        <small class="text-muted">Antrian Sebelum</small>
                    </div>
                    <div class="col-6">
                        <div class="h4 text-success mb-1">{{ $antrian->layanan->estimasi_durasi_layanan }}</div>
                        <small class="text-muted">Menit/Layanan</small>
                    </div>
                </div>
                
                <!-- Actions -->
                <div class="d-grid gap-2 mt-4 no-print">
                    <button onclick="window.print()" class="btn btn-primary btn-action">
                        <i class="fas fa-print me-2"></i>Cetak Tiket
                    </button>
                    <a href="{{ route('public.queue') }}" class="btn btn-success btn-action" target="_blank">
                        <i class="fas fa-tv me-2"></i>Lihat Display Antrian
                    </a>
                    <a href="{{ route('public.index') }}" class="btn btn-outline-secondary btn-action">
                        <i class="fas fa-home me-2"></i>Kembali ke Beranda
                    </a>
                </div>
                
                <!-- Instructions -->
                <div class="alert alert-info mt-4 no-print">
                    <h6><i class="fas fa-info-circle me-2"></i>Petunjuk:</h6>
                    <ul class="mb-0 small">
                        <li>Simpan tiket ini dengan baik</li>
                        <li>Datang 15 menit sebelum estimasi waktu</li>
                        <li>Pantau display antrian untuk mengetahui giliran Anda</li>
                        <li>Siapkan dokumen yang diperlukan</li>
                    </ul>
                </div>
            </div>
        </div>
        
        <!-- Footer -->
        <div class="text-center mt-4 no-print">
            <p class="text-white">
                <small>© {{ date('Y') }} Dinas Kependudukan dan Pencatatan Sipil</small>
            </p>
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Auto refresh every 30 seconds to check status update
        @if(in_array($antrian->status_antrian, ['menunggu', 'dipanggil']))
        setInterval(function() {
            location.reload();
        }, 30000);
        @endif
        
        // Sound notification when called
        @if($antrian->status_antrian == 'dipanggil')
            // Play notification sound (you can add audio file here)
            console.log('Antrian Anda dipanggil!');
            
            // Show browser notification if supported
            if ('Notification' in window && Notification.permission === 'granted') {
                new Notification('Antrian Dipanggil!', {
                    body: 'Nomor antrian {{ $antrian->nomor_antrian }} sedang dipanggil',
                    icon: '/favicon.ico'
                });
            }
        @endif
        
        // Request notification permission
        if ('Notification' in window && Notification.permission === 'default') {
            Notification.requestPermission();
        }
    </script>
</body>
</html>