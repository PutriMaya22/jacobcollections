<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'JACOBCOLLECTIONS')</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">

    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: #F3F3F3;
            margin: 0;
            padding: 0;
        }

        .sidebar-logo {
            font-size: 1.6rem;
            font-weight: 700;
            color: #4A8692;
            margin-bottom: .5rem;
        }

        .sidebar-user {
            color: #2196b6;
            font-size: 0.9rem;
            margin-bottom: 2rem;
        }

        .sidebar-link {
            display: flex;
            align-items: center;
            gap: .8rem;
            padding: .8rem 1.2rem;
            color: #787878;
            text-decoration: none;
            border-radius: .5rem;
            margin-bottom: .3rem;
            transition: .2s;
        }

        .sidebar-link:hover,
        .sidebar-link.active {
            background: #E6FAFD;
            color: #2196b6;
        }
    </style>
</head>

<body>
<div class="flex min-h-screen">

    {{-- ================= SIDEBAR ================= --}}
    <aside class="w-[230px] bg-white shadow-lg flex flex-col justify-between p-4">

        <div>

            {{-- LOGO --}}
            <div class="sidebar-logo text-center">
                JACOBCOLLECTIONS
            </div>

            {{-- ROLE DISPLAY --}}
            <div class="sidebar-user text-center">
                @auth
                    {{ ucfirst(Auth::user()->role) }}
                @else
                    Guest
                @endauth
            </div>

            {{-- ================= NAVIGATION ================= --}}
            <nav>
                @auth

                {{-- DASHBOARD --}}
                <a href="{{ route('dashboard') }}"
                   class="sidebar-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <i class="fas fa-th-large"></i> Dashboard
                </a>

                {{-- MENU UNTUK ADMIN & OWNER --}}
                @if(auth()->user()->role === 'admin' || auth()->user()->role === 'owner')

                    <a href="{{ route('data_barang.index') }}"
                       class="sidebar-link {{ request()->routeIs('data_barang.*') ? 'active' : '' }}">
                        <i class="fas fa-box"></i> Data Produk
                    </a>

                    <a href="{{ route('data_penjualan.index') }}"
                       class="sidebar-link {{ request()->routeIs('data_penjualan.*') ? 'active' : '' }}">
                        <i class="fas fa-chart-bar"></i> Data Penjualan
                    </a>

                    <a href="{{ route('prediksi') }}"
                       class="sidebar-link {{ request()->routeIs('prediksi*') ? 'active' : '' }}">
                        <i class="fas fa-chart-line"></i> Prediksi Penjualan
                    </a>

                @endif

                {{-- KHUSUS ADMIN SAJA --}}
                @if(auth()->user()->role === 'admin')

                    <a href="{{ route('users.index') }}"
                       class="sidebar-link {{ request()->routeIs('users.*') ? 'active' : '' }}">
                        <i class="fas fa-users"></i> User Management
                    </a>

                @endif

                @endauth
            </nav>
        </div>

        {{-- ================= PROFILE FOOTER ================= --}}
        <div>
            @auth
                <div class="flex items-center gap-3 p-3 bg-gray-100 rounded-lg mb-3">

                    <img
                        src="{{ Auth::user()->profile_picture 
                                ? asset('storage/' . Auth::user()->profile_picture) 
                                : 'https://ui-avatars.com/api/?name=' . urlencode(Auth::user()->name) }}"
                        class="w-10 h-10 rounded-full object-cover">

                    <div>
                        <div class="text-sm font-semibold">
                            {{ Auth::user()->name }}
                        </div>
                        <div class="text-xs text-gray-500">
                            {{ Auth::user()->email }}
                        </div>
                    </div>
                </div>

                <a href="{{ route('profile.edit') }}" class="sidebar-link">
                    <i class="fas fa-cog"></i> Setting
                </a>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="sidebar-link text-red-500">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </button>
                </form>
            @endauth
        </div>

    </aside>

    {{-- ================= MAIN CONTENT ================= --}}
    <main class="flex-1 p-6">

        {{-- Header hanya muncul kalau halaman set header-title --}}
        @hasSection('header-title')
            <div class="bg-white rounded-lg shadow-sm p-4 mb-6">
                <h2 class="text-lg font-semibold text-gray-700">
                    @yield('header-title')
                </h2>
            </div>
        @endif

        @yield('content')

    </main>

</div>
</body>
</html>