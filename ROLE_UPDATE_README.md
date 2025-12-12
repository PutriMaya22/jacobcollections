# Update Role dari user_input menjadi user

## Deskripsi Perubahan

Telah dilakukan perubahan role dari `user_input` menjadi `user` untuk menyederhanakan sistem role dan memperbaiki error validasi.

## Perubahan yang Dilakukan

### 1. Database Migration
- **File**: `database/migrations/2025_08_13_173128_update_user_role_enum.php`
- **Perubahan**: Mengubah enum role dari `['admin', 'user_input', 'user_operasional']` menjadi `['admin', 'user', 'user_operasional']`
- **Default**: Berubah dari `user_input` menjadi `user`

### 2. User Model
- **File**: `app/Models/User.php`
- **Perubahan**: 
  - Method `isUserInput()` → `isUser()`
  - Logic: `$this->role === 'user_input'` → `$this->role === 'user'`

### 3. UserController
- **File**: `app/Http/Controllers/UserController.php`
- **Perubahan**: Validasi role di method `store()` dan `update()`
  - Dari: `'role' => 'required|in:admin,user_input,user_operasional'`
  - Menjadi: `'role' => 'required|in:admin,user,user_operasional'`

### 4. RegisterController
- **File**: `app/Http/Controllers/Auth/RegisteredUserController.php`
- **Perubahan**: Default role untuk user baru
  - Dari: `'role' => 'user_input'`
  - Menjadi: `'role' => 'user'`

### 5. RegisterController (Lama)
- **File**: `app/Http/Controllers/RegisterController.php`
- **Perubahan**: Default role untuk user baru
  - Dari: `'role' => 'user_input'`
  - Menjadi: `'role' => 'user'`

### 6. Views
- **File**: `resources/views/users/create.blade.php`
  - Option: `value="user_input"` → `value="user"`
  - Label: `User Input` → `User`

- **File**: `resources/views/users/edit.blade.php`
  - Option: `value="user"` (sudah benar)
  - Label: `User` (sudah benar)

- **File**: `resources/views/auth/register.blade.php`
  - Option: `value="user_input"` → `value="user"`
  - Label: `User Input` → `User`

### 7. Middleware
- **File**: `app/Http/Middleware/AdminOrUserInputMiddleware.php` → `AdminOrUserMiddleware.php`
- **Perubahan**: 
  - Nama class: `AdminOrUserInputMiddleware` → `AdminOrUserMiddleware`
  - Logic: `isUserInput()` → `isUser()`

- **File**: `app/Http/Middleware/UserInputMiddleware.php`
- **Status**: Dihapus (tidak terpakai)

- **File**: `app/Http/Middleware/UserMiddleware.php`
- **Status**: Dibuat baru untuk role `user`

### 8. Kernel.php
- **File**: `app/Http/Kernel.php`
- **Perubahan**: Middleware aliases
  - `'user.input'` → `'user'`
  - `'admin_or_user_input'` → `'admin_or_user'`

### 9. Routes
- **File**: `routes/web.php`
- **Perubahan**: Middleware group
  - Dari: `['admin_or_user_input']`
  - Menjadi: `['admin_or_user']`

## Struktur Role Baru

```php
enum('role', ['admin', 'user', 'user_operasional'])
```

### Penjelasan Role:
1. **admin**: Administrator dengan akses penuh
2. **user**: User biasa dengan akses input data
3. **user_operasional**: User operasional dengan akses operasional

## Testing

### 1. Test Create User
```bash
# Buka halaman create user
# Pilih role "User" 
# Submit form
# Harus berhasil tanpa error validasi
```

### 2. Test Edit User
```bash
# Buka halaman edit user
# Ubah role dari "User Input" ke "User"
# Submit form
# Harus berhasil tanpa error validasi
```

### 3. Test Middleware
```bash
# Login sebagai user dengan role "user"
# Akses halaman yang memerlukan middleware admin_or_user
# Harus berhasil
```

## Troubleshooting

### Jika masih ada error "The selected role is invalid":

1. **Clear cache**:
```bash
php artisan route:clear
php artisan config:clear
php artisan cache:clear
```

2. **Check database**:
```sql
SELECT DISTINCT role FROM users;
-- Harus menampilkan: admin, user, user_operasional
```

3. **Check migration**:
```bash
php artisan migrate:status
-- Pastikan migration terakhir sudah dijalankan
```

### Jika ada user dengan role lama:

1. **Update manual di database**:
```sql
UPDATE users SET role = 'user' WHERE role = 'user_input';
```

2. **Atau jalankan migration ulang**:
```bash
php artisan migrate:rollback --step=1
php artisan migrate
```

## Rollback (Jika Perlu)

Jika ingin kembali ke role `user_input`:

1. **Rollback migration**:
```bash
php artisan migrate:rollback --step=1
```

2. **Restore file-file yang diubah** (gunakan git)

## Kesimpulan

Perubahan ini telah:
- ✅ Memperbaiki error validasi role
- ✅ Menyederhanakan sistem role
- ✅ Mempertahankan fungsionalitas yang ada
- ✅ Memperbaiki konsistensi naming

Sekarang sistem role menggunakan:
- `admin` untuk administrator
- `user` untuk user biasa (sebelumnya user_input)
- `user_operasional` untuk user operasional
