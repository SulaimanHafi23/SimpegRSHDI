<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FileRequirement extends Model
{
    use HasUuid;

    protected $fillable = [
        'position_id',
        'document_type_id',
        'is_required',
        'is_active',
        'sort_order',
        'notes',
    ];

    protected $casts = [
        'is_required' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function position(): BelongsTo
    {
        return $this->belongsTo(Position::class);
    }

    public function documentType(): BelongsTo
    {
        return $this->belongsTo(DocumentType::class);
    }

    public function berkas(): HasMany
    {
        return $this->hasMany(Berkas::class);
    }
}
