<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'CPSU Campus Navigator')</title>
    
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #f0f2f5;
            color: #1a2c3e;
        }
        
        /* Admin Container */
        .admin-container {
            display: flex;
            min-height: 100vh;
        }
        
        /* Sidebar */
        .admin-sidebar {
            width: 260px;
            background: #1a2c3e;
            color: white;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
            transition: all 0.3s;
            z-index: 100;
        }
        
        .sidebar-header {
            padding: 24px;
            text-align: center;
            border-bottom: 1px solid #2c3e50;
        }
        
        .sidebar-header h2 {
            font-size: 1.2rem;
        }
        
        .sidebar-header p {
            font-size: 0.75rem;
            opacity: 0.7;
            margin-top: 5px;
        }
        
        .sidebar-nav {
            padding: 20px 0;
        }
        
        .nav-item {
            padding: 12px 24px;
            display: flex;
            align-items: center;
            gap: 12px;
            cursor: pointer;
            transition: all 0.2s;
            color: #bdc3c7;
            text-decoration: none;
        }
        
        .nav-item:hover {
            background: #2c3e50;
            color: white;
        }
        
        .nav-item.active {
            background: #0057a3;
            color: white;
        }
        
        .nav-icon {
            font-size: 1.2rem;
            width: 28px;
        }
        
        /* Main Content */
        .admin-main {
            flex: 1;
            margin-left: 260px;
            padding: 24px;
        }
        
        /* Header Bar */
        .admin-header {
            background: white;
            padding: 20px 24px;
            border-radius: 12px;
            margin-bottom: 24px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .admin-header h1 {
            font-size: 1.5rem;
            color: #1a2c3e;
        }
        
        .user-info {
            display: flex;
            align-items: center;
            gap: 16px;
        }
        
        .user-name {
            font-size: 0.85rem;
            font-weight: 500;
            color: #1a2c3e;
        }
        
        .logout-btn {
            background: #e74c3c;
            color: white;
            padding: 8px 16px;
            border-radius: 8px;
            text-decoration: none;
            font-size: 0.8rem;
            border: none;
            cursor: pointer;
            transition: background 0.2s;
        }
        
        .logout-btn:hover {
            background: #c0392b;
        }
        
        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px;
            margin-bottom: 24px;
        }
        
        .stat-card {
            background: white;
            padding: 24px;
            border-radius: 16px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            transition: transform 0.2s, box-shadow 0.2s;
        }
        
        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        
        .stat-number {
            font-size: 2.5rem;
            font-weight: 700;
            color: #0057a3;
        }
        
        .stat-label {
            color: #5c6f87;
            margin-top: 8px;
            font-size: 0.85rem;
        }
        
        /* Cards */
        .card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            margin-bottom: 24px;
        }
        
        .card-header {
            padding: 20px 24px;
            border-bottom: 1px solid #e9edf2;
        }
        
        .card-header h2 {
            font-size: 1.1rem;
            font-weight: 600;
            color: #1a2c3e;
        }
        
        .card-body {
            padding: 20px 24px;
        }
        
        /* Tables */
        .data-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .data-table th,
        .data-table td {
            padding: 12px 16px;
            text-align: left;
            border-bottom: 1px solid #e9edf2;
        }
        
        .data-table th {
            background: #f8fafc;
            font-weight: 600;
            color: #1a2c3e;
        }
        
        /* Buttons */
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 20px;
            border-radius: 8px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s;
            border: none;
            font-size: 0.85rem;
        }
        
        .btn-primary {
            background: #0057a3;
            color: white;
        }
        
        .btn-primary:hover {
            background: #003d73;
        }
        
        /* Alerts */
        .alert {
            padding: 12px 16px;
            border-radius: 12px;
            margin-bottom: 20px;
        }
        
        .alert-success {
            background: #dcfce7;
            color: #16a34a;
            border-left: 4px solid #16a34a;
        }
        
        .alert-error {
            background: #fee2e2;
            color: #dc2626;
            border-left: 4px solid #dc2626;
        }
        
        /* Chart Container */
        .chart-container {
            background: white;
            border-radius: 16px;
            padding: 20px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            margin-top: 24px;
        }
        
        /* Mobile Responsive */
        @media (max-width: 768px) {
            .admin-sidebar {
                transform: translateX(-100%);
            }
            
            .admin-sidebar.open {
                transform: translateX(0);
            }
            
            .admin-main {
                margin-left: 0;
            }
            
            .menu-toggle {
                display: block;
            }
            
            .stats-grid {
                grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
                gap: 12px;
            }
            
            .stat-number {
                font-size: 1.8rem;
            }
        }
        
        @media (min-width: 769px) {
            .menu-toggle {
                display: none;
            }
        }
        
        .menu-toggle {
            background: #0057a3;
            border: none;
            color: white;
            font-size: 1.3rem;
            cursor: pointer;
            margin-right: 15px;
            padding: 5px 10px;
            border-radius: 8px;
        }
        
        .header-left {
            display: flex;
            align-items: center;
        }
        
        /* Footer */
        .footer {
            text-align: center;
            padding: 20px;
            color: #94a3b8;
            font-size: 0.75rem;
            border-top: 1px solid #e9edf2;
            margin-top: 24px;
        }
    </style>
    
    @stack('styles')
