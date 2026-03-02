<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DepartmentDocumentType extends Pivot
{
    use HasFactory, HasUuids, Auditable;

    protected $table = 'department_document_type';
    
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'department_id',
        'document_type_id',
    ];

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function documentType(): BelongsTo
    {
        return $this->belongsTo(DocumentType::class, 'document_type_id');
    }
}
