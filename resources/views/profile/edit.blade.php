@extends('layouts.app')

@php
use Illuminate\Support\Facades\Storage;
@endphp

@section('content')
<div class="py-12">
    <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">
        <!-- Profile Banner Section -->
        <div class="profile-banner relative rounded-2xl h-44 mb-12 overflow-hidden select-none" 
             @if(Auth::user()->banner_image && Storage::disk('public')->exists(Auth::user()->banner_image))
                 style="background-image: url('{{ asset('storage/' . Auth::user()->banner_image) }}'); background-size: cover; background-position: center;"
             @else
                 style="background: linear-gradient(90deg, #d6f2f2 0%, #eaf6ff 45%, #ffe5cf 100%);"
             @endif
             data-original-banner="{{ Auth::user()->banner_image && Storage::disk('public')->exists(Auth::user()->banner_image) ? asset('storage/' . Auth::user()->banner_image) : '' }}">

            <!-- Hidden file input for banner -->
            <input type="file" id="bannerInput" name="banner_image" accept="image/*" class="hidden">
            
            <!-- Avatar + text inside banner -->
            <div class="absolute inset-x-6 bottom-4 flex items-center gap-4 pr-16 z-10">
                <div class="relative">
                    <img id="profileAvatar" 
                         src="{{ Auth::user()->profile_picture && Storage::disk('public')->exists(Auth::user()->profile_picture) ? asset('storage/' . Auth::user()->profile_picture) : 'https://randomuser.me/api/portraits/men/32.jpg' }}" 
                         alt="Profile" 
                         class="w-24 h-24 rounded-full object-cover ring-4 ring-white shadow-lg"
                         onerror="this.src='https://randomuser.me/api/portraits/men/32.jpg'">
                    <button id="profileCameraBtn" type="button" class="absolute bottom-1 right-1 p-2 bg-teal-500 text-white rounded-full hover:bg-teal-600 shadow">
                        <i class="fas fa-camera text-xs"></i>
                    </button>
                </div>
                <div>
                    <h1 class="text-2xl font-semibold text-gray-800">Setting</h1>
                    <p class="text-sm text-gray-700">{{ Auth::user()->email ?? 'Hendrickmoseng@gmail.com' }}</p>
                </div>
            </div>
        </div>
        
        <!-- Profile header under banner -->
        <div class="flex items-center justify-between -mt-10 mb-6 pr-2 border-b border-gray-200 pb-2">
            <nav class="flex items-center gap-8">
                <button id="profileTab" class="py-2 px-1 border-b-2 border-teal-500 text-teal-500 font-medium transition-all cursor-pointer" data-tab="profile">
                    Profile
                </button>
                <button id="passwordTab" class="py-2 px-1 border-b-2 border-transparent text-gray-500 font-medium transition-all cursor-pointer hover:text-gray-700 hover:border-gray-300" data-tab="password">
                    Password
                </button>
            </nav>
            <div class="flex gap-2 mr-2">
                <button id="cancelBtn" type="button" class="px-4 py-1.5 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200 transition">Cancel</button>
                <button id="saveBtn" type="button" class="px-4 py-1.5 bg-teal-600 text-white rounded-md hover:bg-teal-700 transition">Save</button>
            </div>
        </div>

        <!-- Profile Form -->
        <div id="profileContent" class="bg-white rounded-lg shadow-sm border p-8 transition-all">
            @if (session('status') === 'profile-updated')
                <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg">
                    <div class="flex items-center">
                        <i class="fas fa-check-circle text-green-500 mr-2"></i>
                        <p class="text-green-700">Profile berhasil diperbarui!</p>
                    </div>
                </div>
            @endif
            
            <div class="mb-8">
                <h2 class="text-2xl font-bold text-gray-800 mb-2">Profile</h2>
                <p class="text-gray-600">Update your photo and personal details.</p>
            </div>

            <form id="profileUpdateForm" method="post" action="{{ route('profile.update') }}" class="space-y-6" enctype="multipart/form-data">
                @csrf
                @method('patch')
                <input type="hidden" name="email" value="{{ old('email', $user->email) }}">

                <!-- Username -->
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Username</label>
                    <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-transparent transition-all"
                           placeholder="Enter your username">
                    @error('name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Phone Number -->
                <div>
                    <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">Phone Number</label>
                    <div class="flex">
                        <div class="flex items-center px-3 py-2 border border-r-0 border-gray-300 rounded-l-lg bg-gray-50 text-gray-700 font-medium">
                            <img src="https://flagcdn.com/w20/id.png" alt="ID" class="w-5 h-3 mr-2">
                            <span>+62</span>
                        </div>
                        <input type="tel" id="phone" name="phone" value="{{ old('phone', Auth::user()->phone) }}"
                               class="flex-1 px-3 py-2 border border-gray-300 rounded-r-lg focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-transparent transition-all"
                               placeholder="Enter phone number">
                    </div>
                </div>

                <!-- Contact Email -->
                <div>
                    <label for="contact_email" class="block text-sm font-medium text-gray-700 mb-2">Contact Email</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-envelope text-gray-400"></i>
                        </div>
                        <input type="email" id="contact_email" name="contact_email" value="{{ old('contact_email', Auth::user()->contact_email ?? Auth::user()->email) }}"
                               class="w-full pl-10 px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-transparent transition-all"
                               placeholder="Enter contact email">
                    </div>
                </div>
            </form>
        </div>

        <!-- Password Form - TANPA OTP -->
        <div id="passwordContent" class="bg-white rounded-lg shadow-sm border p-8 transition-all hidden">
            @if (session('status') === 'password-updated')
                <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg">
                    <div class="flex items-center">
                        <i class="fas fa-check-circle text-green-500 mr-2"></i>
                        <p class="text-green-700">Password berhasil diperbarui!</p>
                    </div>
                </div>
            @endif
            
            @if ($errors->any())
                <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg">
                    <div class="flex items-center">
                        <i class="fas fa-exclamation-circle text-red-500 mr-2"></i>
                        <p class="text-red-700">Terjadi kesalahan:</p>
                    </div>
                    <ul class="mt-2 list-disc list-inside text-sm text-red-600">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            
            <div class="mb-8">
                <h2 class="text-2xl font-bold text-gray-800 mb-2">Password</h2>
                <p class="text-gray-600">Update your password to keep your account secure.</p>
            </div>

            <form id="passwordUpdateForm" method="POST" action="{{ route('profile.password.update') }}" class="space-y-6">
                @csrf
                @method('put')

                <!-- Current Password -->
                <div>
                    <label for="current_password" class="block text-sm font-medium text-gray-700 mb-2">Current Password</label>
                    <input type="password" id="current_password" name="current_password" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-transparent transition-all"
                           placeholder="Enter current password">
                </div>

                <!-- New Password -->
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-2">New Password</label>
                    <input type="password" id="password" name="password" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-transparent transition-all"
                           placeholder="Enter new password (minimal 8 karakter)">
                </div>

                <!-- Confirm Password -->
                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-2">Confirm New Password</label>
                    <input type="password" id="password_confirmation" name="password_confirmation" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-transparent transition-all"
                           placeholder="Confirm new password">
                </div>
            </form>
        </div>

        <!-- Delete Account Section -->
        <div class="bg-white rounded-lg shadow-sm border p-8 transition-all hover:shadow-md">
            <div class="flex justify-between items-center">
                <div>
                    <h3 class="text-lg font-medium text-gray-900">Delete Account</h3>
                    <p class="text-sm text-gray-600">Once your account is deleted, all of its resources and data will be permanently deleted.</p>
                </div>
                <button id="deleteAccountBtn" class="px-4 py-2 text-red-600 hover:text-red-800 font-medium hover:bg-red-50 rounded-lg transition-all">
                    Delete Account
                </button>
            </div>
        </div>

        <!-- Hidden Delete Account Form -->
        <form id="deleteAccountForm" method="POST" action="{{ route('profile.destroy') }}" class="hidden">
            @csrf
            @method('DELETE')
            <input type="password" name="password" id="deletePassword" class="hidden">
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // ========== TAB FUNCTIONALITY ==========
        const profileTab = document.getElementById('profileTab');
        const passwordTab = document.getElementById('passwordTab');
        const profileContent = document.getElementById('profileContent');
        const passwordContent = document.getElementById('passwordContent');
        const saveBtn = document.getElementById('saveBtn');
        const cancelBtn = document.getElementById('cancelBtn');

        function switchTab(tabName) {
            profileContent.classList.add('hidden');
            passwordContent.classList.add('hidden');
            
            profileTab.classList.remove('border-teal-500', 'text-teal-500');
            profileTab.classList.add('border-transparent', 'text-gray-500');
            passwordTab.classList.remove('border-teal-500', 'text-teal-500');
            passwordTab.classList.add('border-transparent', 'text-gray-500');
            
            if (tabName === 'profile') {
                profileContent.classList.remove('hidden');
                profileTab.classList.remove('border-transparent', 'text-gray-500');
                profileTab.classList.add('border-teal-500', 'text-teal-500');
            } else {
                passwordContent.classList.remove('hidden');
                passwordTab.classList.remove('border-transparent', 'text-gray-500');
                passwordTab.classList.add('border-teal-500', 'text-teal-500');
            }
        }

        profileTab.addEventListener('click', () => switchTab('profile'));
        passwordTab.addEventListener('click', () => switchTab('password'));

        // Open password tab if session says so
        @if (session('open_tab') === 'password')
            switchTab('password');
        @endif

        // ========== PROFILE PICTURE HANDLING ==========
        const editProfileBtn = document.getElementById('editProfileBtn');
        const deleteProfileBtn = document.getElementById('deleteProfileBtn');
        const profileImage = document.getElementById('profileAvatar');
        const profileInput = document.getElementById('profileInput');
        const deleteProfileFlag = document.getElementById('deleteProfileFlag');

        if (editProfileBtn) {
            editProfileBtn.addEventListener('click', () => profileInput.click());
        }

        if (deleteProfileBtn) {
            deleteProfileBtn.addEventListener('click', () => {
                Swal.fire({
                    title: 'Hapus Foto Profil?',
                    text: 'Foto profil Anda akan dihapus.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, hapus',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        deleteProfileFlag.value = '1';
                        profileImage.src = 'https://randomuser.me/api/portraits/men/32.jpg';
                        Swal.fire('Terhapus', 'Foto profil telah dihapus', 'success');
                    }
                });
            });
        }

        if (profileInput) {
            profileInput.addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        profileImage.src = e.target.result;
                        deleteProfileFlag.value = '0';
                    };
                    reader.readAsDataURL(file);
                }
            });
        }

        // ========== BANNER HANDLING ==========
        const bannerCameraBtn = document.getElementById('bannerCameraBtn');
        const bannerInput = document.getElementById('bannerInput');
        const bannerEl = document.querySelector('.profile-banner');

        if (bannerCameraBtn && bannerInput) {
            bannerCameraBtn.addEventListener('click', () => bannerInput.click());
            
            bannerInput.addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        bannerEl.style.backgroundImage = `url(${e.target.result})`;
                        bannerEl.style.backgroundSize = 'cover';
                        bannerEl.style.backgroundPosition = 'center';
                    };
                    reader.readAsDataURL(file);
                }
            });
        }

        // ========== SAVE BUTTON ==========
        if (saveBtn) {
            saveBtn.addEventListener('click', function() {
                const isProfileActive = !profileContent.classList.contains('hidden');
                
                if (isProfileActive) {
                    // Submit profile form
                    Swal.fire({
                        title: 'Simpan perubahan?',
                        text: 'Apakah Anda yakin ingin menyimpan perubahan profil?',
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonText: 'Ya, simpan',
                        cancelButtonText: 'Batal'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            const form = document.getElementById('profileUpdateForm');
                            if (bannerInput && !form.contains(bannerInput)) {
                                form.appendChild(bannerInput);
                            }
                            form.submit();
                        }
                    });
                } else {
                    // Validate password form
                    const currentPassword = document.getElementById('current_password').value;
                    const newPassword = document.getElementById('password').value;
                    const confirmPassword = document.getElementById('password_confirmation').value;
                    
                    if (!currentPassword) {
                        Swal.fire('Error', 'Password saat ini wajib diisi', 'error');
                        return;
                    }
                    
                    if (newPassword.length < 8) {
                        Swal.fire('Error', 'Password baru minimal 8 karakter', 'error');
                        return;
                    }
                    
                    if (newPassword !== confirmPassword) {
                        Swal.fire('Error', 'Konfirmasi password baru tidak sesuai', 'error');
                        return;
                    }
                    
                    Swal.fire({
                        title: 'Update Password?',
                        text: 'Apakah Anda yakin ingin mengubah password?',
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonText: 'Ya, update',
                        cancelButtonText: 'Batal'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            document.getElementById('passwordUpdateForm').submit();
                        }
                    });
                }
            });
        }

        // ========== CANCEL BUTTON ==========
        const originalData = {
            name: document.getElementById('name')?.value || '',
            phone: document.getElementById('phone')?.value || '',
            contact_email: document.getElementById('contact_email')?.value || '',
            profileSrc: profileImage?.src || '',
            bannerBg: bannerEl?.style.backgroundImage || '',
            originalBanner: bannerEl?.getAttribute('data-original-banner') || ''
        };

        if (cancelBtn) {
            cancelBtn.addEventListener('click', function() {
                Swal.fire({
                    title: 'Batalkan perubahan?',
                    text: 'Semua perubahan yang belum disimpan akan hilang.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, batalkan',
                    cancelButtonText: 'Kembali'
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Reset form values
                        if (document.getElementById('name')) document.getElementById('name').value = originalData.name;
                        if (document.getElementById('phone')) document.getElementById('phone').value = originalData.phone;
                        if (document.getElementById('contact_email')) document.getElementById('contact_email').value = originalData.contact_email;
                        
                        // Reset profile picture
                        if (profileImage) profileImage.src = originalData.profileSrc;
                        if (deleteProfileFlag) deleteProfileFlag.value = '0';
                        
                        // Reset banner
                        if (bannerEl) {
                            if (originalData.originalBanner) {
                                bannerEl.style.backgroundImage = `url('${originalData.originalBanner}')`;
                                bannerEl.style.backgroundSize = 'cover';
                                bannerEl.style.backgroundPosition = 'center';
                            } else {
                                bannerEl.style.backgroundImage = 'linear-gradient(90deg, #d6f2f2 0%, #eaf6ff 45%, #ffe5cf 100%)';
                            }
                        }
                        
                        // Clear file inputs
                        if (profileInput) profileInput.value = '';
                        if (bannerInput) bannerInput.value = '';
                        
                        // Reset password form
                        if (document.getElementById('current_password')) document.getElementById('current_password').value = '';
                        if (document.getElementById('password')) document.getElementById('password').value = '';
                        if (document.getElementById('password_confirmation')) document.getElementById('password_confirmation').value = '';
                        
                        Swal.fire('Perubahan dibatalkan', '', 'success');
                    }
                });
            });
        }

        // ========== DELETE ACCOUNT ==========
        const deleteAccountBtn = document.getElementById('deleteAccountBtn');
        if (deleteAccountBtn) {
            deleteAccountBtn.addEventListener('click', async function() {
                const { value: password } = await Swal.fire({
                    title: 'Hapus Akun',
                    text: "Masukkan password Anda untuk mengkonfirmasi penghapusan akun",
                    input: 'password',
                    inputPlaceholder: 'Masukkan password Anda',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Ya, Hapus Akun',
                    cancelButtonText: 'Batal'
                });

                if (password) {
                    document.getElementById('deletePassword').value = password;
                    document.getElementById('deleteAccountForm').submit();
                }
            });
        }
    });
</script>
@endsection