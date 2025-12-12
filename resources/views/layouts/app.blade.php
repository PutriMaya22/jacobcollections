<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'ASTERA')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Stretch+Pro:wght@400&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        /* Notifikasi */
        .notification {
            animation: slideIn 0.5s forwards, fadeOut 0.5s 4.5s forwards;
            position: fixed;
            top: 1rem;
            right: 1rem;
            z-index: 1000;
            max-width: 350px;
        }

        @keyframes slideIn {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }

        @keyframes fadeOut {
            from { opacity: 1; }
            to { opacity: 0; }
        }

        /* Notification Badge */
        .notification-badge {
            position: absolute;
            top: -2px;
            right: -2px;
            background-color: #ef4444;
            color: white;
            font-size: 10px;
            font-weight: bold;
            border-radius: 50%;
            width: 16px;
            height: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            animation: pulse 2s infinite;
        }

        .notification-badge.hidden {
            display: none;
        }

        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.1); }
            100% { transform: scale(1); }
        }

        body { 
            font-family: 'Poppins', sans-serif; 
            background: #F3F3F3; 
            margin: 0;
            padding: 0;
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
        }
        .sidebar-link.active, .sidebar-link:hover { 
            background: #E6FAFD; 
            color: #2196b6; 
        }
        .sidebar-link i { 
            font-size: 1.3rem; 
        }
        
        /* Submenu styling */
        #dataSubMenu, #operasionalSubMenu {
            transition: all 0.3s ease;
            max-height: 0;
            overflow: hidden;
            padding-left: 2.5rem;
        }
        
        #dataSubMenu.show, #operasionalSubMenu.show {
            max-height: 500px;
        }
        
        .rotate-180 {
            transform: rotate(180deg);
        }
        
        .transition-transform {
            transition: transform 0.3s ease;
        }
        
        /* Notification Panel Styles */
        .notification-item {
            border-left: 4px solid transparent;
            transition: all 0.2s ease;
        }
        
        .notification-item:hover {
            background-color: #f9fafb;
        }
        
        .notification-item.type-barang_masuk {
            border-left-color: #10b981;
        }
        
        .notification-item.type-stok_rendah {
            border-left-color: #f59e0b;
        }
        
        .notification-item.type-barang_keluar {
            border-left-color: #3b82f6;
        }
        
        .notification-item.type-stok_habis {
            border-left-color: #ef4444;
        }
        
        .notification-fade-in {
            animation: notificationFadeIn 0.3s ease-out;
        }
        
        @keyframes notificationFadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        /* Skeleton Loading */
        .skeleton {
            background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
            background-size: 200% 100%;
            animation: loading 1.5s infinite;
            border-radius: 4px;
        }
        
        .skeleton-text {
            height: 12px;
            margin-bottom: 6px;
        }
        
        .skeleton-text.short {
            width: 60%;
        }
        
        .skeleton-text.medium {
            width: 80%;
        }
        
        @keyframes loading {
            0% { background-position: 200% 0; }
            100% { background-position: -200% 0; }
        }
    </style>
    @stack('styles')
