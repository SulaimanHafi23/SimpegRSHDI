<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkerDocument extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'worker_id',
        'document_type_id',
        'file_name',
        'file_path',
        'file_size',
        'expired_date',
        'status',
        'verified_by',
        'verified_at',
        'notes',
    ];

    protected $casts = [
        'file_size' => 'integer',
        'expired_date' => 'date',
        'verified_at' => 'datetime',
    ];

    public function worker(): BelongsTo
    {
        return $this->belongsTo(Worker::class);
    }

    public function documentType(): BelongsTo
    {
        return $this->belongsTo(DocumentType::class);
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    /**
     * Check if document is expired
     */
    public function isExpired(): bool
    {
        if (!$this->expired_date) {
            return false;
        }

        return $this->expired_date < now();
    }

    /**
     * Get file size in human readable format
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
}
