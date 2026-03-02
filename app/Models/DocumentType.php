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
    ];

    protected $casts = [
        'is_required' => 'boolean',
        'is_universal' => 'boolean',
        'is_active' => 'boolean',
    ];

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
