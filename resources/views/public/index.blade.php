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

        <!-- Services Section -->
        <div id="services" class="mb-5">
            <div class="info-card text-center">
                <h2 class="fw-bold mb-4" style="color: #2c3e50;">Pelayanan Antrian Online</h2>
                <p class="mb-4" style="color: #6c757d;">Pilih layanan yang Anda butuhkan untuk mengambil nomor antrian</p>
            </div>

            <div class="row g-4">
                <!-- KTP -->
                <div class="col-lg-4 col-md-6">
                    <div class="card service-card h-100 text-center p-4" onclick="selectService('KTP')">
                        <div class="card-body">
                            <div class="service-icon">
                                <i class="fas fa-id-card"></i>
                            </div>
                            <h4 class="fw-bold mb-3">KTP</h4>
                            <p class="text-muted">Kartu Tanda Penduduk</p>
                        </div>
                    </div>
                </div>

                <!-- Kartu Keluarga -->
                <div class="col-lg-4 col-md-6">
                    <div class="card service-card h-100 text-center p-4" onclick="selectService('KK')">
                        <div class="card-body">
                            <div class="service-icon">
                                <i class="fas fa-users"></i>
                            </div>
                            <h4 class="fw-bold mb-3">Kartu Keluarga</h4>
                            <p class="text-muted">Kartu Keluarga</p>
                        </div>
                    </div>
                </div>

                <!-- KIA -->
                <div class="col-lg-4 col-md-6">
                    <div class="card service-card h-100 text-center p-4" onclick="selectService('KIA')">
                        <div class="card-body">
                            <div class="service-icon">
                                <i class="fas fa-child"></i>
                            </div>
                            <h4 class="fw-bold mb-3">Kartu Identitas Anak</h4>
                            <p class="text-muted">KIA untuk anak dibawah 17 tahun</p>
                        </div>
                    </div>
                </div>

                <!-- Akta Kelahiran -->
                <div class="col-lg-4 col-md-6">
                    <div class="card service-card h-100 text-center p-4" onclick="selectService('AKTA')">
                        <div class="card-body">
                            <div class="service-icon">
                                <i class="fas fa-certificate"></i>
                            </div>
                            <h4 class="fw-bold mb-3">Akta Kelahiran</h4>
                            <p class="text-muted">Surat keterangan kelahiran</p>
                        </div>
                    </div>
                </div>

                <!-- Pencatatan Perkawinan -->
                <div class="col-lg-4 col-md-6">
                    <div class="card service-card h-100 text-center p-4" onclick="selectService('KAWIN')">
                        <div class="card-body">
                            <div class="service-icon">
                                <i class="fas fa-heart"></i>
                            </div>
                            <h4 class="fw-bold mb-3">Pencatatan Perkawinan</h4>
                            <p class="text-muted">Surat nikah dan perkawinan</p>
                        </div>
                    </div>
                </div>

                <!-- Akta Kematian -->
                <div class="col-lg-4 col-md-6">
                    <div class="card service-card h-100 text-center p-4" onclick="selectService('MATI')">
                        <div class="card-body">
                            <div class="service-icon">
                                <i class="fas fa-file-alt"></i>
                            </div>
                            <h4 class="fw-bold mb-3">Akta Kematian</h4>
                            <p class="text-muted">Surat keterangan kematian</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
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
                                <input type="text" class="form-control" id="nik" name="nik" required maxlength="16" 
                                       placeholder="16 digit NIK" oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 16)">
                            </div>
                            <div class="col-md-6">
                                <label for="nama" class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="nama" name="nama_pengunjung" required maxlength="100">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="no_hp" class="form-label">No. HP</label>
                                <input type="text" class="form-control" id="no_hp" name="no_hp" maxlength="15" 
                                       placeholder="081234567890" oninput="this.value = this.value.replace(/[^0-9+]/g, '').slice(0, 15)">
                            </div>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-danger btn-lg">
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
        const services = {
            'KTP': { id: 1, name: 'KTP (Kartu Tanda Penduduk)' },
            'KK': { id: 2, name: 'Kartu Keluarga' },
            'KIA': { id: 3, name: 'Kartu Identitas Anak' },
            'AKTA': { id: 4, name: 'Akta Kelahiran' },
            'KAWIN': { id: 5, name: 'Pencatatan Perkawinan' },
            'MATI': { id: 6, name: 'Akta Kematian' }
        };

        function selectService(serviceCode) {
            const service = services[serviceCode];
            document.getElementById('selectedService').value = service.id;
            document.getElementById('serviceName').textContent = service.name;
            
            const modal = new bootstrap.Modal(document.getElementById('formModal'));
            modal.show();
        }

        // Validate NIK length
        document.getElementById('queueForm').addEventListener('submit', function(e) {
            const nik = document.getElementById('nik').value;
            if (nik.length !== 16) {
                e.preventDefault();
                alert('NIK harus 16 digit!');
                return false;
            }
        });
    </script>
</body>
</html>