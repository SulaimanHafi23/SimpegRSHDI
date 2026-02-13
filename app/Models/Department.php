<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Department extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'name',
        'code',
        'description',
        'is_active',
        'requires_holiday_attendance',
        'parent_id',
        'manager_id',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'requires_holiday_attendance' => 'boolean',
    ];

    public function workers(): HasMany
    {
        return $this->hasMany(Worker::class);
    }

    /**
     * Get the parent department
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'parent_id');
    }

    /**
     * Get the child departments
     */
    public function children(): HasMany
    {
        return $this->hasMany(Department::class, 'parent_id');
    }

    /**
     * Get the department manager (user)
     */
    public function manager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'manager_id');
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
