@extends('layouts.app')

@section('title', 'Buat Antrian Baru')
@section('page-title', 'Buat Antrian Baru')

@section('page-actions')
<a href="{{ route('antrian.index') }}" class="btn btn-secondary">
    <i class="fas fa-arrow-left me-2"></i>Kembali
</a>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">
                    <i class="fas fa-ticket-alt me-2"></i>Form Buat Antrian Baru
                </h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('antrian.store') }}" id="antrianForm">
                    @csrf
                    
                    <!-- Pre-filled alerts -->
                    @if($prefilledNik || $prefilledLayanan)
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Info:</strong> Beberapa data sudah diisi otomatis berdasarkan parameter.
                    </div>
                    @endif

                    <!-- Data Pengunjung -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h6 class="text-primary border-bottom pb-2">
                                <i class="fas fa-user me-2"></i>Data Pengunjung
                            </h6>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="nik" class="form-label">NIK <span class="text-danger">*</span></label>
                            <input type="text" 
                                   class="form-control @error('nik') is-invalid @enderror" 
                                   id="nik" 
                                   name="nik" 
                                   value="{{ old('nik', $prefilledNik) }}" 
                                   required
                                   maxlength="16"
                                   placeholder="16 digit NIK"
                                   oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 16); checkExistingVisitor()">
                            @error('nik')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">NIK harus 16 digit angka</div>
                        </div>
                        <div class="col-md-6">
                            <label for="nama_pengunjung" class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                            <input type="text" 
                                   class="form-control @error('nama_pengunjung') is-invalid @enderror" 
                                   id="nama_pengunjung" 
                                   name="nama_pengunjung" 
                                   value="{{ old('nama_pengunjung') }}" 
                                   required
                                   maxlength="100"
                                   placeholder="Masukkan nama lengkap">
                            @error('nama_pengunjung')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="no_hp" class="form-label">No. HP</label>
                            <input type="text" 
                                   class="form-control @error('no_hp') is-invalid @enderror" 
                                   id="no_hp" 
                                   name="no_hp" 
                                   value="{{ old('no_hp') }}" 
                                   maxlength="15"
                                   placeholder="081234567890"
                                   oninput="this.value = this.value.replace(/[^0-9+]/g, '').slice(0, 15)">
                            @error('no_hp')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Opsional - untuk notifikasi SMS</div>
                        </div>
                    </div>

                    <!-- Info pengunjung yang sudah ada -->
                    <div id="existingVisitorInfo" class="alert alert-success" style="display: none;">
                        <h6><i class="fas fa-user-check me-2"></i>Pengunjung Ditemukan:</h6>
                        <div id="visitorDetails"></div>
                        <small class="text-muted">Data akan digunakan dan diperbarui jika ada perubahan.</small>
                    </div>

                    <!-- Pilih Layanan -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h6 class="text-primary border-bottom pb-2">
                                <i class="fas fa-cogs me-2"></i>Pilih Layanan
                            </h6>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-12">
                            <label for="id_layanan" class="form-label">Layanan <span class="text-danger">*</span></label>
                            <select class="form-select @error('id_layanan') is-invalid @enderror" 
                                    id="id_layanan" 
                                    name="id_layanan" 
                                    required>
                                <option value="">-- Pilih Layanan --</option>
                                @foreach($layanans as $layanan)
                                    <option value="{{ $layanan->id_layanan }}" 
                                            data-kode="{{ $layanan->kode_layanan }}"
                                            data-durasi="{{ $layanan->estimasi_durasi_layanan }}"
                                            data-kapasitas="{{ $layanan->kapasitas_harian }}"
                                            {{ old('id_layanan', $prefilledLayanan) == $layanan->id_layanan ? 'selected' : '' }}>
                                        {{ $layanan->nama_layanan }} ({{ $layanan->kode_layanan }})
                                    </option>
                                @endforeach
                            </select>
                            @error('id_layanan')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Info Layanan -->
                    <div id="layananInfo" class="alert alert-info" style="display: none;">
                        <div class="row">
                            <div class="col-md-6">
                                <h6><i class="fas fa-info-circle me-2"></i>Info Layanan:</h6>
                                <ul class="mb-0">
                                    <li>Kode: <strong id="layananKode">-</strong></li>
                                    <li>Estimasi durasi: <strong id="layananDurasi">-</strong> menit</li>
                                    <li>Kapasitas harian: <strong id="layananKapasitas">-</strong> orang</li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <h6><i class="fas fa-chart-bar me-2"></i>Status Hari Ini:</h6>
                                <ul class="mb-0">
                                    <li>Antrian hari ini: <strong id="antrianHariIni">0</strong></li>
                                    <li>Sisa kapasitas: <strong id="sisaKapasitas">-</strong></li>
                                    <li>Estimasi nomor: <strong id="estimasiNomor">-</strong></li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- Preview Antrian -->
                    <div id="antrianPreview" class="alert alert-success" style="display: none;">
                        <h6><i class="fas fa-eye me-2"></i>Preview Antrian:</h6>
                        <div class="row">
                            <div class="col-md-6">
                                <ul class="mb-0">
                                    <li>Nomor antrian: <strong id="previewNomor">-</strong></li>
                                    <li>Pengunjung: <strong id="previewNama">-</strong></li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <ul class="mb-0">
                                    <li>Layanan: <strong id="previewLayanan">-</strong></li>
                                    <li>Estimasi waktu: <strong id="previewWaktu">-</strong></li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- Warning kapasitas penuh -->
                    <div id="capacityWarning" class="alert alert-warning" style="display: none;">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Peringatan:</strong> Kapasitas layanan hari ini sudah penuh atau hampir penuh!
                    </div>

                    <!-- Tombol Submit -->
                    <div class="row">
                        <div class="col-12">
                            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                <a href="{{ route('antrian.index') }}" class="btn btn-secondary me-md-2">
                                    <i class="fas fa-times me-2"></i>Batal
                                </a>
                                <button type="submit" class="btn btn-primary" id="submitBtn">
                                    <i class="fas fa-ticket-alt me-2"></i>Buat Antrian
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
                <div class="row">
                    <div class="col-md-6">
                        <ul class="mb-0 small">
                            <li><strong>NIK:</strong> Harus 16 digit angka yang valid</li>
                            <li><strong>Nama:</strong> Sesuai dengan dokumen identitas</li>
                            <li><strong>No. HP:</strong> Opsional, untuk notifikasi status antrian</li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <ul class="mb-0 small">
                            <li><strong>Layanan:</strong> Pilih sesuai kebutuhan pengunjung</li>
                            <li><strong>Kapasitas:</strong> Sistem akan cek ketersediaan otomatis</li>
                            <li><strong>Estimasi:</strong> Waktu perkiraan berdasarkan antrian sebelumnya</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
