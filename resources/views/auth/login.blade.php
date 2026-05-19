<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - JacobCollections</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
   <style>
        @import url('https://fonts.googleapis.com/css2?family=Stretch+Pro:wght@400&display=swap');

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --primary: #58C1D1;
            --primary-dark: #46a7b6;
            --secondary: #4A8692;
            --bg-left: #F3F3F3;
            --bg-right: #ffffff;
            --text-dark: #374151;
            --text-muted: #6b7280;
            --border: #e5e7eb;
            --shadow: 0 20px 45px rgba(0, 0, 0, 0.08);
        }

        body {
            font-family: 'Poppins', sans-serif;
            min-height: 100vh;
            background: #eef2f5;
        }

        .main-container {
            min-height: 100vh;
            display: flex;
            align-items: stretch;
        }

        /* LEFT */
        .left-section {
            flex: 1.15;
            background: var(--bg-left);
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 3rem;
        }

        .left-content {
            width: 100%;
            max-width: 760px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
        }

        .astera-logo {
            font-family: 'Stretch Pro', sans-serif;
            font-size: clamp(1.8rem, 3vw, 3rem);
            font-weight: 700;
            line-height: 1.3;
            letter-spacing: 0.03em;
            text-transform: uppercase;
            margin-bottom: 2.5rem;
            max-width: 760px;
            background: linear-gradient(135deg, #4A8692, #58C1D1);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            word-break: break-word;
        }

        .feature-cards-container {
            width: 100%;
            max-width: 700px;
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1.25rem;
        }

        .feature-card {
            background: white;
            border-radius: 18px;
            padding: 1.5rem 1.25rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.06);
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            min-height: 170px;
            justify-content: center;
            transition: transform 0.25s ease;
        }

        .feature-card:hover {
            transform: translateY(-4px);
        }

        .feature-icon {
            width: 58px;
            height: 58px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1rem;
            font-size: 1.1rem;
        }

        .icon-pink {
            background: #eefbff;
            color: #58C1D1;
        }

        .icon-blue {
            background: #e8f0ff;
            color: #1976d2;
        }

        .icon-outline {
            border: 2px dashed #9ca3af;
            background: transparent;
            color: #3b82f6;
        }

        .feature-title {
            font-weight: 700;
            color: var(--text-dark);
            margin-bottom: 0.35rem;
            font-size: 0.95rem;
        }

        .feature-subtitle {
            font-weight: 400;
            color: var(--text-muted);
            font-size: 0.9rem;
            line-height: 1.5;
        }

        /* RIGHT */
        .right-section {
            flex: 0.9;
            background: #f9fbfc;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 3rem;
        }

        .login-form {
            width: 100%;
            max-width: 470px;
        }

        .login-card {
            background: #ffffff;
            border-radius: 24px;
            box-shadow: var(--shadow);
            padding: 2.2rem;
        }

        .login-header {
            margin-bottom: 2rem;
            text-align: left;
        }

        .welcome-text {
            font-size: 1.8rem;
            font-weight: 700;
            line-height: 1.3;
            margin-bottom: 0.5rem;
        }

        .welcome-halo {
            color: var(--secondary);
        }

        .welcome-subtitle {
            color: #787878;
        }

        .grey-text {
            color: #6b7280;
            font-size: 0.95rem;
            line-height: 1.6;
        }

        .input-group {
            position: relative;
            margin-bottom: 1.25rem;
        }

        .input-icon {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--primary);
            z-index: 2;
        }

        .input-with-icon {
            padding-left: 3rem !important;
        }

        .password-toggle {
            position: absolute;
            right: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: #6b7280;
            cursor: pointer;
            z-index: 2;
        }

        .form-input {
            width: 100%;
            height: 54px;
            border: 1px solid var(--border);
            border-radius: 12px;
            font-size: 0.95rem;
            transition: all 0.2s ease;
            font-family: 'Poppins', sans-serif;
            padding: 0 1rem;
            background: #fff;
        }

        .form-input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(88, 193, 209, 0.12);
        }

        .login-btn {
            width: 100%;
            height: 54px;
            background: var(--primary);
            color: white;
            border: none;
            border-radius: 12px;
            font-weight: 600;
            font-size: 0.95rem;
            cursor: pointer;
            transition: all 0.2s ease;
            margin-top: 0.25rem;
            font-family: 'Poppins', sans-serif;
        }

        .login-btn:hover {
            background: var(--primary-dark);
        }

        .form-footer {
            display: flex;
            justify-content: flex-end;
            margin-bottom: 1rem;
        }

        .link-custom {
            color: var(--secondary);
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 600;
        }

        .link-custom:hover {
            text-decoration: underline;
        }

        .signup-text {
            margin-top: 1.25rem;
            text-align: center;
            color: var(--text-muted);
            font-size: 0.92rem;
        }

        .signup-link {
            color: var(--secondary);
            text-decoration: none;
            font-weight: 600;
        }

        .signup-link:hover {
            text-decoration: underline;
        }

        .session-status {
            margin-bottom: 1rem;
            padding: 0.9rem 1rem;
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            color: #15803d;
            border-radius: 12px;
            font-size: 0.9rem;
        }

        .error-text {
            margin-top: 0.45rem;
            font-size: 0.85rem;
            color: #dc2626;
        }

        @media (max-width: 1100px) {
            .main-container {
                flex-direction: column;
            }

            .left-section,
            .right-section {
                width: 100%;
                padding: 2rem 1.25rem;
            }

            .left-content {
                max-width: 100%;
            }

            .feature-cards-container {
                max-width: 100%;
            }

            .login-form {
                max-width: 600px;
            }
        }

        @media (max-width: 768px) {
            .astera-logo {
                font-size: 1.5rem;
                margin-bottom: 2rem;
            }

            .feature-cards-container {
                grid-template-columns: 1fr;
            }

            .feature-card {
                min-height: auto;
            }

            .login-card {
                padding: 1.5rem;
                border-radius: 18px;
            }

            .welcome-text {
                font-size: 1.45rem;
            }

            .form-input,
            .login-btn {
                height: 50px;
            }
        }
    </style>
