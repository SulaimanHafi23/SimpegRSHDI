<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DocumentType extends Model
{
    use HasFactory, HasUuids, Auditable;

    protected $fillable = [
        'name',
        'description',
        'file_format',
        'max_file_size',
        'is_required',
        'is_universal',
        'is_active',
        'employment_category',
        'process_type',
        'expiration_buffer_days',
        'requirement_notes',
        'source_document_type_id',
    ];

    protected $casts = [
        'is_required' => 'boolean',
        'is_universal' => 'boolean',
        'is_active' => 'boolean',
        'expiration_buffer_days' => 'integer',
    ];

    public static function getEmploymentCategories(): array
    {
        return [
            'all' => 'Semua Pegawai',
            'asn' => 'ASN',
            'pppk' => 'PPPK',
            'pppk_paruh_waktu' => 'PPPK Paruh Waktu',
            'non_asn' => 'Non-ASN',
            'outsourced' => 'Outsourcing',
        ];
    }

    public static function getProcessTypes(): array
    {
        return [
            'onboarding' => 'Onboarding',
            'promotion' => 'Promosi',
            'payroll' => 'Payroll',
            'contract_extension' => 'Perpanjangan Kontrak',
        ];
    }

    public function getEmploymentCategoryLabelAttribute(): string
    {
        return self::getEmploymentCategories()[$this->employment_category] ?? ($this->employment_category ?: '-');
    }

    public function getProcessTypeLabelAttribute(): string
    {
        return self::getProcessTypes()[$this->process_type] ?? ($this->process_type ?: '-');
    }

    public function workerDocuments(): HasMany
    {
        return $this->hasMany(WorkerDocument::class);
    }

    /**
     * Departments that this document type applies to
     */
    public function departments()
    {
        return $this->belongsToMany(Department::class, 'department_document_type', 'document_type_id', 'department_id')->withTimestamps();
    }
}
