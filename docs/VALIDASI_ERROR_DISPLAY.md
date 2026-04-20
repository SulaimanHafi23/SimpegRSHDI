# Konfigurasi Tampilan Error Validasi

## Arsitektur Validasi

Sistem validasi form menggunakan pendekatan **2-tier** untuk menampilkan error:

### 1. Field-Level Errors (Error di Bawah Field)
**Untuk:** Error validasi spesifik field
**Ditampilkan di:** Bawah setiap input field
**Komponen:** Semua form components (input, select, textarea, file, checkbox)

#### Cara Kerja:
```blade
{{-- Form component otomatis menampilkan error --}}
<x-form.input 
    name="email"
    label="Email"
    required />

{{-- Output jika ada error --}}
<!-- Field dengan border merah + pesan error di bawah -->
<input type="text" class="border-red-500" />
<p class="text-red-600">Email tidak valid</p>
```

**Error ditampilkan dari:**
- `$errors->first('fieldName')` - Laravel validation bag
- atau `error` prop jika di-pass langsung

### 2. General Errors (SweetAlert Modal)
**Untuk:** Error besar/general (bukan field validation)
**Ditampilkan di:** Modal SweetAlert
**Type:** success, error, warning, info

#### Cara Kerja:
```php
// Di Controller
return redirect()->back()
    ->with('error', 'Terjadi kesalahan sistem')  // ← Tampil di SweetAlert
    ->withErrors(['email' => 'Email sudah terdaftar']); // ← Tampil di field
```

## Komponen Form yang Mendukung Error Display

### 1. Input Component
```blade
<x-form.input 
    name="username"
    label="Username"
    type="text"
    error="Pesan error custom (opsional)" />
```

Menampilkan:
- Border merah jika ada error
- Pesan error di bawah field
- Focus state tetap biru

### 2. Select Component
```blade
<x-form.select 
    name="role_id"
    label="Role"
    :options="$roles"
    error="Pilih role yang valid" />
```

### 3. Textarea Component
```blade
<x-form.textarea 
    name="description"
    label="Deskripsi"
    rows="4"
    error="Deskripsi minimal 10 karakter" />
```

### 4. File Component
```blade
<x-form.file 
    name="attachment"
    label="File"
    accept=".pdf,.jpg,.png"
    error="File tidak valid" />
```

### 5. Checkbox Component
```blade
<x-form.checkbox 
    name="agree_terms"
    label="Saya setuju dengan syarat dan ketentuan"
    error="Anda harus menyetujui syarat dan ketentuan" />
```

## SweetAlert untuk General Error Saja

### Contoh: General Error (dari Session)
```php
// Di Controller - Terjadi error besar
return redirect()->route('home')
    ->with('error', 'Database connection failed');
    
// Akan menampilkan SweetAlert modal ✓
```

### Contoh: Field Validation Error (TIDAK pakai SweetAlert)
```php
// Di Controller
return redirect()->back()
    ->withErrors($validator);
    
// Field error ditampilkan di bawah field ✓
// BUKAN di SweetAlert modal
```

## Validasi di Controller

```php
class OrderController extends Controller
{
    public function store(Request $request)
    {
        // Field validation - error akan muncul di field
        $validated = $request->validate([
            'product_id' => 'required|exists:products',
            'quantity' => 'required|numeric|min:1',
            'email' => 'required|email'
        ]);
        
        try {
            // Simpan order
            $order = Order::create($validated);
            
            return redirect()->route('orders.show', $order)
                ->with('success', 'Order berhasil dibuat');
        } catch (Exception $e) {
            // General error - muncul di SweetAlert
            return redirect()->back()
                ->with('error', 'Gagal menyimpan order: ' . $e->getMessage());
        }
    }
}
```

## Validasi JavaScript (Client-Side)

Untuk validasi real-time sebelum submit form:

```javascript
// Validasi input email
document.getElementById('email').addEventListener('blur', function() {
    if (!this.value.includes('@')) {
        // Tampilkan pesan di bawah field (bukan modal)
        this.classList.add('border-red-500');
        this.parentElement.querySelector('.error-message').textContent = 'Format email tidak valid';
    }
});
```

## Custom Error Display

Jika perlu menampilkan error custom di field:

```blade
<x-form.input 
    name="username"
    label="Username"
    error="Username harus minimal 3 karakter dan hanya huruf/angka" />
```

## Ringkasan

| Type | Lokasi | Trigger | Component |
|------|--------|---------|-----------|
| Field Error | Bawah field | Validasi gagal | x-form.input/select/textarea/file/checkbox |
| General Error | Modal SweetAlert | Session `with('error')` | sweet-alert component |
| Success | Toast SweetAlert | Session `with('success')` | sweet-alert component |
| Warning | Toast SweetAlert | Session `with('warning')` | sweet-alert component |
| Info | Toast SweetAlert | Session `with('info')` | sweet-alert component |

## Files Terkait

- `resources/views/components/sweet-alert.blade.php` - SweetAlert helper functions
- `resources/views/components/form/input.blade.php` - Input field component
- `resources/views/components/form/select.blade.php` - Select field component
- `resources/views/components/form/textarea.blade.php` - Textarea field component
- `resources/views/components/form/file.blade.php` - File upload field component
- `resources/views/components/form/checkbox.blade.php` - Checkbox field component
