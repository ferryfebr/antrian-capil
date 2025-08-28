@extends('layouts.app')

@section('title', 'Tambah Loket')
@section('page-title', 'Tambah Loket Baru')

@section('page-actions')
<a href="{{ route('loket.index') }}" class="btn btn-secondary">
    <i class="fas fa-arrow-left me-2"></i>Kembali
</a>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">
                    <i class="fas fa-desktop me-2"></i>Form Tambah Loket
                </h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('loket.store') }}">
                    @csrf
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="nama_loket" class="form-label">Nama Loket <span class="text-danger">*</span></label>
                            <input type="text" 
                                   class="form-control @error('nama_loket') is-invalid @enderror" 
                                   id="nama_loket" 
                                   name="nama_loket" 
                                   value="{{ old('nama_loket') }}" 
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
                                <option value="aktif" {{ old('status_loket') == 'aktif' ? 'selected' : '' }}>Aktif</option>
                                <option value="tidak_aktif" {{ old('status_loket') == 'tidak_aktif' ? 'selected' : '' }}>Tidak Aktif</option>
                            </select>
                            @error('status_loket')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
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
                                        {{ old('id_layanan') == $layanan->id_layanan ? 'selected' : '' }}>
                                    {{ $layanan->nama_layanan }} ({{ $layanan->kode_layanan }})
                                </option>
                            @endforeach
                        </select>
                        @error('id_layanan')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">Layanan yang akan dilayani di loket ini (opsional)</div>
                    </div>

                    <div class="mb-3">
                        <label for="deskripsi_loket" class="form-label">Deskripsi Loket</label>
                        <textarea class="form-control @error('deskripsi_loket') is-invalid @enderror" 
                                  id="deskripsi_loket" 
                                  name="deskripsi_loket" 
                                  rows="3"
                                  placeholder="Deskripsi atau keterangan tambahan tentang loket ini">{{ old('deskripsi_loket') }}</textarea>
                        @error('deskripsi_loket')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">Opsional - Informasi tambahan tentang loket</div>
                    </div>

                    <!-- Info Layanan yang dipilih -->
                    <div id="layanan-info" class="alert alert-info" style="display: none;">
                        <h6><i class="fas fa-info-circle me-2"></i>Informasi Layanan:</h6>
                        <ul class="mb-0">
                            <li>Kode layanan: <strong id="kode-layanan">-</strong></li>
                            <li>Estimasi durasi: <strong id="durasi-layanan">-</strong> menit</li>
                        </ul>
                    </div>

                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <a href="{{ route('loket.index') }}" class="btn btn-secondary me-md-2">
                            <i class="fas fa-times me-2"></i>Batal
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Simpan Loket
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Petunjuk -->
        <div class="card mt-4">
            <div class="card-body">
                <h6 class="text-success"><i class="fas fa-lightbulb me-2"></i>Petunjuk:</h6>
                <ul class="mb-0 small">
                    <li><strong>Nama Loket:</strong> Berikan nama yang mudah diidentifikasi seperti "Loket 1", "Loket A", dll</li>
                    <li><strong>Status:</strong> Aktif = loket dapat melayani antrian, Tidak Aktif = loket tidak beroperasi</li>
                    <li><strong>Layanan:</strong> Opsional, jika tidak dipilih maka loket bisa melayani semua jenis layanan</li>
                    <li><strong>Deskripsi:</strong> Informasi tambahan seperti lokasi, jam operasional khusus, dll</li>
                </ul>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
// Show layanan info when selected
document.getElementById('id_layanan').addEventListener('change', function() {
    const selectedOption = this.options[this.selectedIndex];
    const infoDiv = document.getElementById('layanan-info');
    
    if (selectedOption.value) {
        const kode = selectedOption.getAttribute('data-kode');
        const durasi = selectedOption.getAttribute('data-durasi');
        
        document.getElementById('kode-layanan').textContent = kode;
        document.getElementById('durasi-layanan').textContent = durasi;
        
        infoDiv.style.display = 'block';
    } else {
        infoDiv.style.display = 'none';
    }
});

// Auto generate nama loket
document.addEventListener('DOMContentLoaded', function() {
    // Get next loket number (simple implementation)
    fetch('/api/next-loket-number')
        .then(response => response.json())
        .then(data => {
            if (data.next_number && !document.getElementById('nama_loket').value) {
                document.getElementById('nama_loket').placeholder = `Contoh: Loket ${data.next_number}`;
            }
        })
        .catch(() => {
            // Silently fail, keep default placeholder
        });
});
</script>
@endpush