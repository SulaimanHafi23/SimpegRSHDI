# 📚 Dokumentasi Komponen Reusable

Sistem komponen reusable untuk SimpegRSHDI menggunakan Laravel Blade Components.

## 📂 Struktur Komponen

```
resources/views/components/
├── page-header.blade.php        # Header halaman dengan judul & actions
├── stats-card.blade.php         # Card statistik dengan ikon
├── card.blade.php               # Card container umum
├── alert.blade.php              # Alert messages
├── button.blade.php             # Button dengan berbagai variant
├── badge.blade.php              # Badge/label kecil
├── table.blade.php              # Tabel responsif
├── pagination.blade.php         # Pagination
├── filter-section.blade.php     # Section filter
├── modal.blade.php              # Modal dialog
├── dropdown.blade.php           # Dropdown menu
├── empty-state.blade.php        # Empty state placeholder
├── loading.blade.php            # Loading spinner
├── table/
│   ├── row.blade.php           # Table row
│   └── cell.blade.php          # Table cell
├── form/
│   ├── input.blade.php         # Input field
│   ├── select.blade.php        # Select dropdown
│   ├── textarea.blade.php      # Textarea
│   ├── checkbox.blade.php      # Checkbox
│   └── file.blade.php          # File upload
└── dropdown/
    ├── item.blade.php          # Dropdown item
    └── divider.blade.php       # Dropdown divider
```

## 🎯 Cara Penggunaan

### 1. Page Header

```blade
<x-page-header 
    title="Data Pegawai" 
    description="Kelola data seluruh pegawai"
    icon="fas fa-users">
    <x-slot:actions>
        <x-button variant="primary" icon="fas fa-plus">
            Tambah
        </x-button>
    </x-slot:actions>
</x-page-header>
```

### 2. Stats Card

```blade
<x-stats-card 
    title="Total Pegawai" 
    value="150" 
    icon="fas fa-users" 
    color="blue"
    trend="+12%"
    :trendUp="true" />
```

**Props:**
- `title` (required): Judul card
- `value` (required): Nilai/angka
- `icon` (required): Icon class (Font Awesome)
- `color`: blue, green, yellow, red, purple (default: blue)
- `trend`: Persentase perubahan
- `trendUp`: true/false untuk arah trend

### 3. Card

```blade
<x-card title="Judul Card">
    Konten card di sini...
</x-card>

{{-- Dengan custom header --}}
<x-card>
    <x-slot:header>
        <h3>Custom Header</h3>
    </x-slot:header>
    
    Konten...
    
    <x-slot:cardFooter>
        Footer content
    </x-slot:cardFooter>
</x-card>
```

**Props:**
- `title`: Judul card (optional)
- `noPadding`: true untuk menghilangkan padding
- `footer`: Footer text

### 4. Alert

```blade
<x-alert type="success">
    Data berhasil disimpan!
</x-alert>

<x-alert type="error" icon="fas fa-exclamation-triangle">
    Terjadi kesalahan!
</x-alert>
```

**Props:**
- `type`: success, error, warning, info
- `dismissible`: true/false (default: true)
- `icon`: Custom icon (optional)

### 5. Button

```blade
<x-button variant="primary" icon="fas fa-save">
    Simpan
</x-button>

<x-button 
    variant="danger" 
    size="sm" 
    icon="fas fa-trash"
    :loading="true">
    Hapus
</x-button>

<x-button variant="outline" icon="fas fa-times">
    Cancel
</x-button>
```

**Props:**
- `variant`: primary, secondary, success, danger, warning, info, outline, outline-primary, outline-secondary, outline-danger
- `size`: xs, sm, md, lg, xl
- `icon`: Icon class
- `iconPosition`: left, right
- `loading`: true/false
- `disabled`: true/false

### 6. Table

```blade
<x-table responsive striped hover>
    <x-slot:thead>
        <tr>
            <x-table.cell header>No</x-table.cell>
            <x-table.cell header>Nama</x-table.cell>
            <x-table.cell header>Aksi</x-table.cell>
        </tr>
    </x-slot:thead>

    @foreach($items as $item)
        <x-table.row>
            <x-table.cell>{{ $loop->iteration }}</x-table.cell>
            <x-table.cell>{{ $item->name }}</x-table.cell>
            <x-table.cell>
                <x-button size="sm">Edit</x-button>
            </x-table.cell>
        </x-table.row>
    @endforeach
</x-table>
```

### 7. Form Input

```blade
<x-form.input 
    name="name" 
    label="Nama Lengkap" 
    placeholder="Masukkan nama"
    required
    help="Nama sesuai KTP" />

<x-form.select 
    name="status" 
    label="Status"
    :options="['active' => 'Aktif', 'inactive' => 'Non-Aktif']"
    selected="active"
    required />

<x-form.textarea 
    name="description" 
    label="Deskripsi"
    rows="5" />

<x-form.checkbox 
    name="agree" 
    label="Setuju dengan syarat dan ketentuan" />

<x-form.file 
    name="photo" 
    label="Foto"
    accept="image/*"
    preview
    help="Maksimal 2MB" />
```

