<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Gender extends Model
{
    use HasUuid;

    protected $fillable = [
        'name',
    ];

    public function workers(): HasMany
    {
        return $this->hasMany(Worker::class);
    }
}
