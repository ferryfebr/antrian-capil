@extends('layouts.app')

@section('title', 'Edit Loket')
@section('page-title', 'Edit Loket - ' . $loket->nama_loket)

@section('page-actions')
<div class="btn-group">
    <a href="{{ route('loket.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left me-2"></i>Kembali
    </a>
</div>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">
                    <i class="fas fa-edit me-2"></i>Form Edit Loket
                </h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('loket.update', $loket->id_loket) }}" id="loketForm">
                    @csrf
                    @method('PUT')
                    
                    <!-- Info Loket -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h6 class="text-primary border-bottom pb-2">
                                <i class="fas fa-info-circle me-2"></i>Informasi Loket
                            </h6>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="nama_loket" class="form-label">Nama Loket <span class="text-danger">*</span></label>
                            <input type="text" 
                                   class="form-control @error('nama_loket') is-invalid @enderror" 
                                   id="nama_loket" 
                                   name="nama_loket" 
                                   value="{{ old('nama_loket', $loket->nama_loket) }}" 
                                   required
                                   maxlength="50"
                                   placeholder="Contoh: Loket 1">
                            @error('nama_loket')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="status_loket" class="form-label">Status Loket <span class="text-danger">*</span></label>
                            <select class="form-select @error('status_loket') is-invalid @enderror" 
                                    id="status_loket" 
                                    name="status_loket" 
                                    required>
                                <option value="">-- Pilih Status --</option>
                                <option value="aktif" {{ old('status_loket', $loket->status_loket) == 'aktif' ? 'selected' : '' }}>Aktif</option>
                                <option value="tidak_aktif" {{ old('status_loket', $loket->status_loket) == 'tidak_aktif' ? 'selected' : '' }}>Tidak Aktif</option>
                            </select>
                            @error('status_loket')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Layanan Assignment -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h6 class="text-primary border-bottom pb-2">
                                <i class="fas fa-cogs me-2"></i>Assignment Layanan
                            </h6>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="id_layanan" class="form-label">Layanan</label>
                        <select class="form-select @error('id_layanan') is-invalid @enderror" 
                                id="id_layanan" 
                                name="id_layanan">
                            <option value="">-- Pilih Layanan (Opsional) --</option>
                            @foreach($layanans as $layanan)
                                <option value="{{ $layanan->id_layanan }}" 
                                        data-kode="{{ $layanan->kode_layanan }}"
                                        data-durasi="{{ $layanan->estimasi_durasi_layanan }}"
                                        data-kapasitas="{{ $layanan->kapasitas_harian }}"
                                        {{ old('id_layanan', $loket->id_layanan) == $layanan->id_layanan ? 'selected' : '' }}>
                                    {{ $layanan->nama_layanan }} ({{ $layanan->kode_layanan }})
                                </option>
                            @endforeach
                        </select>
                        @error('id_layanan')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">Kosongkan jika loket dapat melayani semua jenis layanan</div>
                    </div>

                    <!-- Info Layanan yang dipilih -->
                    <div id="layanan-info" class="alert alert-info" style="display: none;">
                        <h6><i class="fas fa-info-circle me-2"></i>Informasi Layanan:</h6>
                        <div class="row">
                            <div class="col-md-6">
                                <ul class="mb-0">
                                    <li>Kode: <strong id="kode-layanan">-</strong></li>
                                    <li>Estimasi durasi: <strong id="durasi-layanan">-</strong> menit</li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <ul class="mb-0">
                                    <li>Kapasitas harian: <strong id="kapasitas-layanan">-</strong> orang</li>
                                    <li>Status: <strong id="status-layanan">-</strong></li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- Deskripsi -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h6 class="text-primary border-bottom pb-2">
                                <i class="fas fa-file-alt me-2"></i>Deskripsi & Keterangan
                            </h6>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="deskripsi_loket" class="form-label">Deskripsi Loket</label>
                        <textarea class="form-control @error('deskripsi_loket') is-invalid @enderror" 
                                  id="deskripsi_loket" 
                                  name="deskripsi_loket" 
                                  rows="4"
                                  placeholder="Deskripsi atau keterangan tambahan tentang loket ini">{{ old('deskripsi_loket', $loket->deskripsi_loket) }}</textarea>
                        @error('deskripsi_loket')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">Opsional - Informasi tambahan seperti lokasi, jam khusus, dll</div>
                    </div>

                    <!-- Statistik Loket -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h6 class="text-info border-bottom pb-2">
                                <i class="fas fa-chart-bar me-2"></i>Statistik Loket
                            </h6>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-3 col-6">
                            <div class="card bg-light">
                                <div class="card-body text-center p-2">
                                    <div class="h5 text-primary mb-1">{{ $loket->id_loket }}</div>
                                    <small>ID Loket</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 col-6">
                            <div class="card bg-light">
                                <div class="card-body text-center p-2">
                                    <div class="h5 text-success mb-1">
                                        {{ $loket->created_at->format('Y') }}
                                    </div>
                                    <small>Tahun Dibuat</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 col-6">
                            <div class="card bg-light">
                                <div class="card-body text-center p-2">
                                    <div class="h5 text-info mb-1">
                                        {{ $loket->status_loket == 'aktif' ? 'YA' : 'TIDAK' }}
                                    </div>
                                    <small>Status Aktif</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 col-6">
                            <div class="card bg-light">
                                <div class="card-body text-center p-2">
                                    <div class="h5 text-warning mb-1">
                                        {{ $loket->layanan ? 'KHUSUS' : 'UMUM' }}
                                    </div>
                                    <small>Jenis Layanan</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Preview Changes -->
                    <div id="changes-preview" class="alert alert-warning" style="display: none;">
                        <h6><i class="fas fa-exclamation-triangle me-2"></i>Preview Perubahan:</h6>
                        <div id="changes-list"></div>
                    </div>

                    <!-- Tombol Submit -->
                    <div class="row">
                        <div class="col-12">
                            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                <a href="{{ route('loket.index') }}" class="btn btn-secondary me-md-2">
                                    <i class="fas fa-times me-2"></i>Batal
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>Update Loket
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Log Perubahan -->
        <div class="card mt-4">
            <div class="card-body">
                <h6 class="text-muted"><i class="fas fa-history me-2"></i>Riwayat Loket:</h6>
                <div class="row">
                    <div class="col-md-6">
                        <small class="text-muted">
                            <strong>Dibuat:</strong> {{ $loket->created_at->format('d/m/Y H:i') }}
                            <br><strong>ID Loket:</strong> {{ $loket->id_loket }}
                        </small>
                    </div>
                    <div class="col-md-6">
                        <small class="text-muted">
                            @if($loket->created_at != $loket->updated_at)
                                <strong>Terakhir diubah:</strong> {{ $loket->updated_at->format('d/m/Y H:i') }}<br>
                            @endif
                            <strong>Status saat ini:</strong> 
                            <span class="badge bg-{{ $loket->status_loket == 'aktif' ? 'success' : 'danger' }}">
                                {{ ucfirst(str_replace('_', ' ', $loket->status_loket)) }}
                            </span>
                        </small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tips -->
        <div class="card mt-4">
            <div class="card-body">
                <h6 class="text-success"><i class="fas fa-lightbulb me-2"></i>Tips Edit Loket:</h6>
                <div class="row">
                    <div class="col-md-6">
                        <ul class="mb-0 small">
                            <li><strong>Status Aktif:</strong> Loket dapat menerima dan melayani antrian</li>
                            <li><strong>Status Tidak Aktif:</strong> Loket tidak beroperasi, antrian tidak akan diarahkan</li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <ul class="mb-0 small">
                            <li><strong>Layanan Khusus:</strong> Hanya melayani satu jenis layanan tertentu</li>
                            <li><strong>Layanan Umum:</strong> Dapat melayani semua jenis layanan</li>
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
const originalValues = {
    nama_loket: '{{ $loket->nama_loket }}',
    status_loket: '{{ $loket->status_loket }}',
    id_layanan: '{{ $loket->id_layanan }}',
    deskripsi_loket: '{{ $loket->deskripsi_loket }}'
};