</head>
<body>
<div class="admin-container">
    <!-- Sidebar -->
    <div class="admin-sidebar" id="sidebar">
        <div class="sidebar-header">
            <h2>🏫 CPSU Admin</h2>
            <p>Campus Management System</p>
        </div>
        <div class="sidebar-nav">
            <a href="{{ route('admin.dashboard') }}" class="nav-item @if(request()->routeIs('admin.dashboard')) active @endif">
                <span class="nav-icon">📊</span>
                <span>Dashboard</span>
            </a>
            <a href="{{ route('admin.offices') }}" class="nav-item @if(request()->routeIs('admin.offices*')) active @endif">
                <span class="nav-icon">🏛️</span>
                <span>Office Management</span>
            </a>
            <a href="{{ route('admin.processes') }}" class="nav-item @if(request()->routeIs('admin.processes*')) active @endif">
                <span class="nav-icon">📋</span>
                <span>Process Management</span>
            </a>
            <a href="{{ route('admin.map-editor') }}" class="nav-item @if(request()->routeIs('admin.map-editor')) active @endif">
                <span class="nav-icon">🗺️</span>
                <span>Map Editor</span>
            </a>
        </div>
    </div>

    <!-- Main Content -->
    <div class="admin-main">
        <div class="admin-header">
            <div class="header-left">
                <button class="menu-toggle" onclick="toggleSidebar()">☰</button>
                <h1>@yield('header-title', 'Dashboard')</h1>
            </div>
            <div class="user-info">
                <span class="user-name">👋 {{ Auth::user()->full_name ?? 'Admin' }}</span>
                <form method="POST" action="{{ route('logout') }}" style="display: inline;">
                    @csrf
                    <button type="submit" class="logout-btn">Logout</button>
                </form>
            </div>
        </div>
        
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        
        @if(session('error'))
            <div class="alert alert-error">{{ session('error') }}</div>
        @endif
        
        @yield('content')
        
        <div class="footer">
            Central Philippines State University - Kabankalan City, Negros Occidental
        </div>
    </div>
</div>

<script>
    function toggleSidebar() {
        document.getElementById('sidebar').classList.toggle('open');
    }
    
    // Close sidebar when clicking outside on mobile
    document.addEventListener('click', function(event) {
        const sidebar = document.getElementById('sidebar');
        const isMobile = window.innerWidth <= 768;
        
        if (isMobile && sidebar.classList.contains('open')) {
            if (!sidebar.contains(event.target) && !event.target.closest('.menu-toggle')) {
                sidebar.classList.remove('open');
            }
        }
    });
</script>

@stack('scripts')
</body>
</html>