let layananData = @json($layanans->keyBy('id_layanan'));

// Check existing visitor by NIK
function checkExistingVisitor() {
    const nik = document.getElementById('nik').value;
    
    if (nik.length === 16) {
        // Simulate API call to check existing visitor
        fetch(`{{ route('api.visitor.check') }}?nik=${nik}`)
            .then(response => response.json())
            .then(data => {
                if (data.exists) {
                    showExistingVisitor(data.visitor);
                } else {
                    hideExistingVisitor();
                }
            })
            .catch(error => {
                console.log('Visitor check failed:', error);
                hideExistingVisitor();
            });
    } else {
        hideExistingVisitor();
    }
}

// Show existing visitor info
function showExistingVisitor(visitor) {
    document.getElementById('existingVisitorInfo').style.display = 'block';
    document.getElementById('visitorDetails').innerHTML = `
        <strong>Nama:</strong> ${visitor.nama_pengunjung}<br>
        <strong>No. HP:</strong> ${visitor.no_hp || 'Tidak ada'}<br>
        <strong>Terdaftar:</strong> ${visitor.waktu_daftar}
    `;
    
    // Auto fill form
    document.getElementById('nama_pengunjung').value = visitor.nama_pengunjung;
    document.getElementById('no_hp').value = visitor.no_hp || '';
}

// Hide existing visitor info
function hideExistingVisitor() {
    document.getElementById('existingVisitorInfo').style.display = 'none';
}

// Update layanan info when selection changes
document.getElementById('id_layanan').addEventListener('change', function() {
    const layananId = this.value;
    
    if (layananId) {
        const layanan = layananData[layananId];
        if (layanan) {
            showLayananInfo(layanan);
            updateAntrianPreview();
        }
    } else {
        hideLayananInfo();
        hideAntrianPreview();
    }
});

// Show layanan information
function showLayananInfo(layanan) {
    document.getElementById('layananKode').textContent = layanan.kode_layanan;
    document.getElementById('layananDurasi').textContent = layanan.estimasi_durasi_layanan;
    document.getElementById('layananKapasitas').textContent = layanan.kapasitas_harian;
    
    // Get today's queue count for this service
    getTodayQueueCount(layanan.id_layanan);
    
    document.getElementById('layananInfo').style.display = 'block';
}

// Hide layanan info
function hideLayananInfo() {
    document.getElementById('layananInfo').style.display = 'none';
}

// Get today's queue count
function getTodayQueueCount(layananId) {
    fetch(`{{ route('api.layanan.today-count') }}?layanan_id=${layananId}`)
        .then(response => response.json())
        .then(data => {
            const antrianHariIni = data.count || 0;
            const layanan = layananData[layananId];
            const sisaKapasitas = layanan.kapasitas_harian - antrianHariIni;
            const estimasiNomor = `${layanan.kode_layanan}-${String(antrianHariIni + 1).padStart(3, '0')}`;
            
            document.getElementById('antrianHariIni').textContent = antrianHariIni;
            document.getElementById('sisaKapasitas').textContent = sisaKapasitas;
            document.getElementById('estimasiNomor').textContent = estimasiNomor;
            
            // Show warning if capacity is full or almost full
            if (sisaKapasitas <= 0) {
                document.getElementById('capacityWarning').style.display = 'block';
                document.getElementById('capacityWarning').innerHTML = `
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>Peringatan:</strong> Kapasitas layanan hari ini sudah penuh! (${antrianHariIni}/${layanan.kapasitas_harian})
                `;
                document.getElementById('submitBtn').disabled = true;
            } else if (sisaKapasitas <= 5) {
                document.getElementById('capacityWarning').style.display = 'block';
                document.getElementById('capacityWarning').innerHTML = `
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>Peringatan:</strong> Sisa kapasitas layanan tinggal ${sisaKapasitas} orang.
                `;
                document.getElementById('submitBtn').disabled = false;
            } else {
                document.getElementById('capacityWarning').style.display = 'none';
                document.getElementById('submitBtn').disabled = false;
            }
        })
        .catch(error => {
            console.log('Queue count check failed:', error);
        });
}

