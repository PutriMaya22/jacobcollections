<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'ASTERA')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Stretch+Pro:wght@400&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="{{ asset('css/notification-bell.css') }}" rel="stylesheet">
    <link href="{{ asset('css/sidebar-fix.css') }}" rel="stylesheet">
    <style>
        body { 
            font-family: 'Poppins', sans-serif; 
            background: #F3F3F3; 
            margin: 0;
            padding: 0;
        }
        
        /* Force Sidebar Width - Inline CSS untuk override Tailwind */
        #sidebar {
            width: 320px !important;
            min-width: 320px !important;
            max-width: 320px !important;
            flex-shrink: 0 !important;
        }
        
        /* Force Main Content Margin */
        #main-content {
            margin-left: 320px !important;
        }
        
        /* Force Sidebar Padding */
        #sidebar > div:first-child,
        #sidebar > div:last-child {
            padding-left: 24px !important;
            padding-right: 24px !important;
        }
        
        /* Force Override untuk Tailwind Classes */
        .w-\[320px\] {
            width: 320px !important;
        }
        
        .ml-\[320px\] {
            margin-left: 320px !important;
        }
        
        .px-6 {
            padding-left: 24px !important;
            padding-right: 24px !important;
        }
        
        .sidebar-logo { 
            font-size: 2rem; 
            font-weight: 700; 
            color: #4A8692; 
            margin-bottom: 0.25rem; 
            font-family: 'Stretch Pro', sans-serif;
            letter-spacing: 0.05em;
            text-transform: uppercase;
        }
        .astera-header {
            font-family: 'Stretch Pro', sans-serif;
            font-weight: 700;
            letter-spacing: 0.05em;
        }
        .sidebar-user { 
            color: #2196b6; 
            font-size: 0.95rem; 
            font-weight: 500; 
            margin-bottom: 2.5rem; 
        }
        .sidebar-link { 
            display: flex; 
            align-items: center; 
            gap: 1rem; 
            padding: 0.9rem 1.5rem; 
            color: #787878; 
            font-size: 1.1rem; 
            font-weight: 500; 
            text-decoration: none; 
            border-radius: 0.5rem; 
            margin-bottom: 0.5rem; 
            transition: background 0.2s, color 0.2s; 
            width: 100% !important;
            max-width: 100% !important;
            box-sizing: border-box !important;
            overflow: hidden !important;
            white-space: nowrap !important;
            flex-shrink: 0 !important;
        }
        .sidebar-link.active, .sidebar-link:hover { 
            background: #E6FAFD; 
            color: #2196b6; 
        }
        .sidebar-link i { 
            font-size: 1.3rem; 
            flex-shrink: 0 !important;
            min-width: auto !important;
        }
        .sidebar-link span {
            overflow: hidden !important;
            text-overflow: ellipsis !important;
            white-space: nowrap !important;
            max-width: 100% !important;
        }
        
        /* Force Profile Summary */
        #sidebar .flex.items-center.gap-3.p-4.bg-gray-50.rounded-lg {
            width: 100% !important;
            max-width: 100% !important;
            box-sizing: border-box !important;
            overflow: hidden !important;
            padding: 16px !important;
        }
        
        #sidebar .flex.items-center.gap-3.p-4.bg-gray-50.rounded-lg img {
            flex-shrink: 0 !important;
            min-width: 48px !important;
            width: 48px !important;
            height: 48px !important;
        }
        
        #sidebar .flex.items-center.gap-3.p-4.bg-gray-50.rounded-lg .flex-1 {
            min-width: 0 !important;
            overflow: hidden !important;
            flex: 1 !important;
        }
        
        #sidebar .flex.items-center.gap-3.p-4.bg-gray-50.rounded-lg .flex-shrink-0 {
            flex-shrink: 0 !important;
            min-width: auto !important;
        }
        
        /* Force Sidebar Links Container */
        #sidebar .sidebar-menu {
            width: 100% !important;
            max-width: 100% !important;
            overflow: hidden !important;
        }
        
        /* Force Settings Link */
        #sidebar a[href*="profile.edit"] {
            width: 100% !important;
            max-width: 100% !important;
            box-sizing: border-box !important;
            overflow: hidden !important;
        }
        
        /* Force Logout Form */
        #sidebar form[method="POST"][action*="logout"] {
            width: 100% !important;
            max-width: 100% !important;
        }
        
        #sidebar form[method="POST"][action*="logout"] button {
            width: 100% !important;
            max-width: 100% !important;
            box-sizing: border-box !important;
            overflow: hidden !important;
        }
        
        /* Submenu styling */
        #dataSubMenu {
            transition: all 0.3s ease;
            max-height: 0;
            overflow: hidden;
            padding-left: 2.5rem;
        }
        
        #dataSubMenu.show {
            max-height: 500px;
        }
        
        .rotate-180 {
            transform: rotate(180deg);
        }
        
        .transition-transform {
            transition: transform 0.3s ease;
        }
        
        /* Debug - Hapus setelah testing */
        #sidebar::before {
            content: "SIDEBAR WIDTH: 320px" !important;
            position: absolute !important;
            top: 0 !important;
            left: 0 !important;
            background: red !important;
            color: white !important;
            padding: 4px !important;
            font-size: 10px !important;
            z-index: 9999 !important;
        }

        /* Force Notification Bell */
        #sidebar .relative button#notificationBell {
            padding: 8px !important;
            min-width: auto !important;
            flex-shrink: 0 !important;
            overflow: hidden !important;
        }
        
        /* Force Profile Summary Container */
        #sidebar .flex.items-center.gap-3.p-4.bg-gray-50.rounded-lg {
            width: 100% !important;
            max-width: 100% !important;
            box-sizing: border-box !important;
            overflow: hidden !important;
            padding: 16px !important;
            position: relative !important;
        }
        
        /* Force Profile Summary Image */
        #sidebar .flex.items-center.gap-3.p-4.bg-gray-50.rounded-lg img {
            flex-shrink: 0 !important;
            min-width: 48px !important;
            width: 48px !important;
            height: 48px !important;
            max-width: 48px !important;
        }
        
        /* Force Profile Summary Text Container */
        #sidebar .flex.items-center.gap-3.p-4.bg-gray-50.rounded-lg .flex-1 {
            min-width: 0 !important;
            overflow: hidden !important;
            flex: 1 !important;
            max-width: calc(100% - 120px) !important;
        }
        
        /* Force Profile Summary Text */
        #sidebar .flex.items-center.gap-3.p-4.bg-gray-50.rounded-lg .flex-1 p {
            overflow: hidden !important;
            text-overflow: ellipsis !important;
            white-space: nowrap !important;
            max-width: 100% !important;
            width: 100% !important;
        }
        
        /* Force Notification Bell Container */
        #sidebar .flex.items-center.gap-3.p-4.bg-gray-50.rounded-lg .flex-shrink-0 {
            flex-shrink: 0 !important;
            min-width: auto !important;
            max-width: 60px !important;
            overflow: hidden !important;
        }
        
        /* Force Notification Bell Button */
        #sidebar .flex.items-center.gap-3.p-4.bg-gray-50.rounded-lg .flex-shrink-0 button {
            padding: 8px !important;
            min-width: auto !important;
            flex-shrink: 0 !important;
            overflow: hidden !important;
            max-width: 100% !important;
        }
        
        /* Force Logout Form */
        #sidebar form[method="POST"][action*="logout"] {
            width: 100% !important;
            max-width: 100% !important;
            overflow: hidden !important;
        }
        
        /* Force Logout Button */
        #sidebar form[method="POST"][action*="logout"] button {
            width: 100% !important;
            max-width: 100% !important;
            box-sizing: border-box !important;
            overflow: hidden !important;
            text-overflow: ellipsis !important;
            white-space: nowrap !important;
        }
        
        /* Force All Sidebar Elements */
        #sidebar * {
            max-width: 100% !important;
            box-sizing: border-box !important;
        }
        
        /* Force Text Truncation */
        #sidebar p, #sidebar span, #sidebar div {
            overflow: hidden !important;
            text-overflow: ellipsis !important;
            white-space: nowrap !important;
        }
        
        /* Force Icon Containment */
        #sidebar i, #sidebar button, #sidebar img {
            flex-shrink: 0 !important;
        }
        
        /* Force Sidebar Container */
        #sidebar {
            width: 320px !important;
            min-width: 320px !important;
            max-width: 320px !important;
            flex-shrink: 0 !important;
            overflow: hidden !important;
        }
        
        /* Force Main Content */
        #main-content {
            margin-left: 320px !important;
        }
        
        /* Force Sidebar Padding */
        #sidebar > div:first-child,
        #sidebar > div:last-child {
            padding-left: 24px !important;
            padding-right: 24px !important;
            max-width: 100% !important;
            overflow: hidden !important;
        }
        
        /* Force Sidebar Menu */
        #sidebar .sidebar-menu {
            width: 100% !important;
            max-width: 100% !important;
            overflow: hidden !important;
            padding-left: 0 !important;
            padding-right: 0 !important;
        }
        
        /* Force Settings Link */
        #sidebar a[href*="profile.edit"] {
            width: 100% !important;
            max-width: 100% !important;
            box-sizing: border-box !important;
            overflow: hidden !important;
            padding-left: 24px !important;
            padding-right: 24px !important;
        }
        
        /* Force All Links */
        #sidebar .sidebar-link {
            width: 100% !important;
            max-width: 100% !important;
            box-sizing: border-box !important;
            overflow: hidden !important;
            padding-left: 24px !important;
            padding-right: 24px !important;
        }
        
        /* Force Submenu */
        #sidebar #dataSubMenu,
        #sidebar #operasionalSubMenu {
            padding-left: 48px !important;
            padding-right: 24px !important;
            max-width: 100% !important;
            overflow: hidden !important;
        }
        
        /* Force Submenu Links */
        #sidebar #dataSubMenu .sidebar-link,
        #sidebar #operasionalSubMenu .sidebar-link {
            padding-left: 48px !important;
            padding-right: 24px !important;
            max-width: 100% !important;
            overflow: hidden !important;
        }
        
        /* Force Text Truncation for All Text Elements */
        #sidebar p,
        #sidebar span,
        #sidebar div,
        #sidebar a,
        #sidebar button {
            overflow: hidden !important;
            text-overflow: ellipsis !important;
            white-space: nowrap !important;
            max-width: 100% !important;
        }
        
        /* Force Specific Text Elements */
        #sidebar .sidebar-user {
            overflow: hidden !important;
            text-overflow: ellipsis !important;
            white-space: nowrap !important;
            max-width: 100% !important;
            width: 100% !important;
            text-align: center !important;
        }
        
        /* Force Button Text */
        #sidebar button span {
            overflow: hidden !important;
            text-overflow: ellipsis !important;
            white-space: nowrap !important;
            max-width: 100% !important;
        }
        
        /* Force Icon Positioning */
        #sidebar .fa-chevron-down {
            flex-shrink: 0 !important;
            min-width: auto !important;
        }
        
        /* Force Absolute Positioning Elements */
        #sidebar .absolute {
            position: absolute !important;
            z-index: 10 !important;
        }
        
        /* Force Notification Badge */
        #sidebar .bg-red-500 {
            position: absolute !important;
            z-index: 10 !important;
            right: -4px !important;
            top: -4px !important;
        }
    </style>
    @stack('styles')