// Show layanan info when selected
document.getElementById('id_layanan').addEventListener('change', function() {
    const selectedOption = this.options[this.selectedIndex];
    const infoDiv = document.getElementById('layanan-info');
    
    if (selectedOption.value) {
        const kode = selectedOption.getAttribute('data-kode');
        const durasi = selectedOption.getAttribute('data-durasi');
        const kapasitas = selectedOption.getAttribute('data-kapasitas');
        
        document.getElementById('kode-layanan').textContent = kode;
        document.getElementById('durasi-layanan').textContent = durasi;
        document.getElementById('kapasitas-layanan').textContent = kapasitas;
        document.getElementById('status-layanan').textContent = 'Aktif'; // Assume active since it's in the list
        
        infoDiv.style.display = 'block';
    } else {
        infoDiv.style.display = 'none';
    }
    
    checkForChanges();
});

// Track changes in real-time
document.querySelectorAll('input, select, textarea').forEach(input => {
    input.addEventListener('input', checkForChanges);
    input.addEventListener('change', checkForChanges);
});

function checkForChanges() {
    const currentValues = {
        nama_loket: document.getElementById('nama_loket').value,
        status_loket: document.getElementById('status_loket').value,
        id_layanan: document.getElementById('id_layanan').value,
        deskripsi_loket: document.getElementById('deskripsi_loket').value
    };
    
    const changes = [];
    const previewDiv = document.getElementById('changes-preview');
    const changesList = document.getElementById('changes-list');
    
    // Check for changes
    for (let field in currentValues) {
        if (currentValues[field] != originalValues[field]) {
            let fieldName, oldValue, newValue;
            
            switch(field) {
                case 'nama_loket':
                    fieldName = 'Nama Loket';
                    break;
                case 'status_loket':
                    fieldName = 'Status';
                    break;
                case 'id_layanan':
                    fieldName = 'Layanan';
                    oldValue = getLayananName(originalValues[field]);
                    newValue = getLayananName(currentValues[field]);
                    break;
                case 'deskripsi_loket':
                    fieldName = 'Deskripsi';
                    break;
            }
            
            if (field !== 'id_layanan') {
                oldValue = originalValues[field] || 'Kosong';
                newValue = currentValues[field] || 'Kosong';
            }
            
            changes.push(`<li><strong>${fieldName}:</strong> "${oldValue}" → "${newValue}"</li>`);
        }
    }
    
    if (changes.length > 0) {
        changesList.innerHTML = '<ul class="mb-0">' + changes.join('') + '</ul>';
        previewDiv.style.display = 'block';
    } else {
        previewDiv.style.display = 'none';
    }
}

