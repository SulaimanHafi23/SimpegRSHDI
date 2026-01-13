# Error Pages Documentation

## Overview
Sistem memiliki halaman error khusus yang user-friendly untuk berbagai jenis error HTTP.

## Error Pages

### 1. **403 - Access Denied (Akses Ditolak)**
- **File**: `resources/views/errors/403.blade.php`
- **Kapan Muncul**: 
  - User mencoba mengakses halaman tanpa permission yang sesuai
  - Middleware permission menolak akses
  - Authorization gate menolak akses
- **Fitur**:
  - Icon ban merah
  - Pesan error yang jelas
  - Tombol "Kembali" (history.back)
  - Tombol "Ke Dashboard"
  - Link kontak admin
  - Animated background

### 2. **404 - Not Found (Halaman Tidak Ditemukan)**
- **File**: `resources/views/errors/404.blade.php`
- **Kapan Muncul**: 
  - URL/Route tidak ditemukan
  - Resource tidak ada
- **Fitur**:
  - Icon search kuning
  - Pesan halaman tidak ditemukan
  - Tombol "Kembali"
  - Tombol "Ke Dashboard"
  - Quick links ke halaman populer
  - Animated background

### 3. **419 - Page Expired (CSRF Token Kadaluarsa)**
- **File**: `resources/views/errors/419.blade.php`
- **Kapan Muncul**: 
  - CSRF token expired
  - Session timeout
  - Form submission setelah lama tidak aktif
- **Fitur**:
  - Icon clock orange
  - Penjelasan kenapa terjadi
  - Tombol "Muat Ulang Halaman"
  - Tombol "Ke Dashboard"
  - Tips mencegah error ini
  - Animated background

### 4. **500 - Server Error (Kesalahan Server)**
- **File**: `resources/views/errors/500.blade.php`
- **Kapan Muncul**: 
  - Internal server error
  - Uncaught exceptions
  - Database errors
  - Code errors
- **Fitur**:
  - Icon warning merah
  - Pesan kesalahan server
  - Tombol "Muat Ulang"
  - Tombol "Ke Dashboard"
  - Email kontak admin
  - Debug info (jika APP_DEBUG=true)
  - Animated background

### 5. **503 - Service Unavailable (Maintenance Mode)**
- **File**: `resources/views/errors/503.blade.php`
- **Kapan Muncul**: 
  - Aplikasi dalam maintenance mode (`php artisan down`)
  - Server overload
- **Fitur**:
  - Icon tools biru (animated pulse)
  - Pesan maintenance
  - Progress bar animation
  - Info cards (Data Aman, Peningkatan, Optimasi)
  - Kontak darurat
  - Full standalone HTML (tidak perlu layout)

## Cara Menggunakan

### Memicu Error 403 (Access Denied)

#### Di Controller:
```php
// Method 1: abort()
abort(403, 'Anda tidak memiliki akses ke resource ini.');

// Method 2: Gate
if (Gate::denies('update-post', $post)) {
    abort(403);
}

// Method 3: Policy
$this->authorize('update', $post);
```

#### Di Middleware:
```php
// PermissionMiddleware sudah otomatis mengembalikan 403
if (!auth()->user()->hasPermissionTo($permission)) {
    abort(403, 'Anda tidak memiliki permission: ' . $permission);
}
```

#### Di Route:
```php
Route::get('/admin', function () {
    abort_if(!auth()->user()->isAdmin(), 403);
    // ...
});
```

#### Di Blade:
```php
@can('manage-users')
    <!-- Content -->
@else
    @php abort(403) @endphp
@endcan
```

### Maintenance Mode

#### Aktifkan:
```bash
# Basic
php artisan down

# Dengan pesan custom
php artisan down --message="Sedang upgrade database"

# Dengan waktu retry (detik)
php artisan down --retry=60

# Dengan secret untuk akses
php artisan down --secret="maintenance-bypass"
# Akses: https://example.com/maintenance-bypass
```

#### Nonaktifkan:
```bash
php artisan up
```

## Customization

### Mengubah Warna/Style
Edit file error yang sesuai dan ubah Tailwind classes:
- `bg-red-100` → background
- `text-red-600` → text color
- `border-red-200` → border

### Menambahkan Error Page Baru
1. Buat file di `resources/views/errors/{code}.blade.php`
2. Extend layout atau buat standalone
3. Tambahkan konten sesuai error type

### Custom Error Handler
Edit `app/Exceptions/Handler.php`:

```php
public function render($request, Throwable $e): Response
{
    // Custom 403 handling
    if ($e instanceof AuthorizationException) {
        return response()->view('errors.403', [
            'exception' => $e,
            'customMessage' => 'Custom message here'
        ], 403);
    }

    return parent::render($request, $e);
}
```

## Best Practices

1. **Always provide "Kembali" button** - User harus bisa kembali
2. **Clear error messages** - Jelaskan apa yang salah
3. **Actionable solutions** - Beri tahu user apa yang harus dilakukan
4. **Contact info** - Sediakan cara menghubungi admin/support
5. **Consistent design** - Gunakan style yang konsisten dengan aplikasi
6. **Avoid technical jargon** - Gunakan bahasa yang mudah dipahami
7. **Log errors properly** - Pastikan error di-log untuk debugging

## Testing Error Pages

### Local Testing:

#### 403:
```php
Route::get('/test-403', fn() => abort(403));
```

#### 404:
```
Akses URL yang tidak ada: /halaman-tidak-ada
```

#### 419:
1. Buka form
2. Tunggu > session lifetime (default 120 menit)
3. Submit form

#### 500:
```php
Route::get('/test-500', fn() => throw new \Exception('Test error'));
```

#### 503:
```bash
php artisan down
# Akses aplikasi
php artisan up
```

## Related Files

- `app/Exceptions/Handler.php` - Exception handler
- `app/Http/Middleware/PermissionMiddleware.php` - Permission checks
- `resources/views/errors/*.blade.php` - Error views
- `config/app.php` - APP_DEBUG setting

## Notes

- Error pages menggunakan `@extends('layouts.admin')` kecuali 503
- 503 standalone karena mungkin database/cache tidak available
- Semua error pages responsive dan mobile-friendly
- Animated backgrounds untuk UX yang lebih baik
- Support untuk dark mode (jika diimplementasikan di layout)
