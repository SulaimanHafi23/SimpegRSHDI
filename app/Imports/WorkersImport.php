<?php

namespace App\Imports;

use App\Models\Worker;
use App\Models\User;
use App\Models\Department;
use App\Models\Position;
use App\Models\Gender;
use App\Models\Religion;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class WorkersImport implements ToModel, WithHeadingRow, WithValidation, SkipsEmptyRows
{
    protected $errors = [];
    protected $successCount = 0;

    public function model(array $row)
    {
        try {
            // Check if worker already exists
            $existingWorker = Worker::where('nip', $row['nip'])->first();
            if ($existingWorker) {
                $this->errors[] = "NIP {$row['nip']} sudah ada dalam database";
                return null;
            }

            // Find related models
            $department = Department::where('name', 'LIKE', '%' . $row['departemen'] . '%')->first();
            $position = Position::where('name', 'LIKE', '%' . $row['jabatan'] . '%')->first();
            $gender = Gender::where('name', 'LIKE', '%' . $row['jenis_kelamin'] . '%')->first();
            $religion = Religion::where('name', 'LIKE', '%' . $row['agama'] . '%')->first();

            // Create user account
            $user = User::create([
                'name' => $row['nama_lengkap'],
                'email' => $row['email'],
                'password' => Hash::make($row['password'] ?? \Illuminate\Support\Str::random(16)),
            ]);

            // Assign Employee role
            $user->assignRole('Employee');

            // Create worker
            $worker = new Worker([
                'nip' => $row['nip'],
                'name' => $row['nama_lengkap'],
                'email' => $row['email'],
                'phone_number' => $row['nomor_telepon'] ?? null,
                'date_of_birth' => $this->parseDate($row['tanggal_lahir'] ?? null),
                'address' => $row['alamat'] ?? null,
                'department_id' => $department?->id,
                'position_id' => $position?->id,
                'gender_id' => $gender?->id,
                'religion_id' => $religion?->id,
                'employment_status' => $this->parseEmploymentStatus($row['status_kepegawaian'] ?? 'Kontrak'),
                'status' => 'active',
                'join_date' => $this->parseDate($row['tanggal_bergabung'] ?? now()),
                'user_id' => $user->id,
            ]);

            $worker->save();
            $this->successCount++;

            return $worker;
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

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function getSuccessCount(): int
    {
        return $this->successCount;
    }
}
