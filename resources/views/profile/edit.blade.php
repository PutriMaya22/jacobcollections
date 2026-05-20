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
            <!-- Camera Icon for Banner -->
            <button id="bannerCameraBtn" type="button" class="absolute bottom-4 right-4 p-2 bg-white/80 border border-white rounded-lg hover:bg-white transition-all z-30 cursor-pointer focus:outline-none">
                <i class="fas fa-camera text-gray-600"></i>
            </button>
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
            <!-- Success Message -->
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
                <!-- Debug info -->
                <div class="hidden">
                    <p>Form action: {{ route('profile.update') }}</p>
                    <p>CSRF token: {{ csrf_token() }}</p>
                </div>

                <!-- Username -->
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Username</label>
                    <input type="text" id="name" name="name" value="{{ old('name', $user->name ?? 'Hendrick Moseng') }}" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-transparent transition-all"
                           placeholder="Enter your username">
                    @error('name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Profile Picture -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Profile Picture</label>
                    <div class="flex items-center gap-3">
                        <button id="editProfileBtn" type="button" class="px-4 py-2 bg-teal-500 text-white rounded-lg hover:bg-teal-600 transition-all">Upload New</button>
                        <button id="deleteProfileBtn" type="button" class="px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 transition-all">Remove</button>
                    </div>
                    <input type="file" id="profileInput" name="profile_picture" accept="image/*" class="hidden">
                    <input type="hidden" id="deleteProfileFlag" name="delete_profile_picture" value="0">
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

                <!-- Save Button -->
                <div class="flex justify-end pt-6">
                    <button type="submit" class="px-6 py-2 bg-teal-500 text-white rounded-lg hover:bg-teal-600 transition-all hover:-translate-y-0.5 shadow-lg">
                        Save Changes
                    </button>
                </div>
            </form>
        </div>

        <!-- Password Form -->
        <div id="passwordContent" class="bg-white rounded-lg shadow-sm border p-8 transition-all hidden">
            <!-- Success Message -->
            @if (session('status') === 'password-updated')
                <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg">
                    <div class="flex items-center">
                        <i class="fas fa-check-circle text-green-500 mr-2"></i>
                        <p class="text-green-700">Password berhasil diperbarui!</p>
                    </div>
                </div>
            @endif
            
            <div class="mb-8">
                <h2 class="text-2xl font-bold text-gray-800 mb-2">Password</h2>
                <p class="text-gray-600">Update your password to keep your account secure.</p>
            </div>

            <!-- Hidden standalone form to send OTP without nesting -->
            <form id="sendOtpForm" method="POST" action="{{ route('password.send-otp') }}" class="hidden">
                @csrf
            </form>

            <form id="passwordUpdateForm" method="post" action="{{ route('password.update') }}" class="space-y-6">
                @csrf
                @method('put')

                <!-- Current Password -->
                <div>
                    <label for="current_password" class="block text-sm font-medium text-gray-700 mb-2">Current Password</label>
                    <input type="password" id="current_password" name="current_password" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-transparent transition-all"
                           placeholder="Enter current password">
                    @error('current_password')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- New Password -->
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-2">New Password</label>
                    <input type="password" id="password" name="password" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-transparent transition-all"
                           placeholder="Enter new password">
                    @error('password')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Confirm Password -->
                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-2">Confirm New Password</label>
                    <input type="password" id="password_confirmation" name="password_confirmation" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-transparent transition-all"
                           placeholder="Confirm new password">
                </div>

                <!-- OTP Code -->
                <div>
                    <label for="otp_code" class="block text-sm font-medium text-gray-700 mb-2">OTP Code</label>
                    <div class="flex gap-3">
                        <input type="text" id="otp_code" name="otp_code" maxlength="4"
                               class="w-32 px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-transparent transition-all"
                               placeholder="1234">
                        <button id="sendOtpButton" type="submit" form="sendOtpForm" class="px-4 py-2 bg-gray-200 rounded-lg hover:bg-gray-300">Kirim OTP</button>
                    </div>
                    @if (session('status') && session('open_tab') === 'password')
                        <p class="mt-2 text-sm text-green-600">{{ session('status') }}</p>
                    @endif
                    @if (session('error') && session('open_tab') === 'password')
                        <p class="mt-2 text-sm text-red-600">{{ session('error') }}</p>
                    @endif
                    @error('otp_code', 'updatePassword')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Save Button -->
                <div class="flex justify-end pt-6">
                    <button type="submit" class="px-6 py-2 bg-teal-500 text-white rounded-lg hover:bg-teal-600 transition-all hover:-translate-y-0.5 shadow-lg">
                        Update Password
                    </button>
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
    // Tab functionality
    document.addEventListener('DOMContentLoaded', function() {
        console.log('Profile page loaded successfully');
        
        const profileTab = document.getElementById('profileTab');
        const passwordTab = document.getElementById('passwordTab');
        const profileContent = document.getElementById('profileContent');
        const passwordContent = document.getElementById('passwordContent');

        if (!profileTab || !passwordTab || !profileContent || !passwordContent) {
            console.error('Some elements not found:', { profileTab, passwordTab, profileContent, passwordContent });
            return;
        }

        function switchTab(tabName) {
            console.log('Switching to tab:', tabName);
            
            // Hide all content
            document.querySelectorAll('#profileContent, #passwordContent').forEach(content => {
                content.classList.add('hidden');
            });

            // Remove active class from all tabs
            document.querySelectorAll('#profileTab, #passwordTab').forEach(tab => {
                tab.classList.remove('border-teal-500', 'text-teal-500');
                tab.classList.add('border-transparent', 'text-gray-500');
            });

            // Show selected content and activate tab
            if (tabName === 'profile') {
                profileContent.classList.remove('hidden');
                profileTab.classList.remove('border-transparent', 'text-gray-500');
                profileTab.classList.add('border-teal-500', 'text-teal-500');
            } else if (tabName === 'password') {
                passwordContent.classList.remove('hidden');
                passwordTab.classList.remove('border-transparent', 'text-gray-500');
                passwordTab.classList.add('border-teal-500', 'text-teal-500');
            }
        }

        profileTab.addEventListener('click', () => switchTab('profile'));
        passwordTab.addEventListener('click', () => switchTab('password'));

        // If server indicates to open specific tab
        @if (session('open_tab') === 'password')
            switchTab('password');
        @endif

        // Profile picture edit functionality
        const editProfileBtn = document.getElementById('editProfileBtn');
        const deleteProfileBtn = document.getElementById('deleteProfileBtn');
        const profileImage = document.getElementById('profileImage') || document.getElementById('profileAvatar');
        const profileInput = document.getElementById('profileInput');
        
        console.log('Profile elements found:', {
            editProfileBtn: !!editProfileBtn,
            deleteProfileBtn: !!deleteProfileBtn,
            profileImage: !!profileImage,
            profileInput: !!profileInput
        });

        if (editProfileBtn) {
            editProfileBtn.addEventListener('click', function() {
                profileInput.click();
            });
        }

        if (deleteProfileBtn) {
            deleteProfileBtn.addEventListener('click', function() {
                if (confirm('Are you sure you want to delete your profile picture?')) {
                    const deleteFlag = document.getElementById('deleteProfileFlag');
                    if (deleteFlag) deleteFlag.value = '1';
                    profileImage.src = 'https://randomuser.me/api/portraits/men/32.jpg';
                }
            });
        }

        if (profileInput) {
            profileInput.addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (file) {
                    console.log('Profile file selected:', file.name, 'Size:', file.size);
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        profileImage.src = e.target.result;
                        console.log('Profile image preview updated');
                    };
                    reader.onerror = function() {
                        console.error('Error reading profile file');
                        Swal.fire({
                            title: 'Error',
                            text: 'Gagal membaca file profile picture. Silakan coba lagi.',
                            icon: 'error',
                            timer: 2000,
                            showConfirmButton: false
                        });
                    };
                    reader.readAsDataURL(file);
                }
            });
        }

        // Save and Cancel functionality
        const saveBtn = document.getElementById('saveBtn');
        const cancelBtn = document.getElementById('cancelBtn');
        
        console.log('Button elements found:', {
            saveBtn: !!saveBtn,
            cancelBtn: !!cancelBtn
        });

        if (saveBtn) {
            saveBtn.addEventListener('click', function() {
                const isProfileActive = !profileContent.classList.contains('hidden');
                console.log('Save button clicked, profile active:', isProfileActive);
                
                const title = isProfileActive ? 'Simpan perubahan profil?' : 'Simpan perubahan password?';
                const text = isProfileActive ? 'Perubahan data profil akan disimpan.' : 'Password akan diperbarui.';
                Swal.fire({
                    title: title,
                    text: text,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, simpan',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        if (isProfileActive) {
                            // Submit profile form with all data including files
                            const form = document.getElementById('profileUpdateForm');
                            if (form) {
                                console.log('Submitting profile form...');
                                
                                // Ensure all file inputs are properly included
                                const profileInput = document.getElementById('profileInput');
                                const bannerInput = document.getElementById('bannerInput');
                                
                                console.log('Profile input files:', profileInput?.files?.length || 0);
                                console.log('Banner input files:', bannerInput?.files?.length || 0);
                                
                                // If profile picture was selected, ensure it's included
                                if (profileInput && profileInput.files.length > 0) {
                                    console.log('Profile picture selected:', profileInput.files[0].name);
                                }
                                
                                // If banner was selected, ensure it's included
                                if (bannerInput && bannerInput.files.length > 0) {
                                    console.log('Banner selected:', bannerInput.files[0].name);
                                }
                                
                                // Ensure banner input is in the form before submitting
                                if (bannerInput && !form.contains(bannerInput)) {
                                    console.log('Adding banner input to form');
                                    form.appendChild(bannerInput);
                                }
                                
                                // Log form data before submission
                                const formData = new FormData(form);
                                console.log('Form data before submission:');
                                for (let [key, value] of formData.entries()) {
                                    console.log(key, ':', value);
                                }
                                
                                console.log('Submitting form...');
                                
                                // Add error handling for form submission
                                try {
                                    form.submit();
                                } catch (error) {
                                    console.error('Error submitting form:', error);
                                    Swal.fire({
                                        title: 'Error',
                                        text: 'Gagal mengirim form. Silakan coba lagi.',
                                        icon: 'error',
                                        timer: 2000,
                                        showConfirmButton: false
                                    });
                                }
                            } else {
                                console.error('Profile form not found!');
                            }
                        } else {
                            const pwdForm = document.getElementById('passwordUpdateForm');
                            if (pwdForm) {
                                console.log('Submitting password form...');
                                pwdForm.submit();
                            } else {
                                console.error('Password form not found!');
                            }
                        }
                    }
                });
            });
        }

        // Capture original state for cancel
        const bannerEl = document.querySelector('.profile-banner');
        const originalBannerBg = bannerEl ? bannerEl.style.backgroundImage : '';
        const originalBannerData = bannerEl ? bannerEl.getAttribute('data-original-banner') : '';
        const nameInput = document.getElementById('name');
        const phoneInput = document.getElementById('phone');
        const contactEmailInput = document.getElementById('contact_email');
        const deleteFlagEl = document.getElementById('deleteProfileFlag');
        const originalName = nameInput ? nameInput.value : '';
        const originalPhone = phoneInput ? phoneInput.value : '';
        const originalContactEmail = contactEmailInput ? contactEmailInput.value : '';
        const originalProfileSrc = profileImage ? profileImage.src : '';

        if (cancelBtn) {
            cancelBtn.addEventListener('click', function() {
                console.log('Cancel button clicked');
                Swal.fire({
                    title: 'Batalkan perubahan?',
                    text: 'Semua perubahan yang belum disimpan akan hilang.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, batalkan',
                    cancelButtonText: 'Kembali'
                }).then((result) => {
                    if (result.isConfirmed) {
                        console.log('Cancelling changes...');
                        
                        // Revert inputs
                        if (nameInput) {
                            nameInput.value = originalName;
                            console.log('Reverted name to:', originalName);
                        }
                        if (phoneInput) {
                            phoneInput.value = originalPhone;
                            console.log('Reverted phone to:', originalPhone);
                        }
                        if (contactEmailInput) {
                            contactEmailInput.value = originalContactEmail;
                            console.log('Reverted contact email to:', originalContactEmail);
                        }
                        
                        // Revert profile image and flags
                        if (deleteFlagEl) {
                            deleteFlagEl.value = '0';
                            console.log('Reset delete profile flag');
                        }
                        if (profileImage) {
                            profileImage.src = originalProfileSrc;
                            console.log('Reverted profile image');
                        }
                        
                        // Clear file inputs
                        const profileFile = document.getElementById('profileInput');
                        if (profileFile) {
                            profileFile.value = '';
                            console.log('Cleared profile file input');
                        }
                        
                        // Revert banner preview to original state
                        if (bannerEl) {
                            if (originalBannerData && originalBannerData !== '') {
                                bannerEl.style.backgroundImage = `url('${originalBannerData}')`;
                                bannerEl.style.backgroundSize = 'cover';
                                bannerEl.style.backgroundPosition = 'center';
                                console.log('Reverted banner to original:', originalBannerData);
                            } else {
                                // Reset to default gradient if no original banner
                                bannerEl.style.backgroundImage = 'linear-gradient(90deg, #d6f2f2 0%, #eaf6ff 45%, #ffe5cf 100%)';
                                console.log('Reset banner to default gradient');
                            }
                        }
                        
                        // Clear banner file input
                        if (bannerInput) {
                            bannerInput.value = '';
                            console.log('Cleared banner file input');
                        }
                        
                        // Feedback
                        Swal.fire({
                            title: 'Perubahan dibatalkan',
                            icon: 'success',
                            timer: 1200,
                            showConfirmButton: false
                        });
                    }
                });
            });
        }

        // Delete Account Confirmation
        const deleteAccountBtn = document.getElementById('deleteAccountBtn');
        if (deleteAccountBtn) {
            deleteAccountBtn.addEventListener('click', async function() {
                const { value: password } = await Swal.fire({
                    title: 'Konfirmasi Penghapusan Akun',
                    text: "Masukkan password Anda untuk mengkonfirmasi penghapusan akun",
                    input: 'password',
                    inputPlaceholder: 'Masukkan password Anda',
                    inputAttributes: {
                        autocapitalize: 'off',
                        autocorrect: 'off'
                    },
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Ya, Hapus Akun Saya',
                    cancelButtonText: 'Batal',
                    reverseButtons: true,
                    focusCancel: true,
                    validationMessage: 'Password wajib diisi'
                });

                if (password) {
                    // Set password value in hidden input
                    document.getElementById('deletePassword').value = password;
                    
                    // Submit the form
                    document.getElementById('deleteAccountForm').submit();
                }
            });
        }

        // Banner image change functionality
        const bannerCameraBtn = document.getElementById('bannerCameraBtn');
        const bannerInput = document.getElementById('bannerInput');
        const profileCameraBtn = document.getElementById('profileCameraBtn');
        
        console.log('Banner elements found:', {
            bannerCameraBtn: !!bannerCameraBtn,
            bannerInput: !!bannerInput,
            profileCameraBtn: !!profileCameraBtn
        });

        if (bannerCameraBtn) {
            bannerCameraBtn.addEventListener('click', function() {
                bannerInput.click();
            });
        }

        if (profileCameraBtn) {
            profileCameraBtn.addEventListener('click', function() {
                profileInput.click();
            });
        }

        if (bannerInput) {
            bannerInput.addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (file) {
                    console.log('Banner file selected:', file.name, 'Size:', file.size);
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const banner = document.querySelector('.profile-banner') || document.querySelector('.rounded-2xl.h-44');
                        if (banner) {
                            banner.style.backgroundImage = `url(${e.target.result})`;
                            banner.style.backgroundSize = 'cover';
                            banner.style.backgroundPosition = 'center';
                            console.log('Banner preview updated');
                        }
                    };
                    reader.onerror = function() {
                        console.error('Error reading banner file');
                        Swal.fire({
                            title: 'Error',
                            text: 'Gagal membaca file banner. Silakan coba lagi.',
                            icon: 'error',
                            timer: 2000,
                            showConfirmButton: false
                        });
                    };
                    reader.readAsDataURL(file);
                }
            });
        }
    });
</script>
@endsection