### 8. Filter Section

```blade
<x-filter-section action="{{ route('admin.workers.index') }}">
    <x-form.input 
        name="search" 
        label="Pencarian" 
        :value="request('search')" />
    
    <x-form.select 
        name="status" 
        label="Status"
        :selected="request('status')">
        <option value="">Semua</option>
        <option value="active">Aktif</option>
    </x-form.select>
</x-filter-section>
```

### 9. Badge

```blade
<x-badge variant="success" icon="fas fa-check">Aktif</x-badge>
<x-badge variant="danger" size="sm">Ditolak</x-badge>
<x-badge variant="warning">Pending</x-badge>
<x-badge variant="secondary">Cancelled</x-badge>
<x-badge variant="gray">Inactive</x-badge>
```

**Props:**
- `variant`: default, primary, success, danger, warning, info, secondary, gray, dark
- `size`: sm, md, lg
- `icon`: Icon class

### 10. Modal

```blade
<x-modal name="delete-modal" title="Konfirmasi Hapus" size="sm">
    <p>Apakah Anda yakin?</p>
    
    <x-slot:footer>
        <x-button @click="$dispatch('close-modal-delete-modal')">
            Batal
        </x-button>
        <x-button variant="danger">
            Hapus
        </x-button>
    </x-slot:footer>
</x-modal>

{{-- Trigger modal --}}
<x-button @click="$dispatch('open-modal-delete-modal')">
    Buka Modal
</x-button>
```

### 11. Dropdown

```blade
<x-dropdown align="right" width="48">
    <x-slot:trigger>
        <x-button icon="fas fa-ellipsis-v" />
    </x-slot:trigger>

    <x-dropdown.item icon="fas fa-eye" href="/detail">
        Detail
    </x-dropdown.item>
    <x-dropdown.item icon="fas fa-edit" href="/edit">
        Edit
    </x-dropdown.item>
    <x-dropdown.divider />
    <x-dropdown.item icon="fas fa-trash" onclick="confirmDelete()">
        Hapus
    </x-dropdown.item>
</x-dropdown>
```

### 12. Empty State

```blade
<x-empty-state 
    icon="fas fa-inbox" 
    title="Tidak ada data"
    description="Belum ada data yang tersedia"
    actionText="Tambah Data"
    :actionUrl="route('create')" />
```

### 13. Loading

```blade
<x-loading size="lg" color="blue" text="Memuat data..." />
```

### 14. Pagination

```blade
<x-pagination :paginator="$items" />
```

## 🎨 Best Practices

### 1. Konsistensi Warna

```blade
{{-- Gunakan variant yang sudah didefinisikan --}}
<x-button variant="primary">   {{-- Biru --}}
<x-button variant="success">   {{-- Hijau --}}
<x-button variant="danger">    {{-- Merah --}}
<x-button variant="warning">   {{-- Kuning --}}
```

### 2. Responsive Design

```blade
{{-- Gunakan grid yang responsif --}}
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
    <x-stats-card ... />
</div>
```

### 3. Loading State

```blade
<x-button :loading="$isLoading" variant="primary">
    Simpan
</x-button>
```

### 4. Error Handling

```blade
<x-form.input 
    name="email" 
    :error="$errors->first('email')" />
```

## 🔧 Customization

Semua komponen mendukung class tambahan:

```blade
<x-card class="mb-6 shadow-xl">
    ...
</x-card>

<x-button class="custom-class" variant="primary">
    Button
</x-button>
```

## 📝 Tips Penggunaan

1. **Selalu gunakan komponen** untuk konsistensi UI
2. **Manfaatkan slots** untuk konten dinamis
3. **Gunakan Alpine.js** untuk interaktivitas (modal, dropdown)
4. **Ikuti naming convention** yang sudah ada
5. **Dokumentasikan** komponen custom yang Anda buat

## 🚀 Migrasi dari View Lama

### Sebelum:
```blade
<div class="bg-white rounded-lg shadow-md p-6">
    <h3 class="text-lg font-bold mb-4">{{ $title }}</h3>
    {{ $content }}
</div>
```

### Sesudah:
```blade
<x-card title="{{ $title }}">
    {{ $content }}
</x-card>
```

## 📖 Contoh Lengkap

Lihat file berikut untuk contoh implementasi lengkap:
- `examples/workers-index-example.blade.php` - Halaman index dengan tabel
- `examples/workers-create-example.blade.php` - Form create/edit

---

**Note:** Pastikan Alpine.js sudah ter-install untuk fitur modal dan dropdown bekerja dengan baik.
