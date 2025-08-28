@extends('layouts.app')

@section('title', 'Tambah Layanan')
@section('page-title', 'Tambah Layanan Baru')

@section('page-actions')
<a href="{{ route('layanan.index') }}" class="btn btn-secondary">
    <i class="fas fa-arrow-left me-2"></i>Kembali
</a>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">
                    <i class="fas fa-plus-circle me-2"></i>Form Tambah Layanan
                </h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('layanan.store') }}" id="layananForm">
                    @csrf
                    
                    <!-- Info Layanan -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h6 class="text-primary border-bottom pb-2">
                                <i class="fas fa-info-circle me-2"></i>Informasi Layanan
                            </h6>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-8">
                            <label for="nama_layanan" class="form-label">Nama Layanan <span class="text-danger">*</span></label>
                            <input type="text" 
                                   class="form-control @error('nama_layanan') is-invalid @enderror" 
                                   id="nama_layanan" 
                                   name="nama_layanan" 
                                   value="{{ old('nama_layanan') }}" 
                                   required
                                   maxlength="100"
                                   placeholder="Contoh: Kartu Tanda Penduduk">
                            @error('nama_layanan')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-4">
                            <label for="kode_layanan" class="form-label">Kode Layanan <span class="text-danger">*</span></label>
                            <input type="text" 
                                   class="form-control @error('kode_layanan') is-invalid @enderror" 
                                   id="kode_layanan" 
                                   name="kode_layanan" 
                                   value="{{ old('kode_layanan') }}" 
                                   required
                                   maxlength="10"
                                   placeholder="Contoh: KTP"
                                   style="text-transform: uppercase;">
                            @error('kode_layanan')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Maksimal 10 karakter, akan diubah ke huruf kapital</div>
                        </div>
                    </div>

                    <!-- Konfigurasi Layanan -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h6 class="text-primary border-bottom pb-2">
                                <i class="fas fa-cogs me-2"></i>Konfigurasi Layanan
                            </h6>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="estimasi_durasi_layanan" class="form-label">Estimasi Durasi <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="number" 
                                       class="form-control @error('estimasi_durasi_layanan') is-invalid @enderror" 
                                       id="estimasi_durasi_layanan" 
                                       name="estimasi_durasi_layanan" 
                                       value="{{ old('estimasi_durasi_layanan', 30) }}" 
                                       required
                                       min="1"
                                       max="999">
                                <span class="input-group-text">menit</span>
                            </div>
                            @error('estimasi_durasi_layanan')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Rata-rata waktu pelayanan per orang</div>
                        </div>
                        <div class="col-md-6">
                            <label for="kapasitas_harian" class="form-label">Kapasitas Harian <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="number" 
                                       class="form-control @error('kapasitas_harian') is-invalid @enderror" 
                                       id="kapasitas_harian" 
                                       name="kapasitas_harian" 
                                       value="{{ old('kapasitas_harian', 50) }}" 
                                       required
                                       min="1"
                                       max="999">
                                <span class="input-group-text">orang</span>
                            </div>
                            @error('kapasitas_harian')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Maksimal antrian per hari</div>
                        </div>
                    </div>

                    <!-- Status dan Admin -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h6 class="text-primary border-bottom pb-2">
                                <i class="fas fa-user-cog me-2"></i>Status dan Admin
                            </h6>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="id_admin" class="form-label">Admin Penanggung Jawab</label>
                            <select class="form-select @error('id_admin') is-invalid @enderror" 
                                    id="id_admin" 
                                    name="id_admin">
                                <option value="">-- Pilih Admin --</option>
                                @foreach($admins as $admin)
                                    <option value="{{ $admin->id_admin }}" 
                                            {{ old('id_admin') == $admin->id_admin ? 'selected' : '' }}>
                                        {{ $admin->nama_admin }} ({{ $admin->username }})
                                    </option>
                                @endforeach
                            </select>
                            @error('id_admin')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Opsional - Admin yang bertanggung jawab atas layanan ini</div>
                        </div>
                        <div class="col-md-6 d-flex align-items-end">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="aktif" name="aktif" 
                                       {{ old('aktif', true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="aktif">
                                    <strong>Layanan Aktif</strong>
                                </label>
                                <div class="form-text">Centang untuk mengaktifkan layanan</div>
                            </div>
                        </div>
                    </div>

                    <!-- Preview Konfigurasi -->
                    <div class="alert alert-info" id="previewConfig">
                        <h6><i class="fas fa-eye me-2"></i>Preview Konfigurasi:</h6>
                        <div class="row">
                            <div class="col-md-6">
                                <small>
                                    <strong>Durasi per layanan:</strong> <span id="previewDurasi">30</span> menit<br>
                                    <strong>Kapasitas harian:</strong> <span id="previewKapasitas">50</span> orang
                                </small>
                            </div>
                            <div class="col-md-6">
                                <small>
                                    <strong>Total waktu operasi:</strong> <span id="previewWaktuTotal">25</span> jam<br>
                                    <strong>Efektif dalam:</strong> <span id="previewJamKerja">3.1</span> jam kerja (8 jam/hari)
                                </small>
                            </div>
                        </div>
                    </div>

                    <!-- Tombol Submit -->
                    <div class="row">
                        <div class="col-12">
                            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                <a href="{{ route('layanan.index') }}" class="btn btn-secondary me-md-2">
                                    <i class="fas fa-times me-2"></i>Batal
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>Simpan Layanan
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Petunjuk -->
        <div class="card mt-4">
            <div class="card-body">
                <h6 class="text-success"><i class="fas fa-lightbulb me-2"></i>Petunjuk Pengisian:</h6>
                <ul class="mb-0 small">
                    <li><strong>Nama Layanan:</strong> Nama lengkap layanan yang mudah dipahami</li>
                    <li><strong>Kode Layanan:</strong> Singkatan unik untuk identifikasi (akan muncul di nomor antrian)</li>
                    <li><strong>Estimasi Durasi:</strong> Perkiraan waktu rata-rata pelayanan per orang</li>
                    <li><strong>Kapasitas Harian:</strong> Batas maksimal antrian yang bisa dilayani per hari</li>
                    <li><strong>Status Aktif:</strong> Hanya layanan aktif yang bisa dipilih untuk ambil antrian</li>
                </ul>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
// Auto uppercase untuk kode layanan
document.getElementById('kode_layanan').addEventListener('input', function() {
    this.value = this.value.toUpperCase().replace(/[^A-Z0-9]/g, '');
});

// Generate kode otomatis dari nama layanan
document.getElementById('nama_layanan').addEventListener('input', function() {
    const nama = this.value;
    const kodeInput = document.getElementById('kode_layanan');
    
    if (kodeInput.value === '' && nama.length > 0) {
        // Ambil huruf pertama dari setiap kata
        const words = nama.split(' ');
        let kode = '';
        words.forEach(word => {
            if (word.length > 0 && kode.length < 10) {
                kode += word.charAt(0).toUpperCase();
            }
        });
        kodeInput.value = kode;
    }
});

// Update preview konfigurasi
function updatePreview() {
    const durasi = parseInt(document.getElementById('estimasi_durasi_layanan').value) || 0;
    const kapasitas = parseInt(document.getElementById('kapasitas_harian').value) || 0;
    
    document.getElementById('previewDurasi').textContent = durasi;
    document.getElementById('previewKapasitas').textContent = kapasitas;
    
    // Hitung total waktu operasi
    const totalWaktu = (durasi * kapasitas) / 60; // dalam jam
    document.getElementById('previewWaktuTotal').textContent = totalWaktu.toFixed(1);
    
    // Hitung efektif dalam jam kerja (8 jam/hari)
    const jamKerjaEfektif = totalWaktu / 8;
    document.getElementById('previewJamKerja').textContent = jamKerjaEfektif.toFixed(1);
    
    // Update warna alert berdasarkan efisiensi
    const alert = document.getElementById('previewConfig');
    alert.className = 'alert ';
    if (jamKerjaEfektif <= 1) {
        alert.className += 'alert-success';
    } else if (jamKerjaEfektif <= 1.5) {
        alert.className += 'alert-info';
    } else if (jamKerjaEfektif <= 2) {
        alert.className += 'alert-warning';
    } else {
        alert.className += 'alert-danger';
    }
}

// Event listeners untuk update preview
document.getElementById('estimasi_durasi_layanan').addEventListener('input', updatePreview);
document.getElementById('kapasitas_harian').addEventListener('input', updatePreview);

// Validasi form
document.getElementById('layananForm').addEventListener('submit', function(e) {
    const nama = document.getElementById('nama_layanan').value.trim();
    const kode = document.getElementById('kode_layanan').value.trim();
    const durasi = parseInt(document.getElementById('estimasi_durasi_layanan').value);
    const kapasitas = parseInt(document.getElementById('kapasitas_harian').value);
    
    let errors = [];
    
    if (nama.length < 3) {
        errors.push('Nama layanan minimal 3 karakter');
    }
    
    if (kode.length < 2) {
        errors.push('Kode layanan minimal 2 karakter');
    }
    
    if (durasi < 1 || durasi > 999) {
        errors.push('Estimasi durasi harus antara 1-999 menit');
    }
    
    if (kapasitas < 1 || kapasitas > 999) {
        errors.push('Kapasitas harian harus antara 1-999 orang');
    }
    
    // Check efisiensi waktu
    const jamKerjaEfektif = (durasi * kapasitas) / 60 / 8;
    if (jamKerjaEfektif > 3) {
        if (!confirm('Konfigurasi ini memerlukan lebih dari 3 hari kerja untuk menyelesaikan semua antrian. Yakin ingin melanjutkan?')) {
            e.preventDefault();
            return;
        }
    }
    
    if (errors.length > 0) {
        e.preventDefault();
        alert('Error:\n- ' + errors.join('\n- '));
        return;
    }
    
    // Show loading state
    const submitBtn = this.querySelector('button[type="submit"]');
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Menyimpan...';
    submitBtn.disabled = true;
});

// Initialize preview
updatePreview();
</script>
@endpush