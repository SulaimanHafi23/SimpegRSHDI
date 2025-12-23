<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Department extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'name',
        'code',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function workers(): HasMany
    {
        return $this->hasMany(Worker::class);
    }

    /**
     * Document types that apply to this department
     */
    public function documentTypes()
    {
        // Use the DepartmentDocumentType model which handles UUID generation
        return $this->belongsToMany(\App\Models\DocumentType::class, 'department_document_type', 'department_id', 'document_type_id')
                    ->using(\App\Models\DepartmentDocumentType::class)
                    ->withPivot('id')
                    ->withTimestamps();
    }
}
