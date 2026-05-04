<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover, maximum-scale=1.0, user-scalable=yes">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <title>@yield('title', 'JacobCollections')</title>
    
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <style>
        * {
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background: #F3F3F3;
            margin: 0;
            padding: 0;
            overflow-x: hidden;
        }
        
        /* Layout utama - FIX SCROLL TIDAK KEPOTONG */
        .app-wrapper {
            display: flex;
            min-height: 100vh;
            width: 100%;
        }
        
        /* ==================== SIDEBAR STYLES ==================== */
        .sidebar-logo {
            font-size: 1.3rem;
            font-weight: 700;
            color: #4A8692;
            margin-bottom: 0.5rem;
        }
        
        .sidebar-user {
            color: #2196b6;
            font-size: 0.9rem;
            margin-bottom: 2rem;
        }
        
        .sidebar-link {
            display: flex;
            align-items: center;
            gap: 0.8rem;
            padding: 0.8rem 1.2rem;
            color: #787878;
            text-decoration: none;
            border-radius: 0.5rem;
            margin-bottom: 0.3rem;
            transition: 0.2s;
            width: 100%;
            background: transparent;
            border: none;
            cursor: pointer;
        }
        
        .sidebar-link:hover,
        .sidebar-link.active {
            background: #E6FAFD;
            color: #2196b6;
        }
        
        .profile-card {
            background: #F8FAFC;
            border-radius: 0.75rem;
            padding: 0.75rem;
            margin: 0.5rem 0;
            transition: all 0.2s;
        }
        
        .profile-card:hover {
            background: #F1F5F9;
        }
        
        /* ==================== SIDEBAR DESKTOP ==================== */
        .sidebar-desktop {
            width: 260px;
            background: white;
            box-shadow: 2px 0 10px rgba(0,0,0,0.05);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: 1.5rem 1rem;
            position: sticky;
            top: 0;
            height: 100vh;
            overflow-y: auto;
            flex-shrink: 0;
        }
        
        /* Custom scrollbar sidebar */
        .sidebar-desktop::-webkit-scrollbar {
            width: 4px;
        }
        
        .sidebar-desktop::-webkit-scrollbar-track {
            background: #f1f1f1;
        }
        
        .sidebar-desktop::-webkit-scrollbar-thumb {
            background: #c1c1c1;
            border-radius: 4px;
        }
        
        /* MAIN CONTENT - SCROLL LANCAR SAMPAI AKHIR */
        .main-content {
            flex: 1;
            min-width: 0;
            background: #F3F3F3;
        }
        
        .content-padding {
            padding: 1rem;
            max-width: 100%;
        }
        
        /* Mobile menu toggle */
        .mobile-menu-toggle {
            display: none;
            position: fixed;
            top: 1rem;
            left: 1rem;
            z-index: 100;
            background: #2196b6;
            color: white;
            padding: 0.75rem;
            border-radius: 0.5rem;
            border: none;
            cursor: pointer;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.5);
            z-index: 85;
        }
        
        .overlay.active {
            display: block;
        }
        
        /* Footer profile section di sidebar */
        .sidebar-footer {
            margin-top: auto;
            padding-top: 1rem;
            border-top: 1px solid #e5e7eb;
        }
        
        /* ==================== RESPONSIVE ==================== */
        @media (max-width: 768px) {
            .mobile-menu-toggle {
                display: block;
            }
            
            .sidebar-desktop {
                position: fixed;
                top: 0;
                left: 0;
                bottom: 0;
                transform: translateX(-100%);
                z-index: 95;
                height: 100vh;
                transition: transform 0.3s ease;
            }
            
            .sidebar-desktop.open {
                transform: translateX(0);
            }
            
            .main-content {
                margin-left: 0 !important;
                width: 100% !important;
                padding-top: 0.5rem;
            }
            
            .content-padding {
                padding: 0.75rem;
            }
            
            h1 {
                font-size: 1.25rem !important;
            }
            
            h2 {
                font-size: 1.125rem !important;
            }
            
            .stats-grid {
                grid-template-columns: 1fr !important;
            }
            
            .table-wrapper {
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
            }
            
            .table-wrapper table {
                min-width: 600px;
            }
            
            .card-grid {
                grid-template-columns: 1fr !important;
            }
        }
        
        @media (min-width: 769px) and (max-width: 1024px) {
            .sidebar-desktop {
                width: 240px;
            }
            
            .stats-grid {
                grid-template-columns: repeat(2, 1fr) !important;
            }
            
            .content-padding {
                padding: 1.25rem;
            }
        }
        
        @media (min-width: 1025px) {
            .content-padding {
                padding: 1.5rem;
            }
        }
        
        /* Touch friendly */
        @media (hover: none) and (pointer: coarse) {
            button, .sidebar-link, a {
                min-height: 44px;
            }
            
            input, select, textarea {
                font-size: 16px !important;
            }
        }
        
        /* Utility classes */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1rem;
            margin-bottom: 1.5rem;
        }
        
        .card-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 1rem;
        }
        
        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1rem;
        }
        
        @media (max-width: 640px) {
            .form-grid {
                grid-template-columns: 1fr;
            }
        }
        
        .table-wrapper {
            overflow-x: auto;
            width: 100%;
        }
        
        .table-wrapper::after {
            content: "← swipe →";
            display: none;
            text-align: center;
            font-size: 0.7rem;
            color: #9ca3af;
            padding: 0.5rem;
            background: #f9fafb;
        }
        
        @media (max-width: 768px) {
            .table-wrapper::after {
                display: block;
            }
        }
        
        .loading {
            display: inline-block;
            width: 1rem;
            height: 1rem;
            border: 2px solid #e5e7eb;
            border-top-color: #2196b6;
            border-radius: 50%;
            animation: spin 0.6s linear infinite;
        }
        
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        
        .line-clamp-2 {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        
        img {
            max-width: 100%;
            height: auto;
        }
        
        /* Biar konten di main area bisa scroll penuh sampai akhir */
        .content-padding > :last-child {
            margin-bottom: 1rem;
        }
    </style>
    
    @stack('styles')
</head>

<body>
    <div id="mobileOverlay" class="overlay" onclick="closeMobileMenu()"></div>
    
    <button type="button" class="mobile-menu-toggle" onclick="toggleMobileMenu()">
        <i class="fas fa-bars"></i>
    </button>
    
    <!-- APP WRAPPER -->
    <div class="app-wrapper">
        <!-- Sidebar -->
        <aside id="sidebar" class="sidebar-desktop">
            <!-- Bagian Atas: Logo & Navigasi -->
            <div>
                <div class="sidebar-logo text-center">
                    JACOB<br>
                    COLLECTIONS
                </div>

                <div class="sidebar-user text-center">
                    @auth
                        {{ ucfirst(Auth::user()->role) }}
                    @else
                        Guest
                    @endauth
                </div>

                <!-- Main Navigation Links -->
                <nav>
                    @auth
                        <!-- DASHBOARD (Semua role bisa akses) -->
                        <a href="{{ route('dashboard') }}" 
                           class="sidebar-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                            <i class="fas fa-th-large"></i> Dashboard
                        </a>

                        <!-- DATA PRODUK (HANYA OWNER) -->
                        @if(auth()->user()->role === 'owner')
                            <a href="{{ route('data_barang.index') }}" 
                               class="sidebar-link {{ request()->routeIs('data_barang.*') ? 'active' : '' }}">
                                <i class="fas fa-box"></i> Data Produk
                            </a>
                        @endif

                        <!-- DATA PENJUALAN (OWNER & ADMIN) -->
                        @if(in_array(auth()->user()->role, ['admin', 'owner']))
                            <a href="{{ route('data_penjualan.index') }}" 
                               class="sidebar-link {{ request()->routeIs('data_penjualan.*') ? 'active' : '' }}">
                                <i class="fas fa-chart-bar"></i> Data Penjualan
                            </a>

                            <!-- PREDIKSI PENJUALAN (OWNER & ADMIN) -->
                            <a href="{{ route('prediksi') }}" 
                               class="sidebar-link {{ request()->routeIs('prediksi*') ? 'active' : '' }}">
                                <i class="fas fa-chart-line"></i> Prediksi Penjualan
                            </a>
                        @endif

                        <!-- USER MANAGEMENT (HANYA ADMIN) -->
                        @if(auth()->user()->role === 'admin')
                            <a href="{{ route('users.index') }}" 
                               class="sidebar-link {{ request()->routeIs('users.*') ? 'active' : '' }}">
                                <i class="fas fa-users"></i> User Management
                            </a>
                        @endif
                    @endauth
                </nav>
            </div>

            <!-- BAGIAN BAWAH / FOOTER: Profile, Setting, Logout (KEMBALI KE SEMULA) -->
            @auth
                <div class="sidebar-footer">
                    <!-- Profile Card -->
                    <div class="profile-card">
                        <div class="flex items-center gap-3">
                            <img src="{{ Auth::user()->profile_picture 
                                    ? asset('storage/' . Auth::user()->profile_picture) 
                                    : 'https://ui-avatars.com/api/?name=' . urlencode(Auth::user()->name) . '&background=2196b6&color=fff' }}" 
                                 class="w-10 h-10 rounded-full object-cover" 
                                 alt="Profile">
                            <div class="flex-1 min-w-0">
                                <div class="text-sm font-semibold truncate text-gray-800">
                                    {{ Auth::user()->name }}
                                </div>
                                <div class="text-xs text-gray-500 truncate">
                                    {{ Auth::user()->email }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Setting Link -->
                    <a href="{{ route('profile.edit') }}" class="sidebar-link">
                        <i class="fas fa-cog"></i> Setting
                    </a>

                    <!-- Logout Form -->
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="sidebar-link text-red-500">
                            <i class="fas fa-sign-out-alt"></i> Logout
                        </button>
                    </form>
                </div>
            @endauth

            @guest
                <div class="sidebar-footer">
                    <div class="text-center text-sm text-gray-400 p-3">
                        Guest Mode
                    </div>
                </div>
            @endguest
        </aside>

        <!-- MAIN CONTENT - Scroll lancar sampai akhir -->
        <main class="main-content">
            <div class="content-padding">
                @hasSection('header-title')
                    <div class="bg-white rounded-lg shadow-sm p-4 mb-6">
                        <h2 class="text-lg font-semibold text-gray-700">
                            @yield('header-title')
                        </h2>
                    </div>
                @endif

                @yield('content')
            </div>
        </main>
    </div>

    <script>
        function toggleMobileMenu() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('mobileOverlay');
            sidebar.classList.toggle('open');
            overlay.classList.toggle('active');
            if (sidebar.classList.contains('open')) {
                document.body.style.overflow = 'hidden';
            } else {
                document.body.style.overflow = '';
            }
        }
        
        function closeMobileMenu() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('mobileOverlay');
            sidebar.classList.remove('open');
            overlay.classList.remove('active');
            document.body.style.overflow = '';
        }
        
        window.addEventListener('resize', function() {
            if (window.innerWidth > 768) {
                closeMobileMenu();
            }
        });
        
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeMobileMenu();
            }
        });
        
        document.querySelectorAll('.sidebar-link').forEach(link => {
            link.addEventListener('click', function() {
                if (window.innerWidth <= 768) {
                    setTimeout(closeMobileMenu, 100);
                }
            });
        });
    </script>

    @stack('scripts')
</body>
</html>