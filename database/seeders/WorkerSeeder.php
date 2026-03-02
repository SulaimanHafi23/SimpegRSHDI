<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Gender;
use App\Models\Religion;
use App\Models\Worker;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class WorkerSeeder extends Seeder
{
    public function run(): void
    {
        $csvPath = database_path('seeders/data/workers_seed.csv');

        if (!file_exists($csvPath)) {
            $this->command->error("❌ File tidak ditemukan: {$csvPath}");
            return;
        }

        $this->command->info('🏥 Importing workers from CSV...');

        $rows = $this->readCsv($csvPath);
        if (empty($rows)) {
            $this->command->warn('⚠️ CSV workers kosong.');
            return;
        }

        $created = 0;
        $updated = 0;

        $usedNips = Worker::pluck('nip')->map(fn ($value) => strtoupper((string) $value))->flip()->all();
        $usedEmails = Worker::pluck('email')->map(fn ($value) => strtolower((string) $value))->flip()->all();
        $usedPhones = Worker::pluck('phone_number')->map(fn ($value) => (string) $value)->flip()->all();

        foreach ($rows as $index => $row) {
            $name = trim((string) ($row['name'] ?? ''));
            if ($name === '') {
                $name = 'Pegawai ' . ($index + 1);
            }

            $genderName = $this->normalizeGender($row['gender'] ?? null);
            $religionName = $this->normalizeReligion($row['religion'] ?? null);
            $departmentName = $this->normalizeDepartment($row['department'] ?? null);

            $gender = Gender::firstOrCreate(['name' => $genderName], ['is_active' => true]);
            $religion = Religion::firstOrCreate(['name' => $religionName], ['is_active' => true]);
            $department = Department::firstOrCreate(
                ['name' => $departmentName],
                ['description' => 'Generated from worker seed data', 'is_active' => true]
            );

            $baseNip = $this->normalizeNip($row['nip'] ?? null);
            if ($baseNip === '') {
                $baseNip = 'AUTO' . str_pad((string) ($index + 1), 5, '0', STR_PAD_LEFT);
            }
            $nip = $this->ensureUnique($baseNip, $usedNips, true);

            $email = $this->normalizeEmail($row['email'] ?? null);
            if ($email === '') {
                $email = strtolower($nip) . '@example.com';
            }
            $email = $this->ensureUnique($email, $usedEmails, false);

            $phone = $this->normalizePhone($row['phone_number'] ?? null);
            if ($phone === '') {
                $phone = '08' . str_pad((string) ($index + 1), 10, '0', STR_PAD_LEFT);
            }
            $phone = $this->ensureUnique($phone, $usedPhones, true);

            $birthDate = $this->normalizeDate($row['birth_date'] ?? null, '1990-01-01');
            $hireDate = $this->normalizeDate($row['hire_date'] ?? null, '2025-01-01');

            $employmentStatus = in_array(($row['employment_status'] ?? ''), ['permanent', 'contract', 'internship'], true)
                ? $row['employment_status']
                : 'contract';

            $status = in_array(($row['status'] ?? ''), ['active', 'inactive', 'resigned'], true)
                ? $row['status']
                : 'active';

            $worker = Worker::where('nip', $nip)->first();

            $payload = [
                'nip' => $nip,
                'name' => $name,
                'email' => $email,
                'phone_number' => $phone,
                'address' => trim((string) ($row['address'] ?? '')),
                'birth_date' => $birthDate,
                'birth_place' => trim((string) ($row['birth_place'] ?? '-')) ?: '-',
                'gender_id' => $gender->id,
                'religion_id' => $religion->id,
                'department_id' => $department->id,
                'hire_date' => $hireDate,
                'employment_status' => $employmentStatus,
                'status' => $status,
            ];

            if ($worker) {
                $worker->update($payload);
                $updated++;
            } else {
                Worker::create($payload);
                $created++;
            }
        }

        $this->command->info("✅ Worker import complete. Created: {$created}, Updated: {$updated}");
    }

    private function readCsv(string $filePath): array
    {
        $rows = [];
        $handle = fopen($filePath, 'r');
        if (!$handle) {
            return $rows;
        }

        $headers = fgetcsv($handle);
        if (!$headers) {
            fclose($handle);
            return $rows;
        }

        $headers = array_map(fn ($header) => trim((string) $header), $headers);

        while (($data = fgetcsv($handle)) !== false) {
            if (count(array_filter($data, fn ($value) => trim((string) $value) !== '')) === 0) {
                continue;
            }

            $row = [];
            foreach ($headers as $i => $header) {
                $row[$header] = $data[$i] ?? null;
            }
            $rows[] = $row;
        }

        fclose($handle);
        return $rows;
    }

    private function normalizeNip(mixed $value): string
    {
        $raw = strtoupper(trim((string) $value));
        if ($raw === '' || in_array($raw, ['-', '—', '0', '0.0'], true)) {
            return '';
        }

        $raw = str_replace(["'", '‘', '’', '"'], '', $raw);
        $normalized = preg_replace('/[^A-Z0-9]/', '', $raw) ?: '';

        return substr($normalized, 0, 50);
    }

    private function normalizeEmail(mixed $value): string
    {
        $email = strtolower(trim((string) $value));
        return filter_var($email, FILTER_VALIDATE_EMAIL) ? $email : '';
    }

    private function normalizePhone(mixed $value): string
    {
        $phone = preg_replace('/\D+/', '', (string) $value) ?: '';
        if ($phone === '' || in_array($phone, ['0'], true)) {
            return '';
        }
        return substr($phone, 0, 20);
    }

    private function normalizeDate(mixed $value, string $fallback): string
    {
        $date = trim((string) $value);
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            return $date;
        }
        return $fallback;
    }

    private function normalizeGender(mixed $value): string
    {
        $gender = strtolower(trim((string) $value));
        return str_contains($gender, 'perempuan') ? 'Perempuan' : 'Laki-laki';
    }

    private function normalizeReligion(mixed $value): string
    {
        $religion = trim((string) $value);
        if ($religion === '') {
            return 'Islam';
        }
        return Str::title(strtolower($religion));
    }

    private function normalizeDepartment(mixed $value): string
    {
        $department = strtolower(trim((string) $value));
        if ($department === '') {
            return 'Administrasi';
        }

        if (str_contains($department, 'dokter')) {
            return 'Dokter';
        }

        if (str_contains($department, 'bidan')) {
            return 'Bidan';
        }

        if (str_contains($department, 'perawat')) {
            return 'Perawat';
        }

        return 'Administrasi';
    }

    private function ensureUnique(string $baseValue, array &$used, bool $upper = false): string
    {
        $candidate = $baseValue;
        $counter = 1;
        $key = $upper ? strtoupper($candidate) : strtolower($candidate);

        while (isset($used[$key])) {
            $counter++;
            $suffix = (string) $counter;
            $maxLength = 255 - strlen($suffix) - 1;
            $trimmed = substr($baseValue, 0, max(1, $maxLength));
            $candidate = $trimmed . '.' . $suffix;
            $key = $upper ? strtoupper($candidate) : strtolower($candidate);
        }

        $used[$key] = true;
        return $candidate;
    }
}
