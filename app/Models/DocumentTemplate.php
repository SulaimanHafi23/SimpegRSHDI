<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class DocumentTemplate extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'document_type_id',
        'employment_category',
        'file_name',
        'file_path',
        'file_size',
        'mime_type',
        'description',
        'created_by',
        'updated_by',
        'is_active',
    ];

    protected $casts = [
        'file_size' => 'integer',
        'is_active' => 'boolean',
    ];

    public function documentType(): BelongsTo
    {
        return $this->belongsTo(DocumentType::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Scope: Get active templates
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: Get templates for a specific employment category
     */
    public function scopeForCategory($query, ?string $category)
    {
        if ($category === null) {
            return $query;
        }

        return $query->where(function ($q) use ($category) {
            $q->where('employment_category', $category)
                ->orWhereNull('employment_category'); // NULL = applicable to all
        });
    }

    /**
     * Get human-readable file size
     */
    public function getFileSizeHumanAttribute(): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $size = $this->file_size;
        $unit = 0;

        while ($size >= 1024 && $unit < count($units) - 1) {
            $size /= 1024;
            $unit++;
        }

        return round($size, 2) . ' ' . $units[$unit];
    }

    /**
     * Download URL for the template
     */
    public function getDownloadUrlAttribute(): string
    {
        return route('admin.document-templates.download', ['template' => $this->id]);
    }
}
