<!DOCTYPE html>
<html lang="id" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin') - IoTrack</title>
    <link rel="icon" type="image/png" href="{{ asset('images/IoTrackLogo.png') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --sidebar-width: 280px;
            --sidebar-collapsed: 80px;
            --topbar-height: 70px;
            --radius-sm: 8px;
            --radius-md: 12px;
            --radius-lg: 16px;
            --radius-xl: 24px;
            --shadow-sm: 0 1px 2px rgba(0,0,0,0.05);
            --shadow-md: 0 4px 6px -1px rgba(0,0,0,0.1);
            --shadow-lg: 0 10px 15px -3px rgba(0,0,0,0.1);
            --shadow-xl: 0 25px 50px -12px rgba(0,0,0,0.25);
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        [data-theme="light"] {
            --bg-body: #f0f5ff;
            --bg-card: #ffffff;
            --bg-sidebar: linear-gradient(180deg, #1e293b 0%, #0f172a 100%);
            --bg-hover: #f8fafc;
            --bg-active: rgba(99, 102, 241, 0.1);
            --text-primary: #0f172a;
            --text-secondary: #475569;
            --text-muted: #94a3b8;
            --text-sidebar: #94a3b8;
            --text-sidebar-active: #ffffff;
            --border: #e2e8f0;
            --border-light: #f1f5f9;
            --primary: #6366f1;
            --primary-dark: #4f46e5;
            --primary-light: #a5b4fc;
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
            --info: #06b6d4;
            --gradient-primary: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
            --gradient-success: linear-gradient(135deg, #10b981 0%, #059669 100%);
            --gradient-warning: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            --gradient-danger: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            --gradient-info: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%);
        }

        [data-theme="dark"] {
            --bg-body: #0f172a;
            --bg-card: #1e293b;
            --bg-sidebar: linear-gradient(180deg, #0f172a 0%, #020617 100%);
            --bg-hover: #334155;
            --bg-active: rgba(99, 102, 241, 0.2);
            --text-primary: #f1f5f9;
            --text-secondary: #cbd5e1;
            --text-muted: #94a3b8;
            --text-sidebar: #94a3b8;
            --text-sidebar-active: #ffffff;
            --border: #334155;
            --border-light: #1e293b;
            --primary: #818cf8;
            --success: #34d399;
            --warning: #fbbf24;
            --danger: #f87171;
            --info: #22d3ee;
            --gradient-primary: linear-gradient(135deg, #818cf8 0%, #a78bfa 100%);
            --gradient-success: linear-gradient(135deg, #34d399 0%, #10b981 100%);
            --gradient-warning: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%);
            --gradient-danger: linear-gradient(135deg, #f87171 0%, #ef4444 100%);
            --gradient-info: linear-gradient(135deg, #22d3ee 0%, #06b6d4 100%);
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Inter', system-ui, sans-serif;
            background: var(--bg-body);
            color: var(--text-primary);
            min-height: 100vh;
            transition: var(--transition);
        }

        /* Scrollbar */
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: var(--text-muted); border-radius: 3px; }
        ::-webkit-scrollbar-thumb:hover { background: var(--text-secondary); }

        /* ========== SIDEBAR ========== */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            width: var(--sidebar-width);
            height: 100vh;
            background: var(--bg-sidebar);
            z-index: 1050;
            transition: var(--transition);
            display: flex;
            flex-direction: column;
            box-shadow: var(--shadow-xl);
        }

        .sidebar.collapsed { width: var(--sidebar-collapsed); }

        .sidebar-header {
            height: 80px;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem 1.5rem;
            border-bottom: 1px solid rgba(255,255,255,0.08);
        }

        .sidebar-brand {
            display: flex;
            align-items: center;
            gap: 1rem;
            text-decoration: none;
            overflow: hidden;
        }

        .sidebar-logo {
            width: 48px;
            height: 48px;
            min-width: 48px;
            object-fit: contain;
            filter: drop-shadow(0 4px 8px rgba(0,0,0,0.3));
        }

        .sidebar-brand-text {
            font-size: 1.375rem;
            font-weight: 800;
            background: linear-gradient(135deg, #fff 0%, #a5b4fc 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            white-space: nowrap;
            transition: var(--transition);
        }

        .sidebar.collapsed .sidebar-brand-text { opacity: 0; width: 0; }

        /* Sidebar Navigation */
        .sidebar-nav {
            flex: 1;
            padding: 1.25rem 0;
            overflow-y: auto;
            overflow-x: hidden;
        }

        .nav-section { margin-bottom: 1.75rem; }

        .nav-section-title {
            padding: 0.5rem 1.5rem;
            font-size: 0.65rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            color: var(--text-sidebar);
            opacity: 0.6;
            transition: var(--transition);
        }

        .sidebar.collapsed .nav-section-title { opacity: 0; }

        .nav-item { padding: 0.25rem 1rem; }

        .nav-link {
            display: flex;
            align-items: center;
            gap: 0.875rem;
            padding: 0.875rem 1rem;
            color: var(--text-sidebar);
            text-decoration: none;
            border-radius: var(--radius-md);
            transition: var(--transition);
            position: relative;
            overflow: hidden;
        }

        .nav-link::before {
            content: '';
            position: absolute;
            left: 0;
            top: 50%;
            transform: translateY(-50%);
            width: 3px;
            height: 0;
            background: var(--gradient-primary);
            border-radius: 0 3px 3px 0;
            transition: var(--transition);
        }

        .nav-link:hover {
            background: rgba(255,255,255,0.05);
            color: var(--text-sidebar-active);
        }

        .nav-link.active {
            background: rgba(99, 102, 241, 0.15);
            color: var(--text-sidebar-active);
        }

        .nav-link.active::before { height: 60%; }

        .nav-link i {
            font-size: 1.25rem;
            min-width: 24px;
            text-align: center;
            transition: var(--transition);
        }

        .nav-link span { font-weight: 500; transition: var(--transition); }

        .sidebar.collapsed .nav-link span { opacity: 0; width: 0; }
        .sidebar.collapsed .nav-link { justify-content: center; padding: 0.875rem; }

        .nav-badge {
            margin-left: auto;
            padding: 0.2rem 0.5rem;
            font-size: 0.65rem;
            font-weight: 700;
            background: var(--gradient-primary);
            color: #fff;
            border-radius: 2rem;
        }

        .sidebar.collapsed .nav-badge { display: none; }

        /* Sidebar Footer */
        .sidebar-footer {
            padding: 1.25rem;
            border-top: 1px solid rgba(255,255,255,0.08);
        }

        .sidebar-user {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.75rem;
            background: rgba(255,255,255,0.05);
            border-radius: var(--radius-md);
            margin-bottom: 0.75rem;
        }

        .sidebar-user-avatar {
            width: 40px;
            height: 40px;
            background: var(--gradient-primary);
            border-radius: var(--radius-md);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            color: #fff;
            font-size: 0.9rem;
        }

        .sidebar-user-info { overflow: hidden; }
        .sidebar-user-name { font-weight: 600; color: #fff; font-size: 0.875rem; }
        .sidebar-user-role { font-size: 0.7rem; color: var(--text-sidebar); }

        .sidebar.collapsed .sidebar-user-info { display: none; }

        .logout-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            width: 100%;
            padding: 0.75rem;
            background: rgba(239, 68, 68, 0.1);
            color: #fca5a5;
            border: 1px solid rgba(239, 68, 68, 0.2);
            border-radius: var(--radius-md);
            font-size: 0.875rem;
            font-weight: 500;
            cursor: pointer;
            transition: var(--transition);
        }

        .logout-btn:hover {
            background: rgba(239, 68, 68, 0.2);
            border-color: rgba(239, 68, 68, 0.3);
            color: #fff;
        }

        .sidebar.collapsed .logout-btn span { display: none; }

        /* ========== MAIN WRAPPER ========== */
        .main-wrapper {
            margin-left: var(--sidebar-width);
            min-height: 100vh;
            transition: var(--transition);
        }

        .main-wrapper.expanded { margin-left: var(--sidebar-collapsed); }

        /* ========== TOPBAR ========== */
        .topbar {
            position: sticky;
            top: 0;
            height: var(--topbar-height);
            background: var(--bg-card);
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 2rem;
            z-index: 1000;
            backdrop-filter: blur(10px);
            transition: var(--transition);
        }

        .topbar-left { display: flex; align-items: center; gap: 1.25rem; }

        .toggle-btn {
            width: 42px;
            height: 42px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--bg-hover);
            border: 1px solid var(--border);
            border-radius: var(--radius-md);
            color: var(--text-secondary);
            cursor: pointer;
            transition: var(--transition);
        }

        .toggle-btn:hover {
            background: var(--primary);
            border-color: var(--primary);
            color: #fff;
        }

        .page-info h1 {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--text-primary);
            margin: 0;
        }

        [data-theme="dark"] .page-info h1 {
            color: #f1f5f9;
        }

        .page-info .breadcrumb {
            font-size: 0.75rem;
            color: var(--text-muted);
            margin: 0;
        }

        [data-theme="dark"] .page-info .breadcrumb {
            color: #94a3b8;
        }

        .topbar-right { display: flex; align-items: center; gap: 0.75rem; }

        .topbar-btn {
            width: 42px;
            height: 42px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--bg-hover);
            border: 1px solid var(--border);
            border-radius: var(--radius-md);
            color: var(--text-secondary);
            cursor: pointer;
            transition: var(--transition);
            position: relative;
        }

        .topbar-btn:hover {
            background: var(--bg-active);
            border-color: var(--primary);
            color: var(--primary);
        }

        .topbar-btn .badge-dot {
            position: absolute;
            top: 8px;
            right: 8px;
            width: 8px;
            height: 8px;
            background: var(--danger);
            border-radius: 50%;
            border: 2px solid var(--bg-card);
        }

        .theme-toggle .bi-moon-fill { display: block; }
        .theme-toggle .bi-sun-fill { display: none; }
        [data-theme="dark"] .theme-toggle .bi-moon-fill { display: none; }
        [data-theme="dark"] .theme-toggle .bi-sun-fill { display: block; }

        .topbar-date {
            padding: 0.5rem 1rem;
            background: var(--bg-hover);
            border: 1px solid var(--border);
            border-radius: var(--radius-md);
            font-size: 0.8rem;
            font-weight: 500;
            color: var(--text-secondary);
        }

        /* ========== CONTENT ========== */
        .content { padding: 2rem; }

        /* ========== CARDS ========== */
        .card {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-sm);
            transition: var(--transition);
        }

        .card:hover { box-shadow: var(--shadow-md); }

        .card-header {
            background: transparent;
            border-bottom: 1px solid var(--border);
            padding: 1.25rem 1.5rem;
        }

        .card-body { padding: 1.5rem; }
        .card-footer { background: transparent; border-top: 1px solid var(--border); padding: 1rem 1.5rem; }

        /* ========== TABLES ========== */
        .table { color: var(--text-primary); margin: 0; }

        .table thead th {
            background: var(--bg-hover);
            border: none;
            font-weight: 600;
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: var(--text-muted);
            padding: 1rem 1.25rem;
        }

        .table tbody td {
            padding: 1rem 1.25rem;
            vertical-align: middle;
            border-bottom: 1px solid var(--border-light);
        }

        .table tbody tr:hover { background: var(--bg-hover); }
        .table tbody tr:last-child td { border-bottom: none; }

        /* ========== BUTTONS ========== */
        .btn {
            font-weight: 500;
            border-radius: var(--radius-md);
            padding: 0.625rem 1.25rem;
            transition: var(--transition);
        }

        .btn-primary {
            background: var(--gradient-primary);
            border: none;
            color: #fff;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(99, 102, 241, 0.4);
        }

        .btn-success { background: var(--gradient-success); border: none; }
        .btn-warning { background: var(--gradient-warning); border: none; color: #fff; }
        .btn-danger { background: var(--gradient-danger); border: none; }
        .btn-info { background: var(--gradient-info); border: none; }

        .btn-outline-primary {
            border: 1px solid var(--primary);
            color: var(--primary);
            background: transparent;
        }

        .btn-outline-primary:hover {
            background: var(--primary);
            color: #fff;
        }

        .btn-ghost {
            background: var(--bg-hover);
            border: 1px solid var(--border);
            color: var(--text-secondary);
        }

        .btn-ghost:hover {
            background: var(--bg-active);
            border-color: var(--primary);
            color: var(--primary);
        }

        /* ========== FORMS ========== */
        .form-control, .form-select {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: var(--radius-md);
            color: var(--text-primary);
            padding: 0.75rem 1rem;
            transition: var(--transition);
        }

        .form-control:focus, .form-select:focus {
            background: var(--bg-card);
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.15);
            color: var(--text-primary);
        }

        .form-control::placeholder { color: var(--text-muted); }
        [data-theme="dark"] .form-control::placeholder { color: var(--text-muted); }
        [data-theme="dark"] .form-select option { background: var(--bg-card); color: var(--text-primary); }

        .form-label { font-weight: 500; color: var(--text-primary); margin-bottom: 0.5rem; }

        /* Dark mode specific fixes */
        [data-theme="dark"] .btn-close { filter: invert(1) grayscale(100%) brightness(200%); }
        [data-theme="dark"] .modal-header { color: var(--text-primary); }
        [data-theme="dark"] .list-group-item { background: transparent; color: var(--text-primary); }
        [data-theme="dark"] code { background: var(--bg-hover); color: var(--primary); }
        [data-theme="dark"] .card-title { color: #f1f5f9; }
        [data-theme="dark"] .card-header h6 { color: #f1f5f9; }
        [data-theme="dark"] .stat-value { color: #f1f5f9; }
        [data-theme="dark"] .activity-name { color: #f1f5f9; }
        [data-theme="dark"] .menu-title { color: #f1f5f9; }
        [data-theme="dark"] .quick-action-text h6 { color: #f1f5f9; }

        /* ========== BADGES ========== */
        .badge {
            font-weight: 600;
            padding: 0.4rem 0.75rem;
            border-radius: 2rem;
            font-size: 0.7rem;
        }

        .badge-primary { background: rgba(99, 102, 241, 0.15); color: var(--primary); }
        .badge-success { background: rgba(16, 185, 129, 0.15); color: var(--success); }
        .badge-warning { background: rgba(245, 158, 11, 0.15); color: var(--warning); }
        .badge-danger { background: rgba(239, 68, 68, 0.15); color: var(--danger); }
        .badge-info { background: rgba(6, 182, 212, 0.15); color: var(--info); }

        /* ========== ALERTS ========== */
        .alert {
            border: none;
            border-radius: var(--radius-md);
            padding: 1rem 1.25rem;
            font-weight: 500;
        }

        .alert-success { background: rgba(16, 185, 129, 0.15); color: var(--success); }
        .alert-danger { background: rgba(239, 68, 68, 0.15); color: var(--danger); }

        /* ========== MODALS ========== */
        .modal-content {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
        }

        .modal-header { border-bottom: 1px solid var(--border); padding: 1.25rem 1.5rem; }
        .modal-body { padding: 1.5rem; }
        .modal-footer { border-top: 1px solid var(--border); padding: 1rem 1.5rem; }

        /* ========== RESPONSIVE ========== */
        @media (max-width: 991.98px) {
            .sidebar { transform: translateX(-100%); }
            .sidebar.mobile-open { transform: translateX(0); }
            .sidebar.collapsed { width: var(--sidebar-width); }
            .main-wrapper, .main-wrapper.expanded { margin-left: 0; }
            .sidebar-overlay {
                display: none;
                position: fixed;
                inset: 0;
                background: rgba(0,0,0,0.5);
                backdrop-filter: blur(4px);
                z-index: 1040;
            }
            .sidebar-overlay.show { display: block; }
            .topbar { padding: 0 1rem; }
            .content { padding: 1rem; }
            .page-info { display: none; }
        }

        @media (min-width: 992px) { .sidebar-overlay { display: none !important; } }

        @yield('styles')
    </style>
</head>
<body>

    {{-- Sidebar --}}
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <a href="{{ route('admin.menu') }}" class="sidebar-brand">
                <img src="{{ asset('images/IoTrackLogo.png') }}" alt="IoTrack" class="sidebar-logo">
                <span class="sidebar-brand-text">IoTrack</span>
            </a>
        </div>

        <nav class="sidebar-nav">
            <div class="nav-section">
                <div class="nav-section-title">Overview</div>
                <div class="nav-item">
                    <a href="{{ route('admin.menu') }}" class="nav-link {{ request()->routeIs('admin.menu') ? 'active' : '' }}">
                        <i class="bi bi-grid-1x2-fill"></i>
                        <span>Menu Utama</span>
                    </a>
                </div>
                <div class="nav-item">
                    <a href="{{ route('admin.dashboard') }}" class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                        <i class="bi bi-speedometer2"></i>
                        <span>Dashboard</span>
                    </a>
                </div>
            </div>

            <div class="nav-section">
                <div class="nav-section-title">Manajemen</div>
                <div class="nav-item">
                    <a href="{{ route('items.index') }}" class="nav-link {{ request()->routeIs('items.*') ? 'active' : '' }}">
                        <i class="bi bi-box-seam-fill"></i>
                        <span>Inventaris</span>
                    </a>
                </div>
                <div class="nav-item">
                    <a href="{{ route('admin.borrowings.index') }}" class="nav-link {{ request()->routeIs('admin.borrowings.*') ? 'active' : '' }}">
                        <i class="bi bi-arrow-left-right"></i>
                        <span>Peminjaman</span>
                        @php $activeBorrowCount = \App\Models\Borrowing::where('status', 'dipinjam')->count(); @endphp
                        @if($activeBorrowCount > 0)
                            <span class="nav-badge">{{ $activeBorrowCount }}</span>
                        @endif
                    </a>
                </div>
                <div class="nav-item">
                    <a href="{{ route('admin.visits.index') }}" class="nav-link {{ request()->routeIs('admin.visits.index') ? 'active' : '' }}">
                        <i class="bi bi-people-fill"></i>
                        <span>Kunjungan</span>
                    </a>
                </div>
            </div>

            <div class="nav-section">
                <div class="nav-section-title">Laporan</div>
                <div class="nav-item">
                    <a href="{{ route('admin.visits.export') }}" class="nav-link">
                        <i class="bi bi-file-earmark-excel-fill"></i>
                        <span>Ekspor Excel</span>
                    </a>
                </div>
            </div>
        </nav>

        <div class="sidebar-footer">
            <div class="sidebar-user">
                <div class="sidebar-user-avatar">A</div>
                <div class="sidebar-user-info">
                    <div class="sidebar-user-name">Administrator</div>
                    <div class="sidebar-user-role">Super Admin</div>
                </div>
            </div>
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="logout-btn">
                    <i class="bi bi-box-arrow-left"></i>
                    <span>Keluar</span>
                </button>
            </form>
        </div>
    </aside>

    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    {{-- Main Content --}}
    <main class="main-wrapper" id="mainWrapper">
        <header class="topbar">
            <div class="topbar-left">
                <button type="button" class="toggle-btn" id="toggleSidebar">
                    <i class="bi bi-list fs-5"></i>
                </button>
                <div class="page-info">
                    <h1>@yield('page-title', 'Dashboard')</h1>
                    <p class="breadcrumb">Admin / @yield('page-title', 'Dashboard')</p>
                </div>
            </div>
            <div class="topbar-right">
                <div class="topbar-date d-none d-md-flex">
                    <i class="bi bi-calendar3 me-2"></i>
                    {{ now()->isoFormat('dddd, D MMM Y') }}
                </div>
                <button type="button" class="topbar-btn theme-toggle" id="themeToggle" title="Toggle Theme">
                    <i class="bi bi-moon-fill"></i>
                    <i class="bi bi-sun-fill"></i>
                </button>
            </div>
        </header>

        <div class="content">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
                    <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
            
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @yield('content')
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const sidebar = document.getElementById('sidebar');
        const mainWrapper = document.getElementById('mainWrapper');
        const toggleBtn = document.getElementById('toggleSidebar');
        const overlay = document.getElementById('sidebarOverlay');
        const themeToggle = document.getElementById('themeToggle');
        const html = document.documentElement;
        
        const isCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
        if (isCollapsed && window.innerWidth >= 992) {
            sidebar.classList.add('collapsed');
            mainWrapper.classList.add('expanded');
        }

        const savedTheme = localStorage.getItem('theme') || 'light';
        html.setAttribute('data-theme', savedTheme);

        toggleBtn.addEventListener('click', function() {
            if (window.innerWidth >= 992) {
                sidebar.classList.toggle('collapsed');
                mainWrapper.classList.toggle('expanded');
                localStorage.setItem('sidebarCollapsed', sidebar.classList.contains('collapsed'));
            } else {
                sidebar.classList.toggle('mobile-open');
                overlay.classList.toggle('show');
                document.body.style.overflow = sidebar.classList.contains('mobile-open') ? 'hidden' : '';
            }
        });

        themeToggle.addEventListener('click', function() {
            const newTheme = html.getAttribute('data-theme') === 'light' ? 'dark' : 'light';
            html.setAttribute('data-theme', newTheme);
            localStorage.setItem('theme', newTheme);
        });

        overlay.addEventListener('click', function() {
            sidebar.classList.remove('mobile-open');
            overlay.classList.remove('show');
            document.body.style.overflow = '';
        });

        document.querySelectorAll('.nav-link').forEach(link => {
            link.addEventListener('click', function() {
                if (window.innerWidth < 992) {
                    sidebar.classList.remove('mobile-open');
                    overlay.classList.remove('show');
                    document.body.style.overflow = '';
                }
            });
        });

        window.addEventListener('resize', function() {
            if (window.innerWidth >= 992) {
                sidebar.classList.remove('mobile-open');
                overlay.classList.remove('show');
                document.body.style.overflow = '';
            }
        });
    });
    </script>
    @yield('scripts')
</body>
</html>