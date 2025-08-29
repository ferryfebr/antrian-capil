<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Antrian Online - Disdukcapil</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
        .hero-section {
            background: rgba(255,255,255,0.95);
            border-radius: 20px;
            backdrop-filter: blur(10px);
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
        }
        .service-card {
            border-radius: 15px;
            transition: all 0.3s ease;
            cursor: pointer;
            border: none;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .service-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 30px rgba(0,0,0,0.2);
        }
        .service-icon {
            font-size: 3rem;
            color: #dc3545;
            margin-bottom: 1rem;
        }
        .navbar-brand {
            font-weight: bold;
            color: white !important;
        }
        .btn-queue {
            background: linear-gradient(135deg, #28a745, #20c997);
            border: none;
            padding: 15px 30px;
            font-size: 1.1rem;
            border-radius: 25px;
            color: white;
            transition: all 0.3s ease;
        }
        .btn-queue:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(40,167,69,0.3);
            color: white;
        }
        .info-card {
            background: rgba(255,255,255,0.9);
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark" style="background: rgba(0,0,0,0.1);">
        <div class="container">
            <a class="navbar-brand" href="#">
                <i class="fas fa-building me-2"></i>
                DISDUKCAPIL
            </a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="{{ route('public.queue') }}" target="_blank">
                    <i class="fas fa-tv me-2"></i>Display Antrian
                </a>
                <a class="nav-link" href="{{ route('login') }}">
                    <i class="fas fa-sign-in-alt me-2"></i>Login Admin
                </a>
            </div>
        </div>
    </nav>

    <div class="container py-5">
        <!-- Hero Section -->
        <div class="hero-section p-5 mb-5">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <h1 class="display-4 fw-bold mb-4" style="color: #2c3e50;">
                        Selamat datang di<br>
                        <span style="color: #dc3545;">Website Resmi Disdukcapil</span>
                    </h1>
                    <p class="lead mb-4" style="color: #6c757d;">
                        "Terwujudnya Tertib Administrasi Kependudukan dan 
                        Pencatatan Sipil yang Akurat dan Dinamis melalui 
                        Pelayanan Prima menuju Penduduk berkualitas"
                    </p>
                    <div class="mb-4">
                        <p class="mb-2" style="color: #495057;">
                            <i class="fas fa-clock me-2"></i>
                            <strong>Pelayanan : Senin - Jumat (08:00 - 15:00)</strong>
                        </p>
                    </div>
                    <div class="d-flex gap-3">
                        <a href="#services" class="btn btn-queue">
                            <i class="fas fa-list me-2"></i>Ambil Nomor Antrian
                        </a>
                        <a href="{{ route('public.queue') }}" class="btn btn-outline-primary btn-lg" target="_blank">
                            <i class="fas fa-tv me-2"></i>Lihat Display
                        </a>
                    </div>
                </div>
                <div class="col-lg-4 text-center">
                    <div class="p-4">
                        <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3953.0316814889396!2d110.36472931477478!3d-7.782830394365796!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2e7a58350ae531cb%3A0x4c2b8b8b8b8b8b8b!2sJl.%20Malioboro%2C%20Yogyakarta!5e0!3m2!1sen!2sid!4v1234567890" 
                                width="100%" height="250" style="border:0; border-radius: 10px;" allowfullscreen="" loading="lazy"></iframe>
                    </div>
                </div>
            </div>
        </div>

        <!-- Alert jika tidak ada layanan -->
        @if($layanans->count() == 0)
        <div class="alert alert-warning text-center">
            <h4><i class="fas fa-exclamation-triangle me-2"></i>Tidak Ada Layanan Tersedia</h4>
            <p>Saat ini tidak ada layanan yang aktif. Silakan hubungi petugas atau coba lagi nanti.</p>
        </div>
        @else

        <!-- Services Section -->
        <div id="services" class="mb-5">
            <div class="info-card text-center">
                <h2 class="fw-bold mb-4" style="color: #2c3e50;">Pelayanan Antrian Online</h2>
                <p class="mb-4" style="color: #6c757d;">Pilih layanan yang Anda butuhkan untuk mengambil nomor antrian</p>
            </div>

            <div class="row g-4">
                @foreach($layanans as $layanan)
                <div class="col-lg-4 col-md-6">
                    <div class="card service-card h-100 text-center p-4" onclick="selectService({{ $layanan->id_layanan }}, '{{ $layanan->nama_layanan }}', '{{ $layanan->kode_layanan }}')">
                        <div class="card-body">
                            <div class="service-icon">
                                @php
                                    $icon = 'fas fa-file-alt'; // default icon
                                    if(str_contains(strtolower($layanan->kode_layanan), 'ktp')) {
                                        $icon = 'fas fa-id-card';
                                    } elseif(str_contains(strtolower($layanan->kode_layanan), 'kk')) {
                                        $icon = 'fas fa-users';
                                    } elseif(str_contains(strtolower($layanan->kode_layanan), 'kia')) {
                                        $icon = 'fas fa-child';
                                    } elseif(str_contains(strtolower($layanan->kode_layanan), 'akta')) {
                                        $icon = 'fas fa-certificate';
                                    } elseif(str_contains(strtolower($layanan->kode_layanan), 'kawin') || str_contains(strtolower($layanan->kode_layanan), 'nikah')) {
                                        $icon = 'fas fa-heart';
                                    }
                                @endphp
                                <i class="{{ $icon }}"></i>
                            </div>
                            <h4 class="fw-bold mb-3">{{ $layanan->nama_layanan }}</h4>
                            <p class="text-muted">Kode: {{ $layanan->kode_layanan }}</p>
                            <small class="text-info">
                                <i class="fas fa-clock me-1"></i>{{ $layanan->estimasi_durasi_layanan }} menit
                                | <i class="fas fa-users me-1"></i>{{ $layanan->kapasitas_harian }} orang/hari
                            </small>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>

    <!-- Modal Form Antrian -->
    <div class="modal fade" id="formModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-ticket-alt me-2"></i>Form Antrian
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    @if(session('success'))
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                        </div>
                    @endif

                    @if($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form id="queueForm" action="{{ route('public.queue.store') }}" method="POST">
                        @csrf
                        <input type="hidden" id="selectedService" name="id_layanan">
                        
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Layanan terpilih: </strong><span id="serviceName"></span>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="nik" class="form-label">NIK <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('nik') is-invalid @enderror" id="nik" name="nik" 
                                       value="{{ old('nik') }}" required maxlength="16" 
                                       placeholder="16 digit NIK" oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 16)">
                                @error('nik')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="nama" class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('nama_pengunjung') is-invalid @enderror" id="nama" 
                                       name="nama_pengunjung" value="{{ old('nama_pengunjung') }}" required maxlength="100"
                                       placeholder="Nama sesuai KTP">
                                @error('nama_pengunjung')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="no_hp" class="form-label">No. HP (Opsional)</label>
                                <input type="text" class="form-control @error('no_hp') is-invalid @enderror" id="no_hp" 
                                       name="no_hp" value="{{ old('no_hp') }}" maxlength="15" 
                                       placeholder="081234567890" oninput="this.value = this.value.replace(/[^0-9+]/g, '').slice(0, 15)">
                                @error('no_hp')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Untuk notifikasi status antrian</div>
                            </div>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-danger btn-lg" id="submitBtn">
                                <i class="fas fa-ticket-alt me-2"></i>Ambil Nomor Antrian
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        function selectService(id, name, code) {
            document.getElementById('selectedService').value = id;
            document.getElementById('serviceName').textContent = name + ' (' + code + ')';
            
            const modal = new bootstrap.Modal(document.getElementById('formModal'));
            modal.show();
        }

        // Validate NIK length dan form
        document.getElementById('queueForm').addEventListener('submit', function(e) {
            const nik = document.getElementById('nik').value;
            const nama = document.getElementById('nama').value.trim();
            const layanan = document.getElementById('selectedService').value;

            let errors = [];

            if (nik.length !== 16) {
                errors.push('NIK harus 16 digit!');
            }

            if (nama.length < 3) {
                errors.push('Nama minimal 3 karakter!');
            }

            if (!layanan) {
                errors.push('Pilih layanan terlebih dahulu!');
            }

            if (errors.length > 0) {
                e.preventDefault();
                alert('Error:\n- ' + errors.join('\n- '));
                return false;
            }

            // Show loading state
            const submitBtn = document.getElementById('submitBtn');
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Memproses...';
            submitBtn.disabled = true;
        });

        // Show modal if there are validation errors
        @if($errors->any() || session('error'))
            document.addEventListener('DOMContentLoaded', function() {
                const modal = new bootstrap.Modal(document.getElementById('formModal'));
                modal.show();
            });
        @endif

        // Auto-show success message and redirect to ticket if success
        @if(session('success'))
            document.addEventListener('DOMContentLoaded', function() {
                // If there's a success message, it means queue was created but redirect failed
                // Let's try to find the latest queue for this session
                setTimeout(function() {
                    alert('{{ session('success') }}');
                    // Redirect to main page since we can't determine the ticket ID
                    window.location.reload();
                }, 2000);
            });
        @endif
    </script>
</body>
</html>