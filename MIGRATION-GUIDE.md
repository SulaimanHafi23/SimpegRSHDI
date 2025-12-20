# 🔄 Panduan Migrasi ke Komponen Reusable

## 📋 Daftar Isi
1. [Persiapan](#persiapan)
2. [Migrasi Step-by-Step](#migrasi-step-by-step)
3. [Contoh Konversi](#contoh-konversi)
4. [Checklist Migrasi](#checklist-migrasi)

## Persiapan

### 1. Install Alpine.js (Jika Belum)

Tambahkan di `resources/views/layouts/admin.blade.php` sebelum `</head>`:

```blade
{{-- Alpine.js for interactive components --}}
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
```

### 2. Verifikasi Font Awesome

Pastikan Font Awesome sudah ter-load di layout:

```blade
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
```

## Migrasi Step-by-Step

### Step 1: Identifikasi Pattern Umum

Cari pattern HTML yang sering diulang:
- Header halaman
- Card/box container
- Form input
- Tabel
- Button
- Alert/notification

### Step 2: Mulai dari Komponen Sederhana

#### ❌ Sebelum (HTML Manual):
```blade
<div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">Data Pegawai</h1>
        <p class="text-gray-600 mt-1">Kelola data seluruh pegawai</p>
    </div>
    <a href="{{ route('admin.workers.create') }}" 
       class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg">
        Tambah
    </a>
</div>
```

#### ✅ Sesudah (Komponen):
```blade
<x-page-header 
    title="Data Pegawai" 
    description="Kelola data seluruh pegawai">
    <x-slot:actions>
        <x-button variant="success" icon="fas fa-plus" 
            onclick="window.location.href='{{ route('admin.workers.create') }}'">
            Tambah
        </x-button>
    </x-slot:actions>
</x-page-header>
```

### Step 3: Konversi Alert Messages

#### ❌ Sebelum:
```blade
@if(session('success'))
    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4">
        <span class="block sm:inline">{{ session('success') }}</span>
    </div>
@endif
```

#### ✅ Sesudah:
```blade
@if(session('success'))
    <x-alert type="success">
        {{ session('success') }}
    </x-alert>
@endif
```

### Step 4: Konversi Form Filter

#### ❌ Sebelum:
```blade
<div class="bg-white rounded-lg shadow-md p-6 mb-6">
    <form method="GET" action="{{ route('admin.workers.index') }}">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Pencarian</label>
                <input type="text" name="search" class="w-full px-4 py-2 border rounded-md">
            </div>
            <!-- ... fields lainnya ... -->
        </div>
        <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-md">
            Filter
        </button>
    </form>
</div>
```

#### ✅ Sesudah:
```blade
<x-filter-section action="{{ route('admin.workers.index') }}">
    <x-form.input 
        name="search" 
        label="Pencarian" 
        :value="request('search')" />
    
    {{-- Fields lainnya --}}
</x-filter-section>
```

### Step 5: Konversi Tabel

#### ❌ Sebelum:
```blade
<div class="bg-white rounded-lg shadow-md overflow-hidden">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">No</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nama</th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            @foreach($items as $item)
            <tr class="hover:bg-gray-50">
                <td class="px-6 py-4 whitespace-nowrap">{{ $loop->iteration }}</td>
                <td class="px-6 py-4 whitespace-nowrap">{{ $item->name }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
```

#### ✅ Sesudah:
```blade
<x-card title="Daftar Data" :no-padding="true">
    <x-table responsive striped hover>
        <x-slot:thead>
            <tr>
                <x-table.cell header>No</x-table.cell>
                <x-table.cell header>Nama</x-table.cell>
            </tr>
        </x-slot:thead>

        @foreach($items as $item)
            <x-table.row>
                <x-table.cell>{{ $loop->iteration }}</x-table.cell>
                <x-table.cell>{{ $item->name }}</x-table.cell>
            </x-table.row>
        @endforeach
    </x-table>
</x-card>
```

### Step 6: Konversi Form Input

#### ❌ Sebelum:
```blade
<div>
    <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
        Nama Lengkap <span class="text-red-500">*</span>
    </label>
    <input type="text" 
           name="name" 
           id="name"
           value="{{ old('name') }}"
           required
           class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-blue-500">
    @error('name')
        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>
```

#### ✅ Sesudah:
```blade
<x-form.input 
    name="name" 
    label="Nama Lengkap" 
    required />
```

### Step 7: Konversi Button Actions

#### ❌ Sebelum:
```blade
<a href="{{ route('admin.workers.edit', $worker->id) }}" 
   class="inline-flex items-center px-3 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-md">
    <i class="fas fa-edit mr-2"></i>
    Edit
</a>
```

#### ✅ Sesudah:
```blade
<x-button 
    variant="primary" 
    icon="fas fa-edit"
    onclick="window.location.href='{{ route('admin.workers.edit', $worker->id) }}'">
    Edit
</x-button>
```

### Step 8: Konversi Dropdown Menu

#### ❌ Sebelum:
```blade
<div class="relative inline-block text-left">
    <button class="...">Menu</button>
    <div class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg">
        <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
            Action 1
        </a>
    </div>
</div>
```

#### ✅ Sesudah:
```blade
<x-dropdown align="right" width="48">
    <x-slot:trigger>
        <x-button icon="fas fa-ellipsis-v" />
    </x-slot:trigger>

    <x-dropdown.item icon="fas fa-edit" href="#">
        Action 1
    </x-dropdown.item>
</x-dropdown>
```

### Step 9: Konversi Badge/Status

#### ❌ Sebelum:
```blade
@if($item->status === 'active')
    <span class="inline-flex items-center px-2.5 py-1 text-sm font-medium rounded-full bg-green-100 text-green-800">
        <i class="fas fa-check-circle mr-1"></i> Aktif
    </span>
@endif
```

#### ✅ Sesudah:
```blade
@if($item->status === 'active')
    <x-badge variant="success" icon="fas fa-check-circle">Aktif</x-badge>
@endif
```

### Step 10: Konversi Pagination

#### ❌ Sebelum:
```blade
<div class="flex items-center justify-between">
    {{ $items->links() }}
</div>
```

#### ✅ Sesudah:
```blade
<x-pagination :paginator="$items" />
```

## Contoh Konversi Lengkap

### File: `admin/workers/index.blade.php`

#### Struktur Lama (200+ baris):
```blade
@extends('layouts.admin')

@section('content')
<div class="container mx-auto px-4 py-6">
    <!-- Header (30 baris HTML) -->
    <!-- Alerts (20 baris HTML) -->
    <!-- Filter (60 baris HTML) -->
    <!-- Table (80 baris HTML) -->
    <!-- Pagination (10 baris HTML) -->
</div>
@endsection
```

#### Struktur Baru (80-100 baris):
```blade
@extends('layouts.admin')

@section('content')
<div class="space-y-6">
    <x-page-header title="..." description="...">
        <x-slot:actions>...</x-slot:actions>
    </x-page-header>

    <x-alert type="success">...</x-alert>

    <x-filter-section action="...">...</x-filter-section>

    <x-card title="..." :no-padding="true">
        <x-table>...</x-table>
        <x-slot:cardFooter>
            <x-pagination :paginator="$items" />
        </x-slot:cardFooter>
    </x-card>
</div>
@endsection
```

**Pengurangan**: ~50% kode, lebih mudah dibaca & maintain!

## Checklist Migrasi

### Per File View:

- [ ] Page Header → `<x-page-header>`
- [ ] Alerts → `<x-alert>`
- [ ] Filter Form → `<x-filter-section>`
- [ ] Input Fields → `<x-form.input>`, `<x-form.select>`, dll
- [ ] Tables → `<x-table>` & `<x-table.row>`
- [ ] Buttons → `<x-button>`
- [ ] Badges → `<x-badge>`
- [ ] Dropdowns → `<x-dropdown>`
- [ ] Pagination → `<x-pagination>`
- [ ] Empty State → `<x-empty-state>`
- [ ] Modals → `<x-modal>`

### Per Modul:

- [ ] Workers (index, create, edit, show)
- [ ] Users (index, create, edit, show)
- [ ] Roles (index, create, edit)
- [ ] Attendance (index, create, show)
- [ ] Schedules (index, create, edit)
- [ ] Leave Requests (index, create, edit, show)
- [ ] Overtime (index, create, edit, show)
- [ ] Master Data (departments, locations, shifts, dll)
- [ ] Reports
- [ ] Dashboard

## Tips Migrasi

### 1. Migrasi Bertahap
Mulai dari 1 modul (misalnya Workers), lalu replikasi ke modul lain.

### 2. Gunakan Find & Replace
Untuk pattern yang sangat mirip, gunakan VSCode find & replace dengan regex.

### 3. Test Setiap Perubahan
Jangan migrasi semua file sekaligus. Test per file untuk memastikan tidak ada bug.

### 4. Dokumentasi
Update dokumentasi jika ada custom komponen yang Anda buat.

### 5. Code Review
Minta tim review hasil migrasi untuk memastikan konsistensi.

## Troubleshooting

### Problem: Alpine.js tidak bekerja
**Solusi**: Pastikan Alpine.js sudah di-load di layout:
```blade
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
```

### Problem: Komponen tidak ditemukan
**Solusi**: Pastikan file komponen ada di `resources/views/components/`

### Problem: Styling tidak sesuai
**Solusi**: Cek apakah Tailwind CSS sudah di-compile dengan benar:
```bash
npm run dev
```

### Problem: Modal tidak muncul
**Solusi**: 
1. Pastikan Alpine.js loaded
2. Gunakan event dispatcher yang benar:
```blade
<x-button @click="$dispatch('open-modal-nama-modal')">
    Buka Modal
</x-button>
```

## Estimasi Waktu Migrasi

| Jenis View | Waktu Estimasi | Kompleksitas |
|------------|---------------|--------------|
| Index (List) | 30-45 menit | Sedang |
| Create/Edit Form | 45-60 menit | Sedang |
| Show (Detail) | 20-30 menit | Mudah |
| Dashboard | 60-90 menit | Tinggi |
| Reports | 30-45 menit | Sedang |

**Total Estimasi untuk seluruh aplikasi**: 2-3 hari kerja

## Hasil yang Diharapkan

✅ Kode lebih ringkas (50-60% pengurangan)
✅ Konsistensi UI di seluruh aplikasi
✅ Maintenance lebih mudah
✅ Development lebih cepat
✅ Bug lebih sedikit
✅ Dokumentasi lebih baik

## Resources

- [Dokumentasi Komponen](./COMPONENTS-GUIDE.md)
- [Contoh Workers Index](./resources/views/examples/workers-index-example.blade.php)
- [Contoh Workers Create](./resources/views/examples/workers-create-example.blade.php)
- [Contoh Users Index Refactored](./resources/views/examples/users-index-refactored.blade.php)

---

**Happy Refactoring! 🚀**
