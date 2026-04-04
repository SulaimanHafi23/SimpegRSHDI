# Relasi Antar Tabel

- Database: `simpegrshdi`
- Sumber data relasi: `migrations`
- Tanggal generate: 2026-03-29 10:42:41
- Total tabel: 37
- Total foreign key: 0

## Legend
- Arah relasi: `child_table.child_fk -> parent_table.parent_pk`
- Jenis hubungan default FK: `Many-to-One` dari child ke parent (atau `One-to-Many` dari parent ke child)
- Hierarki level:
  - Level lebih kecil = tabel lebih hulu/fondasi
  - Level lebih besar = tabel lebih hilir/dependen

## Daftar Relasi (Arah + Jenis Hubungan)

| No | Child (dependen) | FK | Parent (referensi) | Arah | Hubungan | On Update | On Delete |
|---:|---|---|---|---|---|---|---|

## Hirarki Tabel (Berdasarkan Ketergantungan FK)

### Level 0
- `attendance_photos` (incoming FK: 0, outgoing FK: 0)
- `attendances` (incoming FK: 0, outgoing FK: 0)
- `audit_logs` (incoming FK: 0, outgoing FK: 0)
- `business_trips` (incoming FK: 0, outgoing FK: 0)
- `cache` (incoming FK: 0, outgoing FK: 0)
- `cache_locks` (incoming FK: 0, outgoing FK: 0)
- `department_document_type` (incoming FK: 0, outgoing FK: 0)
- `departments` (incoming FK: 0, outgoing FK: 0)
- `document_types` (incoming FK: 0, outgoing FK: 0)
- `failed_jobs` (incoming FK: 0, outgoing FK: 0)
- `genders` (incoming FK: 0, outgoing FK: 0)
- `holidays` (incoming FK: 0, outgoing FK: 0)
- `job_batches` (incoming FK: 0, outgoing FK: 0)
- `jobs` (incoming FK: 0, outgoing FK: 0)
- `leave_requests` (incoming FK: 0, outgoing FK: 0)
- `leave_types` (incoming FK: 0, outgoing FK: 0)
- `locations` (incoming FK: 0, outgoing FK: 0)
- `notifications` (incoming FK: 0, outgoing FK: 0)
- `password_reset_tokens` (incoming FK: 0, outgoing FK: 0)
- `personal_access_tokens` (incoming FK: 0, outgoing FK: 0)
- `religions` (incoming FK: 0, outgoing FK: 0)
- `sessions` (incoming FK: 0, outgoing FK: 0)
- `shift_day_times` (incoming FK: 0, outgoing FK: 0)
- `shift_overrides` (incoming FK: 0, outgoing FK: 0)
- `shift_swap_audit_logs` (incoming FK: 0, outgoing FK: 0)
- `shift_swap_overrides` (incoming FK: 0, outgoing FK: 0)
- `shift_swap_requests` (incoming FK: 0, outgoing FK: 0)
- `shifts` (incoming FK: 0, outgoing FK: 0)
- `users` (incoming FK: 0, outgoing FK: 0)
- `worker_documents` (incoming FK: 0, outgoing FK: 0)
- `worker_off_day_exceptions` (incoming FK: 0, outgoing FK: 0)
- `worker_off_days` (incoming FK: 0, outgoing FK: 0)
- `worker_shift_histories` (incoming FK: 0, outgoing FK: 0)
- `worker_shifts` (incoming FK: 0, outgoing FK: 0)
- `workers` (incoming FK: 0, outgoing FK: 0)

## Catatan
- Tabel tanpa FK masuk biasanya master/fondasi.
- Tabel dengan FK masuk tinggi biasanya tabel transaksi/audit/detail.
- Kalau ada relasi many-to-many, biasanya diwujudkan lewat tabel pivot (memiliki >=2 FK).