function getLayananName(layananId) {
    if (!layananId) return 'Semua Layanan';
    
    const option = document.querySelector(`#id_layanan option[value="${layananId}"]`);
    return option ? option.textContent : 'Unknown';
}

// Form validation
document.getElementById('loketForm').addEventListener('submit', function(e) {
    const namaLoket = document.getElementById('nama_loket').value.trim();
    const statusLoket = document.getElementById('status_loket').value;
    
    let errors = [];
    
    if (namaLoket.length < 3) {
        errors.push('Nama loket minimal 3 karakter');
    }
    
    if (!statusLoket) {
        errors.push('Status loket wajib dipilih');
    }
    
    if (errors.length > 0) {
        e.preventDefault();
        alert('Error:\n- ' + errors.join('\n- '));
        return false;
    }
    
    // Show confirmation if there are changes
    const changesExist = document.getElementById('changes-preview').style.display !== 'none';
    if (changesExist) {
        if (!confirm('Anda telah melakukan perubahan. Yakin ingin menyimpan?')) {
            e.preventDefault();
            return false;
        }
    }
    
    // Show loading state
    const submitBtn = this.querySelector('button[type="submit"]');
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Memperbarui...';
    submitBtn.disabled = true;
});

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    // Show layanan info if already selected
    const layananSelect = document.getElementById('id_layanan');
    if (layananSelect.value) {
        layananSelect.dispatchEvent(new Event('change'));
    }
    
    // Set initial change detection
    checkForChanges();
});

// Auto-generate suggestions
document.getElementById('nama_loket').addEventListener('input', function() {
    const value = this.value.toLowerCase();
    
    // Auto-suggest status based on name
    if (value.includes('tutup') || value.includes('maintenance') || value.includes('rusak')) {
        if (document.getElementById('status_loket').value === '') {
            document.getElementById('status_loket').value = 'tidak_aktif';
            checkForChanges();
        }
    }
});

// Warn about status changes
document.getElementById('status_loket').addEventListener('change', function() {
    const newStatus = this.value;
    const oldStatus = '{{ $loket->status_loket }}';
    
    if (oldStatus === 'aktif' && newStatus === 'tidak_aktif') {
        if (!confirm('Menonaktifkan loket akan menghentikan penerimaan antrian baru. Lanjutkan?')) {
            this.value = oldStatus;
            checkForChanges();
        }
    }
});
</script>
@endpush