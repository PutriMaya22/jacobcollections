<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - ASTERA</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Stretch+Pro:wght@400&display=swap');
        body {
            font-family: 'Poppins', sans-serif;
            margin: 0;
            padding: 0;
            min-height: 100vh;
        }
        .main-container {
            display: flex;
            min-height: 100vh;
        }
        .left-section {
            flex: 1;
            background: #F3F3F3;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 2rem;
            color: white;
        }
        .right-section {
            flex: 1;
            background: white;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 2rem;
        }
        .astera-logo {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 3rem;
            font-family: 'Stretch Pro', sans-serif;
            letter-spacing: 0.05em;
            text-transform: uppercase;
            background: linear-gradient(135deg, #4A8692, #58C1D1);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            transition: all 0.3s ease;
        }
        
        .astera-logo:hover {
            transform: scale(1.05);
            filter: brightness(1.1);
        }
        .feature-cards-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            width: 100%;
            max-width: 320px;
            margin: 0 auto;
            gap: 1.5rem;
        }
        .feature-card {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            width: 100%;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            font-family: 'Poppins', sans-serif;
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
        }
        .feature-icon {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1rem;
        }
        .icon-pink {
            background: #fce4ec;
            color: #58C1D1;
        }
        .icon-blue {
            background: #e3f2fd;
            color: #1976d2;
        }
        .icon-outline {
            border: 2px dashed #9ca3af;
            background: transparent;
            color: #3b82f6;
        }
        .feature-text {
            font-family: 'Poppins', sans-serif;
            width: 100%;
        }
        .feature-title {
            font-family: 'Poppins', sans-serif;
            font-weight: 600;
            color: #374151;
            margin-bottom: 0.25rem;
        }
        .feature-subtitle {
            font-family: 'Poppins', sans-serif;
            font-weight: 400;
            color: #6b7280;
            font-size: 0.875rem;
        }
        .register-form {
            width: 100%;
            max-width: 400px;
        }
        .form-input {
            width: 100%;
            padding: 0.875rem 1rem;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            font-size: 0.875rem;
            transition: all 0.2s;
            position: relative;
            font-family: 'Poppins', sans-serif;
        }
        .form-input:focus {
            outline: none;
            border-color: #58C1D1;
            box-shadow: 0 0 0 3px rgba(88, 193, 209, 0.1);
        }
        .input-group {
            position: relative;
            margin-bottom: 1.5rem;
        }
        .input-icon {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: #58C1D1;
            z-index: 10;
        }
        .input-with-icon {
            padding-left: 3rem;
        }
        .password-toggle {
            position: absolute;
            right: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: #6b7280;
            cursor: pointer;
            z-index: 10;
        }
        .register-btn {
            width: 100%;
            background: #58C1D1;
            color: white;
            padding: 0.875rem 1rem;
            border: none;
            border-radius: 8px;
            font-weight: 500;
            font-size: 0.875rem;
            cursor: pointer;
            transition: background-color 0.2s;
            margin-bottom: 1.5rem;
            font-family: 'Poppins', sans-serif;
        }
        .register-btn:hover {
            background: #4a9ba8;
        }
        .google-btn {
            width: 100%;
            background: white;
            color: #374151;
            padding: 0.875rem 1rem;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            font-weight: 500;
            font-size: 0.875rem;
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            font-family: 'Poppins', sans-serif;
        }
        .google-btn:hover {
            background: #f9fafb;
        }
        .divider {
            display: flex;
            align-items: center;
            margin: 1.5rem 0;
        }
        .divider-line {
            flex: 1;
            height: 1px;
            background: #e5e7eb;
        }
        .divider-text {
            padding: 0 1rem;
            color: #6b7280;
            font-size: 0.875rem;
            font-family: 'Poppins', sans-serif;
        }
        .teal-text {
            color: #58C1D1;
        }
        .grey-text {
            color: #6b7280;
            font-family: 'Poppins', sans-serif;
        }
        .link-custom {
            color: #4A8692;
            text-decoration: none;
            font-size: 0.875rem;
            font-family: 'Poppins', sans-serif;
            font-weight: 600;
        }
        .link-custom:hover {
            text-decoration: underline;
        }
        .welcome-text {
            font-family: 'Poppins', sans-serif;
        }
        .welcome-halo {
            color: #4A8692;
            font-family: 'Poppins', sans-serif;
        }
        .welcome-subtitle {
            color: #787878;
            font-family: 'Poppins', sans-serif;
        }
        .signup-text {
            font-family: 'Poppins', sans-serif;
        }
        .signup-link {
            color: #498E9C;
            font-family: 'Poppins', sans-serif;
        }
        .signin-link {
            color: #4A8692;
            font-family: 'Poppins', sans-serif;
            font-weight: 600;
            text-decoration: none;
        }
        .signin-link:hover {
            text-decoration: underline;
        }
        .kenalin-text {
            color: #787878;
            font-family: 'Poppins', sans-serif;
            font-weight: 400;
        }
        .kenalin-astera {
            color: #4A8692;
            font-family: 'Poppins', sans-serif;
            font-weight: 400;
        }
        .description-text {
            color: #787878;
            font-family: 'Poppins', sans-serif;
            font-weight: 400;
        }
        @media (max-width: 768px) {
            .main-container {
                flex-direction: column;
            }
            .left-section {
                padding: 1rem;
            }
            .right-section {
                padding: 1rem;
            }
        }
    </style>
