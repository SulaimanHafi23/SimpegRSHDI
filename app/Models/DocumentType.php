<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DocumentType extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'name',
        'description',
        'file_format',
        'max_file_size',
        'description',
        'is_required',
        'is_active',
    ];

    protected $casts = [
        'is_required' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function workerDocuments(): HasMany
    {
        return $this->hasMany(WorkerDocument::class);
    }
}
