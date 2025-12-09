<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Berkas extends Model
{
    use HasUuid;

    protected $table = 'berkas';

    protected $fillable = [
        'worker_id',
        'keperluan_berkas_id',
        'file_name',
        'file_path',
        'file_type',
        'file_size',
        'status',
        'verified_by',
        'verified_at',
        'rejection_reason',
        'notes',
        'expired_date',
        'is_expired',
    ];

    protected $casts = [
        'verified_at' => 'datetime',
        'expired_date' => 'date',
        'is_expired' => 'boolean',
    ];

    public function worker(): BelongsTo
    {
        return $this->belongsTo(Worker::class);
    }

    public function FileRequirement(): BelongsTo
    {
        return $this->belongsTo(FileRequirment::class);
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function getFileSizeInKbAttribute(): float
    {
        return round($this->file_size / 1024, 2);
    }

    public function getFileSizeInMbAttribute(): float
    {
        return round($this->file_size / 1024 / 1024, 2);
    }
}
