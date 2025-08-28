@extends('layouts.app')

@section('title', 'Kelola Admin')
@section('page-title', 'Kelola Admin')

@section('page-actions')
<a href="{{ route('admin.create') }}" class="btn btn-primary">
    <i class="fas fa-plus me-2"></i>Tambah Admin Baru
</a>
@endsection

@section('content')
<div class="card shadow">
    <div class="card-header">
        <h5 class="mb-0">
            <i class="fas fa-user-shield me-2"></i>Data Admin
        </h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Username</th>
                        <th>Nama Admin</th>
                        <th>Email</th>
                        <th>Dibuat</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($admins as $admin)
                    <tr>
                        <td>{{ $admin->id_admin }}</td>
                        <td>
                            <strong class="text-primary">{{ $admin->username }}</strong>
                        </td>
                        <td>{{ $admin->nama_admin }}</td>
                        <td>
                            @if($admin->email)
                                {{ $admin->email }}
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>{{ $admin->created_at->format('d/m/Y H:i') }}</td>
                        <td>
                            <div class="btn-group btn-group-sm" role="group">
                                <a href="{{ route('admin.show', $admin->id_admin) }}" class="btn btn-outline-info">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('admin.edit', $admin->id_admin) }}" class="btn btn-outline-primary">
                                    <i class="fas fa-edit"></i>
                                </a>
                                @if($admin->id_admin != Auth::guard('admin')->id())
                                <form action="{{ route('admin.destroy', $admin->id_admin) }}" method="POST" style="display: inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger" 
                                            onclick="return confirm('Yakin ingin menghapus admin ini?')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-4">
                            <div class="text-muted">
                                <i class="fas fa-user-shield fa-3x mb-3"></i>
                                <p>Belum ada data admin</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($admins->hasPages())
            <div class="d-flex justify-content-center mt-4">
                {{ $admins->links() }}
            </div>
        @endif
    </div>
</div>
@endsection