</head>
<body>
    <div class="main-container">
        <!-- Left Section - ASTERA Branding -->
        <div class="left-section">
            <div class="astera-logo">ASTERA</div>
            <div class="feature-cards-container">
                <div class="feature-card">
                    <div class="feature-icon icon-pink">
                        <i class="fas fa-layer-group fa-lg"></i>
                    </div>
                    <div class="feature-text">
                        <div class="feature-title">PRODUCT</div>
                        <div class="feature-subtitle">CATEGORY</div>
                    </div>
                </div>
                <div class="feature-card">
                    <div class="feature-icon icon-blue">
                        <i class="fas fa-chart-pie fa-lg"></i>
                    </div>
                    <div class="feature-text">
                        <div class="feature-subtitle">VISUAL DATA</div>
                    </div>
                </div>
                <div class="feature-card">
                    <div class="feature-icon icon-outline">
                        <i class="fas fa-plus fa-lg"></i>
                    </div>
                    <div class="feature-text">
                        <div class="feature-subtitle">Input-Output Products</div>
                    </div>
                </div>
            </div>
            <div class="text-center mt-8">
                <h2 class="text-xl font-bold mb-2">
                    <span class="kenalin-text">Kenalin kita</span> 
                    <span class="kenalin-astera">ASTERA</span>
                </h2>
                <p class="text-sm description-text">
                    Website penyedia inventaris barang untuk kebutuhan STI PLN UID Jakarta
                </p>
            </div>
        </div>
        <!-- Right Section - Register Form -->
        <div class="right-section">
            <div class="register-form">
                <div class="text-center mb-8">
                    <h1 class="text-2xl font-semibold welcome-text mb-2">
                        <span class="welcome-halo">Daftar</span> 
                        <span class="welcome-subtitle">Akun Baru</span>
                    </h1>
                    <p class="text-sm grey-text">Silahkan isi data untuk membuat akun baru.</p>
                </div>
                @if (session('status'))
                    <div class="mb-4 p-3 bg-green-50 border border-green-200 text-green-700 rounded-lg text-sm">
                        {{ session('status') }}
                    </div>
                @endif
                <form method="POST" action="{{ route('register') }}">
                    @csrf
                    <!-- Name Field -->
                    <div class="input-group">
                        <i class="fas fa-user input-icon"></i>
                        <input 
                            id="name" 
                            type="text" 
                            name="name" 
                            value="{{ old('name') }}"
                            class="form-input input-with-icon"
                            placeholder="Nama Lengkap"
                            required 
                            autofocus 
                            autocomplete="name"
                        >
                        @error('name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
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
                            placeholder="Password"
                            required 
                            autocomplete="new-password"
                        >
                        <span class="password-toggle" onclick="togglePassword()">
                            <i class="fas fa-eye" id="eye-icon"></i>
                        </span>
                        @error('password')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <!-- Password Confirmation Field -->
<div class="input-group">
    <input 
        id="password_confirmation" 
        type="password" 
        name="password_confirmation"
        class="form-input"
        placeholder="Konfirmasi Password"
        required 
        autocomplete="new-password"
    >
    <span class="password-toggle" onclick="toggleConfirmPassword()">
        <i class="fas fa-eye" id="confirm-eye-icon"></i>
    </span>
</div>
                    <!-- Register Button -->
                    <button type="submit" class="register-btn">
                        Daftar
                    </button>
                </form>
                <div class="divider">
                    <div class="divider-line"></div>
                    <span class="divider-text">atau</span>
                    <div class="divider-line"></div>
                </div>
                <!-- Google Register Button -->
                <button class="google-btn">
                    <img src="https://developers.google.com/identity/images/g-logo.png" alt="Google" class="w-5 h-5">
                    Google
                </button>
                <!-- Sign In Link -->
                <div class="text-center mt-6">
                    <p class="text-sm grey-text signup-text">
                        Sudah punya akun? 
                        <a href="{{ route('login') }}" class="signin-link font-medium">
                            Sign In
                        </a>
                    </p>
                </div>
            </div>
        </div>
    </div>
    @if(auth()->check() && auth()->user()->isAdmin())
    <div class="input-group">
        <i class="fas fa-user-tag input-icon"></i>
        <select id="role" name="role" class="form-input input-with-icon" required>
            <option value="user">User</option>
            <option value="user_operasional">User Operasional</option>
        </select>
    </div>
@endif
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
        function toggleConfirmPassword() {
    const confirmPasswordInput = document.getElementById('password_confirmation');
    const confirmEyeIcon = document.getElementById('confirm-eye-icon');
    if (confirmPasswordInput.type === 'password') {
        confirmPasswordInput.type = 'text';
        confirmEyeIcon.classList.remove('fa-eye');
        confirmEyeIcon.classList.add('fa-eye-slash');
    } else {
        confirmPasswordInput.type = 'password';
        confirmEyeIcon.classList.remove('fa-eye-slash');
        confirmEyeIcon.classList.add('fa-eye');
    }
}
    </script>
</body>
</html>
