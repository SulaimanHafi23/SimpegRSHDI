<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DocumentType extends Model
{
    use HasUuid;

    protected $fillable = [
        'name',
        'description',
        'file_format',
        'max_file_size',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function keperluanBerkas(): HasMany
    {
        return $this->hasMany(KeperluanBerkas::class);
    }

    public function getAllowedFormatsAttribute(): array
    {
        return explode(',', $this->file_format);
    }
}
