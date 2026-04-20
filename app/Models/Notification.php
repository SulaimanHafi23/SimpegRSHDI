<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Notification extends Model
{
    use HasFactory, HasUuids;

    protected static function booted(): void
    {
        static::creating(function (self $notification) {
            // Backward compatibility for legacy payloads that only send user_id.
            if (empty($notification->notifiable_type) && !empty($notification->user_id)) {
                $notification->notifiable_type = User::class;
            }

            if (empty($notification->notifiable_id) && !empty($notification->user_id)) {
                $notification->notifiable_id = $notification->user_id;
            }

            // Keep user_id aligned for code paths that still read by user_id.
            if (empty($notification->user_id)
                && $notification->notifiable_type === User::class
                && !empty($notification->notifiable_id)
            ) {
                $notification->user_id = $notification->notifiable_id;
            }
        });
    }

    protected $fillable = [
        'user_id',
        'notifiable_type',
        'notifiable_id',
        'type',
        'title',
        'message',
        'data',
        'read_at',
    ];

    protected $casts = [
        'data' => 'array',
        'read_at' => 'datetime',
    ];

    /**
     * Get the polymorphic notifiable entity
     */
    public function notifiable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the user that owns the notification
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if notification is read
     */
    public function isRead(): bool
    {
        return $this->read_at !== null;
    }

    /**
     * Mark notification as read
     */
    public function markAsRead(): void
    {
        if (!$this->isRead()) {
            $this->update(['read_at' => now()]);
        }
    }

    /**
     * Scope for unread notifications
     */
    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }

    /**
     * Scope for read notifications
     */
    public function scopeRead($query)
    {
        return $query->whereNotNull('read_at');
    }

    public function getTitleAttribute($value): string
    {
        if (!empty($value)) {
            return $value;
        }

        return $this->data['title'] ?? 'Notifikasi';
    }

    public function getMessageAttribute($value): string
    {
        if (!empty($value)) {
            return $value;
        }

        return $this->data['message'] ?? '-';
    }

    public function getIsReadAttribute(): bool
    {
        return $this->read_at !== null;
    }
}