// Update antrian preview
function updateAntrianPreview() {
    const nama = document.getElementById('nama_pengunjung').value;
    const layananId = document.getElementById('id_layanan').value;
    
    if (nama && layananId) {
        const layanan = layananData[layananId];
        const estimasiNomor = document.getElementById('estimasiNomor').textContent;
        
        // Calculate estimated time
        const antrianSebelum = parseInt(document.getElementById('antrianHariIni').textContent) || 0;
        const estimasiMenit = antrianSebelum * layanan.estimasi_durasi_layanan;
        const estimasiWaktu = new Date(Date.now() + estimasiMenit * 60000);
        
        document.getElementById('previewNomor').textContent = estimasiNomor;
        document.getElementById('previewNama').textContent = nama;
        document.getElementById('previewLayanan').textContent = layanan.nama_layanan;
        document.getElementById('previewWaktu').textContent = estimasiWaktu.toLocaleTimeString('id-ID', {
            hour: '2-digit',
            minute: '2-digit'
        });
        
        document.getElementById('antrianPreview').style.display = 'block';
    } else {
        hideAntrianPreview();
    }
}

// Hide antrian preview
function hideAntrianPreview() {
    document.getElementById('antrianPreview').style.display = 'none';
}

// Update preview when nama changes
document.getElementById('nama_pengunjung').addEventListener('input', updateAntrianPreview);

// Form validation and submission
document.getElementById('antrianForm').addEventListener('submit', function(e) {
    const nik = document.getElementById('nik').value;
    const nama = document.getElementById('nama_pengunjung').value;
    const layanan = document.getElementById('id_layanan').value;
    
    let errors = [];
    
    // Validate NIK
    if (!nik || nik.length !== 16) {
        errors.push('NIK harus 16 digit angka');
    }
    
    // Validate nama
    if (!nama || nama.trim().length < 3) {
        errors.push('Nama lengkap minimal 3 karakter');
    }
    
    // Validate layanan
    if (!layanan) {
        errors.push('Pilih layanan yang diinginkan');
    }
    
    // Check capacity
    const sisaKapasitas = parseInt(document.getElementById('sisaKapasitas').textContent) || 0;
    if (sisaKapasitas <= 0) {
        errors.push('Kapasitas layanan hari ini sudah penuh');
    }
    
    if (errors.length > 0) {
        e.preventDefault();
        alert('Error:\n- ' + errors.join('\n- '));
        return false;
    }
    
    // Final confirmation
    const layananNama = layananData[layanan].nama_layanan;
    const estimasiNomor = document.getElementById('estimasiNomor').textContent;
    
    if (!confirm(`Konfirmasi pembuatan antrian:\n\nNama: ${nama}\nLayanan: ${layananNama}\nNomor Antrian: ${estimasiNomor}\n\nLanjutkan?`)) {
        e.preventDefault();
        return false;
    }
    
    // Show loading state
    const submitBtn = document.getElementById('submitBtn');
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Membuat Antrian...';
    submitBtn.disabled = true;
});

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    // Check if layanan is pre-selected
    const layananSelect = document.getElementById('id_layanan');
    if (layananSelect.value) {
        layananSelect.dispatchEvent(new Event('change'));
    }
    
    // Check if NIK is pre-filled
    const nikInput = document.getElementById('nik');
    if (nikInput.value && nikInput.value.length === 16) {
        checkExistingVisitor();
    }
});

// Format NIK input with spaces for better readability
document.getElementById('nik').addEventListener('input', function() {
    let value = this.value.replace(/\D/g, ''); // Remove non-digits
    value = value.slice(0, 16); // Limit to 16 digits
    
    // Add spaces for readability (but keep the actual value as numbers only)
    let formatted = value.replace(/(\d{2})(\d{2})(\d{2})(\d{6})(\d{4})/, '$1 $2 $3 $4 $5');
    
    // Update display value temporarily
    const cursorPos = this.selectionStart;
    this.value = value; // Keep actual value as numbers only
    
    // Show formatted version in a data attribute for display purposes
    this.setAttribute('data-formatted', formatted);
});

// Add API route for checking visitors (you'll need to add this route)
// This is a placeholder - you'll need to implement the actual API endpoint
window.checkVisitorAPI = function(nik) {
    // This would typically call your Laravel API
    return fetch(`/api/visitors/check?nik=${nik}`)
        .then(response => response.json())
        .catch(() => ({ exists: false }));
};
</script>
@endpush