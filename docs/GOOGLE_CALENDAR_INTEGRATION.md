# Integrasi Google Calendar - Panduan Setup

## Langkah-langkah untuk mengaktifkan Google Calendar Integration

### 1. Setup Google Cloud Project

1. Buka [Google Cloud Console](https://console.cloud.google.com/)
2. Buat project baru atau pilih project yang sudah ada
3. Aktifkan Google Calendar API:
   - Pergi ke "APIs & Services" > "Library"
   - Cari "Google Calendar API"
   - Klik "Enable"

### 2. Buat OAuth 2.0 Credentials

1. Pergi ke "APIs & Services" > "Credentials"
2. Klik "Create Credentials" > "OAuth 2.0 Client IDs"
3. Pilih "Web application"
4. Isi nama aplikasi
5. Tambahkan Authorized redirect URIs:
   - `http://localhost:8000/auth/google/callback` (untuk development)
   - `https://yourdomain.com/auth/google/callback` (untuk production)
6. Simpan Client ID dan Client Secret

### 3. Konfigurasi Environment

Tambahkan ke file `.env`:

```env
GOOGLE_CLIENT_ID=your-client-id.apps.googleusercontent.com
GOOGLE_CLIENT_SECRET=your-client-secret
GOOGLE_REDIRECT_URI="${APP_URL}/auth/google/callback"
GOOGLE_CALENDAR_ID=primary
```

### 4. Install Package

```bash
composer require google/apiclient:^2.15
```

### 5. Publish Config (Opsional)

Buat file `config/google.php`:

```php
<?php

return [
    'client_id' => env('GOOGLE_CLIENT_ID'),
    'client_secret' => env('GOOGLE_CLIENT_SECRET'),
    'redirect_uri' => env('GOOGLE_REDIRECT_URI'),
    'calendar_id' => env('GOOGLE_CALENDAR_ID', 'primary'),
    'scopes' => [
        \Google\Service\Calendar::CALENDAR_EVENTS,
        \Google\Service\Calendar::CALENDAR_READONLY,
    ],
];
```

## Fitur yang Akan Tersedia

Setelah integrasi aktif, sistem SIDIA (Sistem Informasi Darlan Ismail dan Absensi) dapat:

1. **Sinkronisasi Jadwal Shift** - Jadwal shift pegawai otomatis masuk ke Google Calendar
2. **Reminder Cuti** - Pengajuan cuti yang disetujui otomatis membuat event di calendar
3. **Reminder Lembur** - Jadwal lembur yang disetujui masuk ke calendar
4. **Notifikasi Perjalanan Dinas** - Event perjalanan dinas di calendar
5. **Hari Libur** - Sinkronisasi hari libur nasional dan cuti bersama

## Flow Autentikasi

1. Pegawai mengklik "Hubungkan Google Calendar" di dashboard
2. Sistem redirect ke Google OAuth
3. Pegawai memberikan izin akses calendar
4. Google mengembalikan token
5. Token disimpan di database untuk penggunaan selanjutnya

## Status

⚠️ **Fitur ini membutuhkan setup tambahan dari sisi Google Cloud Console**

Untuk mengaktifkan fitur ini, silakan hubungi administrator IT untuk:
1. Setup Google Cloud Project
2. Mendapatkan OAuth credentials
3. Mengkonfigurasi environment variables

## Catatan Keamanan

- Token akses disimpan terenkripsi di database
- Refresh token digunakan untuk memperpanjang akses
- User dapat disconnect kapan saja dari pengaturan akun
