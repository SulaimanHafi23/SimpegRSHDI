<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Position extends Model
{
    use HasUuid;

    protected $fillable = [
        'name',
        'description',
        'has_shift',
    ];

    protected $casts = [
        'has_shift' => 'boolean',
    ];

    public function workers(): HasMany
    {
        return $this->hasMany(Worker::class);
    }

    public function shifts(): BelongsToMany
    {
        return $this->belongsToMany(Shift::class, 'position_shift')
            ->withPivot('is_active')
            ->withTimestamps();
    }

    public function keperluanBerkas(): HasMany
    {
        return $this->hasMany(KeperluanBerkas::class);
    }
}
