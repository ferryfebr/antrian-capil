<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') - Sistem Antrian Disdukcapil</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Custom CSS -->
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .sidebar {
            min-height: 100vh;
            background: linear-gradient(135deg, #2c3e50, #34495e);
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
        }
        
        .sidebar .nav-link {
            color: #ecf0f1;
            padding: 12px 20px;
            border-radius: 8px;
            margin: 2px 10px;
            transition: all 0.3s ease;
        }
        
        .sidebar .nav-link:hover, .sidebar .nav-link.active {
            background-color: rgba(255,255,255,0.1);
            color: #fff;
            transform: translateX(5px);
        }
        
        .sidebar .nav-link i {
            width: 20px;
            text-align: center;
            margin-right: 10px;
        }
        
        .main-content {
            padding: 20px;
        }
        
        .page-header {
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 20px;
            margin-bottom: 20px;
            border-left: 4px solid #007bff;
        }
        
        .card {
            border: none;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            border-radius: 10px;
        }
        
        .btn {
            border-radius: 6px;
        }
        
        .navbar-brand {
            font-weight: bold;
            color: #2c3e50;
        }
        
        .user-menu {
            background: rgba(255,255,255,0.1);
            border-radius: 20px;
            padding: 8px 15px;
            color: #ecf0f1;
        }
        
        .breadcrumb {
            background: none;
            padding: 0;
            margin: 0;
        }
        
        .breadcrumb-item a {
            color: #6c757d;
            text-decoration: none;
        }
        
        .breadcrumb-item.active {
            color: #495057;
        }
        
        @media (max-width: 768px) {
            .sidebar {
                margin-left: -250px;
                transition: margin-left 0.3s ease;
            }
            
            .sidebar.show {
                margin-left: 0;
            }
            
            .main-content {
                margin-left: 0 !important;
            }
        }
    </style>
    
    @stack('styles')
</head>
<body>
    <div class="d-flex">
        <!-- Sidebar -->
        <nav class="sidebar" style="width: 250px;">
            <div class="p-3">
                <h4 class="text-white text-center mb-4">
                    <i class="fas fa-building me-2"></i>
                    DISDUKCAPIL
                </h4>
                
                <!-- User Info -->
                <div class="user-menu text-center mb-4">
                    <div class="mb-2">
                        <i class="fas fa-user-circle fa-2x"></i>
                    </div>
                    <div class="small">
                        <strong>{{ Auth::guard('admin')->user()->nama_admin }}</strong><br>
                        <span class="text-muted">{{ Auth::guard('admin')->user()->username }}</span>
                    </div>
                </div>
                
                <!-- Navigation -->
                <ul class="nav nav-pills flex-column">
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
                            <i class="fas fa-tachometer-alt"></i>Dashboard
                        </a>
                    </li>
                    
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('antrian.*') ? 'active' : '' }}" href="{{ route('antrian.index') }}">
                            <i class="fas fa-list-ol"></i>Kelola Antrian
                        </a>
                    </li>
                    
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('layanan.*') ? 'active' : '' }}" href="{{ route('layanan.index') }}">
                            <i class="fas fa-cogs"></i>Kelola Layanan
                        </a>
                    </li>
                    
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('loket.*') ? 'active' : '' }}" href="{{ route('loket.index') }}">
                            <i class="fas fa-desktop"></i>Kelola Loket
                        </a>
                    </li>
                    
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('pengunjung.*') ? 'active' : '' }}" href="{{ route('pengunjung.index') }}">
                            <i class="fas fa-users"></i>Data Pengunjung
                        </a>
                    </li>
                    
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('admin.*') ? 'active' : '' }}" href="{{ route('admin.index') }}">
                            <i class="fas fa-user-shield"></i>Kelola Admin
                        </a>
                    </li>
                    
                    <hr class="my-3" style="border-color: rgba(255,255,255,0.2);">
                    
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('public.queue') }}" target="_blank">
                            <i class="fas fa-tv"></i>Display Antrian
                        </a>
                    </li>
                    
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('public.index') }}" target="_blank">
                            <i class="fas fa-globe"></i>Website Publik
                        </a>
                    </li>
                    
                    <hr class="my-3" style="border-color: rgba(255,255,255,0.2);">
                    
                    <li class="nav-item">
                        <form action="{{ route('logout') }}" method="POST" style="display: inline;">
                            @csrf
                            <button type="submit" class="nav-link w-100 text-start border-0 bg-transparent" 
                                    onclick="return confirm('Yakin ingin logout?')">
                                <i class="fas fa-sign-out-alt"></i>Logout
                            </button>
                        </form>
                    </li>
                </ul>
            </div>
        </nav>
        
        <!-- Main Content -->
        <div class="flex-grow-1" style="margin-left: 0;">
            <!-- Top Navbar (for mobile) -->
            <nav class="navbar navbar-expand-lg navbar-light bg-white d-lg-none shadow-sm">
                <div class="container-fluid">
                    <button class="btn btn-outline-primary" type="button" id="sidebarToggle">
                        <i class="fas fa-bars"></i>
                    </button>
                    <span class="navbar-brand">@yield('page-title', 'Dashboard')</span>
                </div>
            </nav>
            
            <!-- Page Header -->
            <div class="page-header">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h3 class="mb-1">@yield('page-title', 'Dashboard')</h3>
                        
                        <!-- Breadcrumb -->
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                                @if(!request()->routeIs('dashboard'))
                                    <li class="breadcrumb-item active">@yield('page-title')</li>
                                @endif
                            </ol>
                        </nav>
                    </div>
                    
                    <!-- Page Actions -->
                    <div>
                        @yield('page-actions')
                    </div>
                </div>
            </div>
            
            <!-- Alerts -->
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i>
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
            
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
            
            @if(session('warning'))
                <div class="alert alert-warning alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    {{ session('warning') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
            
            @if(session('info'))
                <div class="alert alert-info alert-dismissible fade show" role="alert">
                    <i class="fas fa-info-circle me-2"></i>
                    {{ session('info') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
            
            <!-- Main Content -->
            <div class="main-content">
                @yield('content')
            </div>
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JS -->
    <script>
        // Mobile sidebar toggle
        document.addEventListener('DOMContentLoaded', function() {
            const sidebarToggle = document.getElementById('sidebarToggle');
            const sidebar = document.querySelector('.sidebar');
            
            if (sidebarToggle) {
                sidebarToggle.addEventListener('click', function() {
                    sidebar.classList.toggle('show');
                });
            }
            
            // Close sidebar when clicking outside on mobile
            document.addEventListener('click', function(e) {
                if (window.innerWidth <= 768) {
                    if (!sidebar.contains(e.target) && !sidebarToggle.contains(e.target)) {
                        sidebar.classList.remove('show');
                    }
                }
            });
        });
        
        // Auto hide alerts after 5 seconds
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                if (alert.classList.contains('alert-success') || 
                    alert.classList.contains('alert-info')) {
                    const bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                }
            });
        }, 5000);
        
        // Confirmation for delete buttons
        document.addEventListener('DOMContentLoaded', function() {
            const deleteForms = document.querySelectorAll('form[action*="destroy"]');
            deleteForms.forEach(function(form) {
                form.addEventListener('submit', function(e) {
                    if (!confirm('Yakin ingin menghapus data ini? Tindakan ini tidak dapat dibatalkan.')) {
                        e.preventDefault();
                    }
                });
            });
        });
    </script>
    
    @stack('scripts')
</body>
</html>