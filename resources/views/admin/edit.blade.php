@extends('layouts.app')

@section('title', 'Edit Admin')
@section('page-title', 'Edit Admin - ' . $admin->nama_admin)

@section('page-actions')
<div class="btn-group">
    <a href="{{ route('admin.show', $admin->id_admin) }}" class="btn btn-info">
        <i class="fas fa-eye me-2"></i>Detail
    </a>
    <a href="{{ route('admin.index') }}" class="btn btn-secondary">
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
                    <i class="fas fa-user-edit me-2"></i>Form Edit Admin
                </h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.update', $admin->id_admin) }}" id="editAdminForm">
                    @csrf
                    @method('PUT')
                    
                    <!-- Alert Info -->
                    @if($isOwnAccount)
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Info:</strong> Anda sedang mengedit akun Anda sendiri.
                    </div>
                    @endif

                    <!-- Info Admin -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h6 class="text-primary border-bottom pb-2">
                                <i class="fas fa-info-circle me-2"></i>Informasi Admin
                            </h6>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="username" class="form-label">Username <span class="text-danger">*</span></label>
                            <input type="text" 
                                   class="form-control @error('username') is-invalid @enderror" 
                                   id="username" 
                                   name="username" 
                                   value="{{ old('username', $admin->username) }}" 
                                   required
                                   maxlength="50"
                                   placeholder="Masukkan username">
                            @error('username')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="nama_admin" class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                            <input type="text" 
                                   class="form-control @error('nama_admin') is-invalid @enderror" 
                                   id="nama_admin" 
                                   name="nama_admin" 
                                   value="{{ old('nama_admin', $admin->nama_admin) }}" 
                                   required
                                   maxlength="100"
                                   placeholder="Masukkan nama lengkap">
                            @error('nama_admin')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" 
                                   class="form-control @error('email') is-invalid @enderror" 
                                   id="email" 
                                   name="email" 
                                   value="{{ old('email', $admin->email) }}" 
                                   maxlength="100"
                                   placeholder="contoh@email.com">
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Opsional</div>
                        </div>
                    </div>

                    <!-- Ubah Password -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h6 class="text-primary border-bottom pb-2">
                                <i class="fas fa-key me-2"></i>Ubah Password (Opsional)
                            </h6>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="password" class="form-label">Password Baru</label>
                            <div class="input-group">
                                <input type="password" 
                                       class="form-control @error('password') is-invalid @enderror" 
                                       id="password" 
                                       name="password" 
                                       minlength="6"
                                       placeholder="Kosongkan jika tidak ingin mengubah">
                                <button type="button" class="btn btn-outline-secondary" onclick="togglePassword('password')">
                                    <i class="fas fa-eye" id="passwordIcon"></i>
                                </button>
                            </div>
                            @error('password')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Minimal 6 karakter. Kosongkan jika tidak ingin mengubah.</div>
                        </div>
                        <div class="col-md-6">
                            <label for="password_confirmation" class="form-label">Konfirmasi Password Baru</label>
                            <div class="input-group">
                                <input type="password" 
                                       class="form-control" 
                                       id="password_confirmation" 
                                       name="password_confirmation" 
                                       placeholder="Konfirmasi password baru">
                                <button type="button" class="btn btn-outline-secondary" onclick="togglePassword('password_confirmation')">
                                    <i class="fas fa-eye" id="password_confirmationIcon"></i>
                                </button>
                            </div>
                            <div class="form-text">Harus sama dengan password baru</div>
                        </div>
                    </div>

                    <!-- Statistik Admin -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h6 class="text-info border-bottom pb-2">
                                <i class="fas fa-chart-bar me-2"></i>Statistik Admin
                            </h6>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-3 col-6">
                            <div class="card bg-light">
                                <div class="card-body text-center p-2">
                                    <div class="h5 text-primary mb-1">{{ $admin->layanan()->count() }}</div>
                                    <small>Total Layanan</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 col-6">
                            <div class="card bg-light">
                                <div class="card-body text-center p-2">
                                    <div class="h5 text-success mb-1">{{ $admin->layanan()->where('aktif', true)->count() }}</div>
                                    <small>Layanan Aktif</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 col-6">
                            <div class="card bg-light">
                                <div class="card-body text-center p-2">
                                    <div class="h5 text-warning mb-1">{{ $admin->antrian()->count() }}</div>
                                    <small>Total Antrian</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 col-6">
                            <div class="card bg-light">
                                <div class="card-body text-center p-2">
                                    <div class="h5 text-info mb-1">{{ $admin->antrian()->whereDate('created_at', today())->count() }}</div>
                                    <small>Antrian Hari Ini</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tombol Submit -->
                    <div class="row">
                        <div class="col-12">
                            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                <a href="{{ route('admin.index') }}" class="btn btn-secondary me-md-2">
                                    <i class="fas fa-times me-2"></i>Batal
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>Update Admin
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
                <h6 class="text-muted"><i class="fas fa-history me-2"></i>Informasi Akun:</h6>
                <small class="text-muted">
                    <strong>Bergabung:</strong> {{ $admin->created_at->format('d/m/Y H:i') }}<br>
                    @if($admin->created_at != $admin->updated_at)
                        <strong>Terakhir diubah:</strong> {{ $admin->updated_at->format('d/m/Y H:i') }}<br>
                    @endif
                    <strong>ID Admin:</strong> {{ $admin->id_admin }}
                </small>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
function togglePassword(fieldId) {
    const passwordInput = document.getElementById(fieldId);
    const icon = document.getElementById(fieldId + 'Icon');
    
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        passwordInput.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}

// Validasi konfirmasi password
document.getElementById('editAdminForm').addEventListener('submit', function(e) {
    const password = document.getElementById('password').value;
    const passwordConfirm = document.getElementById('password_confirmation').value;
    
    // Jika password diisi, harus dikonfirmasi
    if (password && password !== passwordConfirm) {
        e.preventDefault();
        alert('Konfirmasi password tidak sesuai!');
        document.getElementById('password_confirmation').focus();
        return false;
    }
    
    // Show loading state
    const submitBtn = this.querySelector('button[type="submit"]');
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Memperbarui...';
    submitBtn.disabled = true;
});

// Real-time password match validation
document.getElementById('password_confirmation').addEventListener('input', function() {
    const password = document.getElementById('password').value;
    const passwordConfirm = this.value;
    
    if (password && passwordConfirm) {
        if (password === passwordConfirm) {
            this.classList.remove('is-invalid');
            this.classList.add('is-valid');
        } else {
            this.classList.remove('is-valid');
            this.classList.add('is-invalid');
        }
    } else {
        this.classList.remove('is-valid', 'is-invalid');
    }
});
</script>
@endpush