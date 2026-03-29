<?php

namespace App\Imports;

use App\Models\Worker;
use App\Models\User;
use App\Models\Department;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class WorkersImport implements ToModel, WithHeadingRow, WithValidation, SkipsEmptyRows
{
    protected $errors = [];
    protected $successCount = 0;

    public function model(array $row)
    {
        try {
            // Check if worker already exists by NIP
            $existingWorker = Worker::where('nip', $row['nip'])->first();
            if ($existingWorker) {
                $this->errors[] = "NIP {$row['nip']} sudah ada dalam database";
                return null;
            }

            // Check if email already exists
            $existingUser = User::where('email', $row['email'])->first();
            if ($existingUser) {
                $this->errors[] = "Email {$row['email']} sudah ada dalam database";
                return null;
            }

            // Find related models
            $department = !empty($row['departemen']) ? Department::where('name', 'LIKE', '%' . $row['departemen'] . '%')->first() : null;
            $gender = $this->normalizeGender($row['jenis_kelamin'] ?? null);
            $religion = $this->normalizeReligion($row['agama'] ?? null);

            return DB::transaction(function () use ($row, $department, $gender, $religion) {
                // Create worker first
                $worker = Worker::create([
                    'nip' => $row['nip'],
                    'name' => $row['nama_lengkap'],
                    'email' => $row['email'],
                    'phone_number' => $row['nomor_telepon'] ?? null,
                    'birth_date' => $this->parseDate($row['tanggal_lahir'] ?? null),
                    'address' => $row['alamat'] ?? null,
                    'department_id' => $department?->id,
                    'gender' => $gender,
                    'religion' => $religion,
                    'employment_status' => $this->parseEmploymentStatus($row['status_kepegawaian'] ?? 'Kontrak'),
                    'status' => 'active',
                    'hire_date' => $this->parseDate($row['tanggal_bergabung'] ?? now()),
                ]);

                // Create user account linked to worker
                $user = User::create([
                    'worker_id' => $worker->id,
                    'email' => $row['email'],
                    'username' => $row['nip'],
                    'password' => Hash::make($row['password'] ?? 'password123'),
                    'is_active' => true,
                ]);

                // Assign Employee role
                $user->assignRole('Employee');

                $this->successCount++;

                return $worker;
            });
        } catch (\Exception $e) {
            $this->errors[] = "Error pada baris NIP {$row['nip']}: " . $e->getMessage();
            return null;
        }
    }

    public function rules(): array
    {
        return [
            'nip' => 'required|string|max:50',
            'nama_lengkap' => 'required|string|max:255',
            'email' => 'required|email|max:255',
        ];
    }

    protected function parseDate($date)
    {
        if (empty($date)) return null;

        try {
            // Try different date formats
            if (is_numeric($date)) {
                // Excel date number
                return \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($date);
            }

            return \Carbon\Carbon::parse($date);
        } catch (\Exception $e) {
            return null;
        }
    }

    protected function parseEmploymentStatus($status)
    {
        $status = strtolower(trim($status));
        return match(true) {
            str_contains($status, 'tetap') => 'permanent',
            str_contains($status, 'kontrak') => 'contract',
            str_contains($status, 'percobaan') => 'probation',
            str_contains($status, 'magang') => 'intern',
            default => 'contract'
        };
    }

    private function normalizeGender($value): string
    {
        $gender = strtolower(trim((string) $value));
        if (str_contains($gender, 'perempuan') || str_contains($gender, 'wanita')) {
            return 'Perempuan';
        }

        return 'Laki-laki';
    }

    private function normalizeReligion($value): string
    {
        $religion = strtolower(trim((string) $value));
        if ($religion === '') {
            return 'Islam';
        }

        return match (true) {
            str_contains($religion, 'kristen') && str_contains($religion, 'katolik') => 'Katolik',
            str_contains($religion, 'katolik') => 'Katolik',
            str_contains($religion, 'kristen') => 'Kristen',
            str_contains($religion, 'hindu') => 'Hindu',
            str_contains($religion, 'buddha') => 'Buddha',
            str_contains($religion, 'konghucu') => 'Konghucu',
            default => 'Islam',
        };
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function getSuccessCount(): int
    {
        return $this->successCount;
    }
}