</head>
<body>
    <div id="app-layout" class="flex min-h-screen relative">
        <!-- Sidebar Overlay -->
        <div id="sidebarOverlay" class="hidden fixed inset-0 bg-black bg-opacity-50 z-20"></div>
        <aside id="sidebar" class="sidebar fixed top-0 left-0 h-full z-30 bg-white transition-transform duration-300 ease-in-out translate-x-0 w-[200px] rounded-r-2xl flex flex-col justify-between pt-8">
            <!-- Bagian Atas -->
            <div class="flex flex-col items-center w-full px-2">
                <button id="closeSidebar" class="absolute top-4 right-4 text-gray-400 hover:text-gray-700 focus:outline-none md:hidden" style="font-size: 1.7rem;">
                    <i class="fas fa-times"></i>
                </button>
                <div class="sidebar-logo">ASTERA</div>
                <div class="sidebar-user">
                    @auth
                        @php
                            $roleDisplay = [
                                'admin' => 'Admin',
                                'user_input' => 'User (Input)',
                                'user_operasional' => 'User (Operasional)'
                            ];
                            $currentRole = Auth::user()->role ?? 'user_input';
                        @endphp
                        {{ $roleDisplay[$currentRole] }}
                    @endauth
                </div>

                <nav class="sidebar-menu w-full mt-6">
                    <!-- Dashboard -->
                    <a href="{{ route('dashboard') }}" class="sidebar-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                        <i class="fas fa-th-large"></i> Dashboard
                    </a>
                    
                    @if(auth()->user()->isAdmin() || auth()->user()->isUser())
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
                    @endif
                    
                    @if(auth()->user()->isAdmin() || auth()->user()->isUserOperasional())
                    <!-- Operasional -->
                    <div class="mb-2">
                        <button id="operasionalMenuButton" class="sidebar-link w-full text-left {{ request()->routeIs('barang_keluar.*', 'pemeliharaan.*', 'peminjaman.*') ? 'active' : '' }}">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <i class="fas fa-exchange-alt"></i>
                                    <span>Operasional</span>
                                </div>
                                <i class="fas fa-chevron-down text-xs transition-transform duration-200" id="operasionalMenuIcon"></i>
                            </div>
                        </button>
                        
                        <div id="operasionalSubMenu" class="{{ request()->routeIs('barang_keluar.*', 'pemeliharaan.*', 'peminjaman.*') ? 'show' : '' }}">
                            <a href="{{ route('barang_keluar.index') }}" class="sidebar-link text-sm {{ request()->routeIs('barang_keluar.*') ? 'active' : '' }}">
                                <i class="fas fa-arrow-up"></i> Barang Keluar
                            </a>
                            <a href="{{ route('pemeliharaan.index') }}" class="sidebar-link text-sm {{ request()->routeIs('pemeliharaan.*') ? 'active' : '' }}">
                                <i class="fas fa-tools"></i> Pemeliharaan
                            </a>
                            <a href="{{ route('peminjaman.index') }}" class="sidebar-link text-sm {{ request()->routeIs('peminjaman.*') ? 'active' : '' }}">
                                <i class="fas fa-hand-holding"></i> Peminjaman
                            </a>
                        </div>
                    </div>
                    @endif
                    
                    @if(auth()->user()->isAdmin())
                    <!-- User Management (hanya untuk admin) -->
                    <a href="{{ route('users.index') }}" class="sidebar-link {{ request()->routeIs('users.*') ? 'active' : '' }}">
                        <i class="fas fa-users"></i> User Management
                    </a>
                    @endif
                </nav>
            </div>

            <!-- Bagian Bawah (Profile, Notifications, Settings, Logout) -->
            <div class="w-full px-2 mb-6 space-y-3">
                <!-- Settings -->
                <a href="{{ route('profile.edit') }}" class="sidebar-link {{ request()->routeIs('profile.*') ? 'active' : '' }}">
                    <i class="fas fa-cog"></i> Setting
                </a>
                
                <!-- Profile Summary -->
                <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg">
                    <img src="{{ Auth::user()->profile_picture ? asset('storage/' . Auth::user()->profile_picture) : 'https://randomuser.me/api/portraits/men/32.jpg' }}" 
                        alt="Profile" 
                        class="w-10 h-10 rounded-full object-cover border-2 border-gray-200">
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-700">
                            {{ Auth::user()->name ?? 'User' }}
                        </p>
                    </div>
                    <div class="flex items-center gap-2 flex-shrink-0">
                        <!-- Notification Bell -->
                        <div class="relative">
                            <button id="notificationBell" class="p-1 rounded-full hover:bg-gray-200 transition-colors focus:outline-none">
                                <i class="far fa-bell text-gray-500 text-sm"></i>
                                <span id="notificationCount" class="notification-badge">3</span>
                            </button>
                        </div>

                        <!-- Logout -->
                        <form method="POST" action="{{ route('logout') }}" class="inline">
                            @csrf
                            <button type="submit" class="p-1 rounded-full hover:bg-gray-200 transition-colors focus:outline-none text-red-500 hover:text-red-700">
                                <i class="fas fa-sign-out-alt"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </aside>

        <div id="main-content" class="flex-1 transition-all duration-300 ease-in-out ml-[200px] pt-8 px-8">
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

            <!-- Notification Panel (Slide-in dari kiri) -->
            <div id="notificationModal" class="hidden fixed inset-0 z-50 flex justify-start">
                <!-- Background overlay -->
                <div id="overlay" class="absolute inset-0 bg-black bg-opacity-30"></div>

                <!-- Panel Notifikasi -->
                <div class="relative bg-white w-[400px] h-full shadow-lg transform -translate-x-full transition-transform duration-300 ease-in-out" id="notificationPanel">
                    <!-- Header -->
                    <div class="flex items-center justify-between px-5 py-4 border-b border-gray-200 bg-gray-50">
                        <h2 class="text-lg font-semibold text-gray-800 flex items-center gap-2">
                            <i class="far fa-bell text-blue-500"></i> 
                            <span>Notifikasi</span>
                            <span id="panelNotificationCount" class="bg-blue-100 text-blue-800 px-2 py-1 rounded-full text-xs font-medium">3</span>
                        </h2>
                        <button id="closeNotification" class="text-gray-500 hover:text-gray-700 transition">
                            <i class="fas fa-times text-lg"></i>
                        </button>
                    </div>

                    <!-- Konten Notifikasi -->
                    <div id="notificationContent" class="overflow-y-auto h-[calc(100%-120px)]">
                        <!-- Empty state -->
                        <div id="notificationEmpty" class="hidden p-8 text-center text-gray-500">
                            <i class="far fa-bell-slash text-4xl mb-4"></i>
                            <p>Tidak ada notifikasi</p>
                        </div>
                        
                        <!-- Notifications will be loaded here -->
                        <div id="notificationList" class="divide-y divide-gray-100">
                            <!-- Skeleton loading akan muncul di sini sementara -->
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="px-5 py-3 border-t border-gray-200 text-center">
                        <button id="refreshNotifications" class="text-blue-600 hover:underline font-medium mr-4">
                            <i class="fas fa-sync-alt mr-1"></i> Refresh
                        </button>
                        <button id="markAllRead" class="text-gray-600 hover:underline font-medium">
                            Tandai Semua Dibaca
                        </button>
                    </div>
                </div>
            </div>
            
            <!-- Main Content Area -->
            <div class="main-container">
                @yield('content')
            </div>
        </div>
    </div>

    <script>
        // Sidebar Toggle Functionality
        document.addEventListener('DOMContentLoaded', function() {
            const openSidebarBtn = document.getElementById('openSidebar');
            const closeSidebarBtn = document.getElementById('closeSidebar');
            const sidebar = document.getElementById('sidebar');
            const sidebarOverlay = document.getElementById('sidebarOverlay');
            const mainContent = document.getElementById('main-content');
            
            // Toggle sidebar on mobile
            function openSidebar() {
                sidebar.classList.remove('-translate-x-full');
                sidebarOverlay.classList.remove('hidden');
                document.body.style.overflow = 'hidden';
            }
            
            function closeSidebar() {
                sidebar.classList.add('-translate-x-full');
                sidebarOverlay.classList.add('hidden');
                document.body.style.overflow = 'auto';
            }
            
            openSidebarBtn.addEventListener('click', openSidebar);
            closeSidebarBtn.addEventListener('click', closeSidebar);
            sidebarOverlay.addEventListener('click', closeSidebar);
            
            // Menu Toggle Functionality
            const dataMenuButton = document.getElementById('dataMenuButton');
            const operasionalMenuButton = document.getElementById('operasionalMenuButton');
            const dataSubMenu = document.getElementById('dataSubMenu');
            const operasionalSubMenu = document.getElementById('operasionalSubMenu');
            const dataMenuIcon = document.getElementById('dataMenuIcon');
            const operasionalMenuIcon = document.getElementById('operasionalMenuIcon');
            
            if (dataMenuButton && dataSubMenu) {
                dataMenuButton.addEventListener('click', function() {
                    dataSubMenu.classList.toggle('show');
                    dataMenuIcon.classList.toggle('rotate-180');
                });
            }
            
            if (operasionalMenuButton && operasionalSubMenu) {
                operasionalMenuButton.addEventListener('click', function() {
                    operasionalSubMenu.classList.toggle('show');
                    operasionalMenuIcon.classList.toggle('rotate-180');
                });
            }
            
            // Close sidebar when a menu item is clicked (on mobile)
            const menuItems = document.querySelectorAll('.sidebar-link');
            menuItems.forEach(item => {
                item.addEventListener('click', function() {
                    if (window.innerWidth < 768) {
                        closeSidebar();
                    }
                });
            });
            
            // Adjust main content margin on resize
            function adjustLayout() {
                if (window.innerWidth >= 768) {
                    mainContent.style.marginLeft = '200px';
                    sidebar.classList.remove('-translate-x-full');
                    sidebarOverlay.classList.add('hidden');
                } else {
                    mainContent.style.marginLeft = '0';
                    sidebar.classList.add('-translate-x-full');
                }
            }
            
            window.addEventListener('resize', adjustLayout);
            adjustLayout(); // Initial call
        });

        // Notification System
        class NotificationSystem {
            constructor() {
                this.bell = document.getElementById('notificationBell');
                this.modal = document.getElementById('notificationModal');
                this.panel = document.getElementById('notificationPanel');
                this.overlay = document.getElementById('overlay');
                this.closeBtn = document.getElementById('closeNotification');
                this.countBadge = document.getElementById('notificationCount');
                this.panelCount = document.getElementById('panelNotificationCount');
                this.empty = document.getElementById('notificationEmpty');
                this.list = document.getElementById('notificationList');
                this.refreshBtn = document.getElementById('refreshNotifications');
                this.markAllReadBtn = document.getElementById('markAllRead');
                
                this.notifications = [];
                this.isOpen = false;
                
                this.init();
            }
            
            init() {
                // Event listeners
                this.bell.addEventListener('click', () => this.togglePanel());
                this.overlay.addEventListener('click', () => this.closePanel());
                this.closeBtn.addEventListener('click', () => this.closePanel());
                this.refreshBtn.addEventListener('click', () => this.loadNotifications());
                this.markAllReadBtn.addEventListener('click', () => this.markAllAsRead());
                
                // Load notifications immediately
                this.loadNotifications();
            }
            
            async loadNotifications() {
                // Tampilkan skeleton loading
                this.showSkeletonLoading();
                
                try {
                    const response = await fetch('/api/notifications', {
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json',
                        }
                    });
                    
                    if (response.ok) {
                        const data = await response.json();
                        this.notifications = data.notifications;
                        this.updateUI(data.count);
                        this.renderNotifications();
                    } else {
                        // Jika gagal, tampilkan notifikasi default
                        this.showDefaultNotifications();
                    }
                } catch (error) {
                    console.error('Error loading notifications:', error);
                    // Tampilkan notifikasi default jika terjadi error
                    this.showDefaultNotifications();
                }
            }
            
            showSkeletonLoading() {
                this.hideEmpty();
                this.list.innerHTML = '';
                
                // Buat 3 skeleton items
                for (let i = 0; i < 3; i++) {
                    const skeletonItem = document.createElement('div');
                    skeletonItem.className = 'p-4';
                    skeletonItem.innerHTML = `
                        <div class="flex items-start gap-3">
                            <div class="skeleton rounded-full w-6 h-6 mt-1"></div>
                            <div class="flex-1">
                                <div class="skeleton skeleton-text medium"></div>
                                <div class="skeleton skeleton-text short"></div>
                                <div class="skeleton skeleton-text" style="width: 40%; margin-top: 8px;"></div>
                            </div>
                        </div>
                    `;
                    this.list.appendChild(skeletonItem);
                }
            }
            
            updateUI(count) {
                // Update badge
                if (count > 0) {
                    this.countBadge.textContent = count > 99 ? '99+' : count;
                    this.countBadge.classList.remove('hidden');
                    this.panelCount.textContent = count;
                } else {
                    this.countBadge.classList.add('hidden');
                    this.panelCount.textContent = '0';
                }
            }
            
            renderNotifications() {
                // Jika tidak ada notifikasi dari server, tampilkan notifikasi default
                if (this.notifications.length === 0) {
                    this.showDefaultNotifications();
                    return;
                }
                
                this.hideEmpty();
                this.list.innerHTML = '';
                
                this.notifications.forEach(notification => {
                    const item = this.createNotificationItem(notification);
                    this.list.appendChild(item);
                });
            }
            
            showDefaultNotifications() {
                this.hideEmpty();
                this.list.innerHTML = '';
                
                // Notifikasi default
                const defaultNotifications = [
                    {
                        title: 'Barang Masuk Baru',
                        message: 'Barang "Laptop Asus" telah masuk (5 unit)',
                        time: '5 menit yang lalu',
                        icon: 'fas fa-box',
                        color: 'green',
                        type: 'barang_masuk'
                    },
                    {
                        title: 'Stok Hampir Habis',
                        message: 'Barang "Printer Canon" tersisa 2 unit',
                        time: '1 jam yang lalu',
                        icon: 'fas fa-exclamation-triangle',
                        color: 'yellow',
                        type: 'stok_rendah'
                    },
                    {
                        title: 'Barang Keluar',
                        message: 'Barang "Proyektor Epson" keluar (1 unit)',
                        time: 'Kemarin',
                        icon: 'fas fa-truck-loading',
                        color: 'blue',
                        type: 'barang_keluar'
                    }
                ];
                
                defaultNotifications.forEach(notification => {
                    const item = this.createNotificationItem(notification);
                    this.list.appendChild(item);
                });
            }
            
            createNotificationItem(notification) {
                const item = document.createElement('div');
                item.className = `notification-item type-${notification.type} p-4 hover:bg-gray-50 cursor-pointer transition notification-fade-in`;
                
                const colorMap = {
                    'green': 'text-green-500',
                    'yellow': 'text-yellow-500',
                    'blue': 'text-blue-500',
                    'red': 'text-red-500'
                };
                
                item.innerHTML = `
                    <div class="flex items-start gap-3">
                        <i class="${notification.icon} ${colorMap[notification.color]} text-xl mt-1"></i>
                        <div class="flex-1">
                            <div class="font-medium text-gray-800">${notification.title}</div>
                            <div class="text-sm text-gray-500">${notification.message}</div>
                            <div class="text-xs text-gray-400 mt-1">${notification.time}</div>
                        </div>
                    </div>
                `;
                
                return item;
            }
            
            hideEmpty() {
                this.empty.classList.add('hidden');
            }
            
            togglePanel() {
                if (this.isOpen) {
                    this.closePanel();
                } else {
                    this.openPanel();
                }
            }
            
            openPanel() {
                this.modal.classList.remove('hidden');
                setTimeout(() => {
                    this.panel.classList.remove('-translate-x-full');
                }, 10);
                this.isOpen = true;
                
                // Load notifications when panel opens
                this.loadNotifications();
            }
            
            closePanel() {
                this.panel.classList.add('-translate-x-full');
                setTimeout(() => {
                    this.modal.classList.add('hidden');
                }, 300);
                this.isOpen = false;
            }
            
            async markAllAsRead() {
                try {
                    const response = await fetch('/api/notifications/mark-read', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({ mark_all: true })
                    });
                    
                    if (response.ok) {
                        // Reset notification count
                        this.updateUI(0);
                        // Show success message
                        this.showToast('Semua notifikasi telah ditandai sebagai dibaca', 'success');
                    }
                } catch (error) {
                    console.error('Error marking notifications as read:', error);
                    this.showToast('Gagal menandai notifikasi', 'error');
                }
            }
            
            showToast(message, type = 'info') {
                const toast = document.createElement('div');
                toast.className = `fixed top-4 right-4 z-50 px-4 py-2 rounded-lg text-white transition-all duration-300 transform translate-x-full`;
                
                const bgColor = type === 'success' ? 'bg-green-500' : 
                               type === 'error' ? 'bg-red-500' : 
                               'bg-blue-500';
                               
                toast.classList.add(bgColor);
                toast.textContent = message;
                
                document.body.appendChild(toast);
                
                // Animate in
                setTimeout(() => {
                    toast.classList.remove('translate-x-full');
                }, 10);
                
                // Animate out and remove
                setTimeout(() => {
                    toast.classList.add('translate-x-full');
                    setTimeout(() => {
                        document.body.removeChild(toast);
                    }, 300);
                }, 3000);
            }
        }

        // Initialize notification system
        let notificationSystem;
        document.addEventListener('DOMContentLoaded', function() {
            notificationSystem = new NotificationSystem();
        });
    </script>
</body>
</html>