</head>
<body>
    <div id="app-layout" class="flex min-h-screen relative">
        <!-- Sidebar Overlay -->
        <div id="sidebarOverlay" class="hidden fixed inset-0 bg-black bg-opacity-50 z-20"></div>
        
        <!-- Sidebar -->
        <aside id="sidebar" class="sidebar fixed top-0 left-0 h-full z-30 bg-white transition-transform duration-300 ease-in-out translate-x-0 w-[320px] rounded-r-2xl flex flex-col justify-between pt-8">
            <!-- Bagian Atas -->
            <div class="flex flex-col items-center w-full px-6">
                <button id="closeSidebar" class="absolute top-4 right-4 text-gray-400 hover:text-gray-700 focus:outline-none md:hidden" style="font-size: 1.7rem;">
                    <i class="fas fa-times"></i>
                </button>
                <div class="sidebar-logo">ASTERA</div>
                <div class="sidebar-user">{{ Auth::user()->name ?? 'User' }}</div>

                <nav class="sidebar-menu w-full mt-6">
                    <!-- Dashboard -->
                    <a href="{{ route('dashboard') }}" class="sidebar-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                        <i class="fas fa-th-large"></i> Dashboard
                    </a>
                    
                    <!-- Data Master -->
                    <div class="mb-2">
                        <button id="dataMenuButton" class="sidebar-link w-full text-left {{ request()->routeIs('data_barang.*', 'data_ruangan.*') ? 'active' : '' }}">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <i class="fas fa-database"></i>
                                    <span>Data Master</span>
                                </div>
                                <i class="fas fa-chevron-down text-xs transition-transform duration-200" id="dataMenuIcon"></i>
                            </div>
                        </button>
                        
                        <div id="dataSubMenu" class="{{ request()->routeIs('data_barang.*', 'data_ruangan.*') ? 'show' : '' }}">
                            <a href="{{ route('data_barang.index') }}" class="sidebar-link text-sm {{ request()->routeIs('data_barang.*') ? 'active' : '' }}">
                                <i class="fas fa-box-open"></i> Data Barang
                            </a>
                            <a href="{{ route('data_ruangan.index') }}" class="sidebar-link text-sm {{ request()->routeIs('data_ruangan.*') ? 'active' : '' }}">
                                <i class="fas fa-door-open"></i> Data Ruangan
                            </a>
                        </div>
                    </div>
                    
                    <!-- Operasional -->
                    <div class="mb-2">
                        <button id="operasionalMenuButton" class="sidebar-link w-full text-left {{ request()->routeIs('barang_keluar.*') ? 'active' : '' }}">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <i class="fas fa-exchange-alt"></i>
                                    <span>Operasional</span>
                                </div>
                                <i class="fas fa-chevron-down text-xs transition-transform duration-200" id="operasionalMenuIcon"></i>
                            </div>
                        </button>
                        
                        <div id="operasionalSubMenu" class="{{ request()->routeIs('barang_keluar.*') ? 'show' : '' }}">
                            <a href="{{ route('barang_keluar.index') }}" class="sidebar-link text-sm {{ request()->routeIs('barang_keluar.*') ? 'active' : '' }}">
                                <i class="fas fa-arrow-up"></i> Barang Keluar
                            </a>
                        </div>
                    </div>
                </nav>
            </div>

            <!-- Bagian Bawah (Profile, Notifications, Settings, Logout) -->
            <div class="w-full px-6 mb-6 space-y-3">
                <!-- Settings -->
                <a href="{{ route('profile.edit') }}" class="sidebar-link {{ request()->routeIs('profile.*') ? 'active' : '' }}">
                    <i class="fas fa-cog"></i> Setting
                </a>
                
                <!-- Profile Summary -->
                <div class="flex items-center gap-3 p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition-all">
                    <div class="flex items-center gap-3 flex-1 cursor-pointer" onclick="window.location.href='{{ route('profile.edit') }}'">
                        <img src="https://randomuser.me/api/portraits/men/32.jpg" alt="Profile" class="w-12 h-12 rounded-full object-cover border-2 border-gray-200 flex-shrink-0">
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-700 truncate">{{ Auth::user()->name ?? 'Hendrick Moseng' }} (User)</p>
                            <p class="text-xs text-gray-500 truncate">{{ Auth::user()->email ?? 'Hendrickmoseng@gmail.com' }}</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 flex-shrink-0">
                        <!-- Notification Bell -->
                        <div class="notification-bell">
                            <button id="notificationBell" class="p-2 rounded-full hover:bg-gray-200 transition-colors focus:outline-none">
                                <i class="far fa-bell text-gray-500 text-sm"></i>
                                <span id="notificationCount" class="notification-count hidden">0</span>
                            </button>
                            
                            <!-- Notification Dropdown -->
                            <div id="notificationDropdown" class="notification-dropdown hidden">
                                <div class="notification-header">
                                    <h3>Notifikasi</h3>
                                    <button onclick="markAllAsRead()" class="mark-all-read">Tandai semua dibaca</button>
                                </div>
                                <div id="notificationList" class="notification-list">
                                    <!-- Notifications will be loaded here -->
                                </div>
                                <div class="notification-footer">
                                    <a href="#" class="view-all-link">Lihat semua notifikasi</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Logout Button -->
                <form method="POST" action="{{ route('logout') }}" class="w-full">
                    @csrf
                    <button type="submit" class="sidebar-link w-full text-left text-red-600 hover:text-red-700 hover:bg-red-50 transition-all">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </button>
                </form>
            </div>
        </aside>

        <div id="main-content" class="flex-1 transition-all duration-300 ease-in-out ml-[320px] pt-8 px-8">
            <!-- Header Bar -->
            <div class="header-bar flex items-center justify-between mb-6 py-3 px-4 bg-white rounded-lg shadow-sm">
                <!-- Left Side - Sidebar Toggle -->
                <button id="openSidebar" class="text-gray-500 hover:text-gray-700 focus:outline-none mr-4 md:hidden">
                    <i class="fas fa-bars text-xl"></i>
                </button>

                <!-- Center - Title -->
                <div class="header-title text-gray-600 font-medium flex-1 text-center hidden md:block">
                    <span class="astera-header">@yield('header-title', 'ASTERA STI PLN UID JAKARTA RAYA')</span>
                </div>

                <!-- Right Side - Empty for now -->
                <div class="flex items-center space-x-4">
                </div>
            </div>
            
            <!-- Page Header Slot -->
            @if (isset($header))
                <header class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6 text-gray-900">
                        {{ $header }}
                    </div>
                </header>
            @endif
            
            <!-- Main Content Area -->
            <div class="main-container">
                {{ $slot }}
            </div>
        </div>
    </div>

    <script>
        // Sidebar functionality
        const sidebar = document.getElementById('sidebar');
        const mainContent = document.getElementById('main-content');
        const openSidebarBtn = document.getElementById('openSidebar');
        const closeSidebarBtn = document.getElementById('closeSidebar');
        const sidebarOverlay = document.getElementById('sidebarOverlay');
        
        function toggleSidebar(open) {
            if (window.innerWidth < 768) {
                if (open) {
                    sidebar.classList.remove('-translate-x-full');
                    sidebarOverlay.classList.remove('hidden');
                    closeSidebarBtn.classList.remove('hidden');
                } else {
                    sidebar.classList.add('-translate-x-full');
                    sidebarOverlay.classList.add('hidden');
                    closeSidebarBtn.classList.add('hidden');
                }
            } else {
                if (open) {
                    sidebar.classList.remove('-translate-x-full');
                    mainContent.classList.remove('ml-0');
                    mainContent.classList.add('ml-[320px]');
                } else {
                    sidebar.classList.add('-translate-x-full');
                    mainContent.classList.add('ml-0');
                    mainContent.classList.remove('ml-[320px]');
                }
            }
        }

        openSidebarBtn.addEventListener('click', () => toggleSidebar(true));
        closeSidebarBtn.addEventListener('click', () => toggleSidebar(false));
        sidebarOverlay.addEventListener('click', () => toggleSidebar(false));

        // Handle responsive behavior
        function handleResize() {
            if (window.innerWidth >= 768) {
                toggleSidebar(true);
                sidebarOverlay.classList.add('hidden');
            } else {
                toggleSidebar(false);
            }
        }
        
        window.addEventListener('resize', handleResize);

        // Data Menu Dropdown functionality
        const dataMenuButton = document.getElementById('dataMenuButton');
        const dataSubMenu = document.getElementById('dataSubMenu');
        const dataMenuIcon = document.getElementById('dataMenuIcon');
        
        if (dataMenuButton && dataSubMenu && dataMenuIcon) {
            dataMenuButton.addEventListener('click', function() {
                dataSubMenu.classList.toggle('show');
                dataMenuIcon.classList.toggle('rotate-180');
                
                // Close other open submenus if any
                document.querySelectorAll('.sidebar-menu > div > div').forEach(menu => {
                    if (menu !== dataSubMenu && menu.classList.contains('show')) {
                        menu.classList.remove('show');
                        menu.previousElementSibling.querySelector('i.fa-chevron-down').classList.remove('rotate-180');
                    }
                });
            });
            
            // Keep submenu open if current route matches
            if (window.location.pathname.includes('/data_barang') || 
                window.location.pathname.includes('/data_ruangan')) {
                dataSubMenu.classList.add('show');
                dataMenuIcon.classList.add('rotate-180');
            }
        }

        // Operasional Menu Dropdown functionality
        const operasionalMenuButton = document.getElementById('operasionalMenuButton');
        const operasionalSubMenu = document.getElementById('operasionalSubMenu');
        const operasionalMenuIcon = document.getElementById('operasionalMenuIcon');

        if (operasionalMenuButton && operasionalSubMenu && operasionalMenuIcon) {
            operasionalMenuButton.addEventListener('click', function() {
                operasionalSubMenu.classList.toggle('show');
                operasionalMenuIcon.classList.toggle('rotate-180');
                
                // Close other open submenus if any
                document.querySelectorAll('.sidebar-menu > div > div').forEach(menu => {
                    if (menu !== operasionalSubMenu && menu.classList.contains('show')) {
                        menu.classList.remove('show');
                        menu.previousElementSibling.querySelector('i.fa-chevron-down').classList.remove('rotate-180');
                    }
                });
            });
            
            // Keep submenu open if current route matches
            if (window.location.pathname.includes('/barang_masuk') || 
                window.location.pathname.includes('/barang_keluar')) {
                operasionalSubMenu.classList.add('show');
                operasionalMenuIcon.classList.add('rotate-180');
            }
        }

        // Notification System
        let notifications = [];
        let unreadCount = 0;

        function toggleNotifications(event) {
            if (event) {
                event.preventDefault();
                event.stopPropagation();
            }
            
            const dropdown = document.getElementById('notificationDropdown');
            const bell = document.getElementById('notificationBell');
            
            if (!dropdown || !bell) {
                console.error('Notification elements not found');
                return false;
            }
            
            const isHidden = dropdown.classList.contains('hidden');
            
            if (isHidden) {
                dropdown.classList.remove('hidden');
                dropdown.classList.add('show');
                loadNotifications();
            } else {
                dropdown.classList.add('hidden');
                dropdown.classList.remove('show');
            }
            
            return false;
        }

        // Close dropdown when clicking outside
        document.addEventListener('click', function(event) {
            const dropdown = document.getElementById('notificationDropdown');
            const bell = document.getElementById('notificationBell');
            
            if (dropdown && bell) {
                if (!dropdown.contains(event.target) && !bell.contains(event.target)) {
                    dropdown.classList.add('hidden');
                    dropdown.classList.remove('show');
                }
            }
        });

        async function loadNotifications() {
            try {
                const response = await fetch('/notifications');
                
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                const data = await response.json();
                
                if (data.success) {
                    notifications = data.notifications || [];
                    unreadCount = data.unread_count || 0;
                    
                    updateNotificationCount();
                    renderNotifications();
                } else {
                    throw new Error(data.error || 'Failed to load notifications');
                }
            } catch (error) {
                console.error('Error loading notifications:', error);
                const list = document.getElementById('notificationList');
                if (list) {
                    list.innerHTML = `
                        <div class="notification-error">
                            <i class="fas fa-exclamation-triangle"></i>
                            <p>Error loading notifications: ${error.message}</p>
                        </div>
                    `;
                }
            }
        }

        function updateNotificationCount() {
            const countElement = document.getElementById('notificationCount');
            
            if (!countElement) {
                console.error('Notification count element not found');
                return;
            }
            
            if (unreadCount > 0) {
                countElement.textContent = unreadCount;
                countElement.classList.remove('hidden');
            } else {
                countElement.classList.add('hidden');
            }
        }

        function renderNotifications() {
            const list = document.getElementById('notificationList');
            
            if (!list) {
                console.error('Notification list element not found');
                return;
            }
            
            if (notifications.length === 0) {
                list.innerHTML = `
                    <div class="notification-empty">
                        <i class="far fa-bell"></i>
                        <p>Tidak ada notifikasi</p>
                    </div>
                `;
                return;
            }

            const html = notifications.map(notification => `
                <div class="notification-item ${notification.is_read ? 'read' : 'unread'}" 
                     onclick="markAsRead(${notification.id})">
                    <div class="notification-content">
                        <div class="notification-icon">
                            <i class="${notification.icon || 'fas fa-info-circle'} text-${notification.color}-500"></i>
                        </div>
                        <div class="notification-text">
                            <p class="notification-title">${notification.title}</p>
                            <p class="notification-message">${notification.message}</p>
                            <p class="notification-time">${formatTime(notification.created_at)}</p>
                        </div>
                        ${!notification.is_read ? '<div class="notification-unread-indicator"></div>' : ''}
                    </div>
                </div>
            `).join('');
            
            list.innerHTML = html;
        }

        async function markAsRead(notificationId) {
            try {
                const response = await fetch('/notifications/mark-read', {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({ notification_id: notificationId })
                });
                
                const data = await response.json();
                if (data.success) {
                    unreadCount = data.unread_count;
                    updateNotificationCount();
                    loadNotifications();
                }
            } catch (error) {
                console.error('Error marking notification as read:', error);
            }
        }

        async function markAllAsRead() {
            try {
                const response = await fetch('/notifications/mark-all-read', {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                });
                
                const data = await response.json();
                if (data.success) {
                    unreadCount = 0;
                    updateNotificationCount();
                    loadNotifications();
                }
            } catch (error) {
                console.error('Error marking all notifications as read:', error);
            }
        }

        function formatTime(timestamp) {
            const date = new Date(timestamp);
            const now = new Date();
            const diffInMinutes = Math.floor((now - date) / (1000 * 60));
            
            if (diffInMinutes < 1) return 'Baru saja';
            if (diffInMinutes < 60) return `${diffInMinutes}m yang lalu`;
            if (diffInMinutes < 1440) return `${Math.floor(diffInMinutes / 60)}j yang lalu`;
            return date.toLocaleDateString('id-ID');
        }

        // Initialize everything when DOM is loaded
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize sidebar
            handleResize();
            
            // Check if notification elements exist
            const bell = document.getElementById('notificationBell');
            const dropdown = document.getElementById('notificationDropdown');
            const count = document.getElementById('notificationCount');
            
            if (bell && dropdown && count) {
                // Initialize notifications
                loadNotifications();
                
                // Update notifications every 30 seconds
                setInterval(loadNotifications, 30000);
                
                // Add event listener for notification bell
                bell.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    toggleNotifications(e);
                });
            }
        });
    </script>

    @stack('scripts')
</body>
</html>