</head>
<body>
    <div class="main-container">
       <!-- Left Section -->
<div class="left-section">
    <div class="left-content">
        <div class="astera-logo">
            SISTEM PREDIKSI PENJUALAN TOKO JACOBCOLLECTIONS
        </div>

        <div class="feature-cards-container">
            <!-- INPUT - OUTPUT (Prediksi) - PERTAMA -->
            <div class="feature-card">
                <div class="feature-icon icon-outline">
                    <i class="fas fa-plus"></i>
                </div>
                <div class="feature-title">INPUT - OUTPUT</div>
                <div class="feature-subtitle">Prediksi Penjualan</div>
            </div>

            <!-- PRODUCT - Rekomendasi Produk - KEDUA -->
            <div class="feature-card">
                <div class="feature-icon icon-pink">
                    <i class="fas fa-layer-group"></i>
                </div>
                <div class="feature-title">PRODUCT</div>
                <div class="feature-subtitle">Rekomendasi Produk</div>
            </div>

            <!-- VISUAL DATA - KETIGA -->
            <div class="feature-card">
                <div class="feature-icon icon-blue">
                    <i class="fas fa-chart-pie"></i>
                </div>
                <div class="feature-title">VISUAL</div>
                <div class="feature-subtitle">DATA</div>
            </div>
        </div>
    </div>
</div>

        <!-- Right Section - Login Form -->
        <div class="right-section">
            <div class="login-form">
                <!-- Header -->
                <div class="text-center mb-8">
                    <h1 class="text-2xl font-semibold welcome-text mb-2">
                        <span class="welcome-halo">Halo,</span> 
                        <span class="welcome-subtitle">Selamat Datang Kembali!</span>
                    </h1>
                    <p class="text-sm grey-text">Silahkan Login untuk menggunakan akses anda.</p>
                </div>

                <!-- Session Status -->
                @if (session('status'))
                    <div class="mb-4 p-3 bg-green-50 border border-green-200 text-green-700 rounded-lg text-sm">
                        {{ session('status') }}
                    </div>
                @endif

                <!-- Login Form -->
                <form method="POST" action="{{ route('login') }}">
                    @csrf
                    
                    <!-- Email Field -->
                    <div class="input-group">
                        <i class="fas fa-envelope input-icon"></i>
                        <input 
                            id="email" 
                            type="email" 
                            name="email" 
                            value="{{ old('email') }}"
                            class="form-input input-with-icon"
                            placeholder="you@gmail.com"
                            required 
                            autofocus 
                            autocomplete="username"
                        >
                        @error('email')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Password Field -->
                    <div class="input-group">
                        <input 
                            id="password" 
                            type="password" 
                            name="password"
                            class="form-input"
                            placeholder="****"
                            required 
                            autocomplete="current-password"
                        >
                        <span class="password-toggle" onclick="togglePassword()">
                            <i class="fas fa-eye" id="eye-icon"></i>
                        </span>
                        @error('password')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>


                    <!-- Login Button -->
                    <button type="submit" class="login-btn">
                        Login
                    </button>
                </form>

                
            </div>
        </div>
    </div>

    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const eyeIcon = document.getElementById('eye-icon');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                eyeIcon.classList.remove('fa-eye');
                eyeIcon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                eyeIcon.classList.remove('fa-eye-slash');
                eyeIcon.classList.add('fa-eye');
            }
        }
    </script>
</body>
</html>