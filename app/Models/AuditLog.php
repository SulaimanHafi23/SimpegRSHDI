<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class AuditLog extends Model
{
    use HasUuids;

    protected $fillable = [
        'user_id',
        'user_name',
        'action',
        'auditable_type',
        'auditable_id',
        'description',
        'old_values',
        'new_values',
        'ip_address',
        'user_agent',
        'url',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
    ];

    // ==================== RELATIONSHIPS ====================

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class)->withTrashed();
    }

    public function auditable(): MorphTo
    {
        return $this->morphTo();
    }

    // ==================== SCOPES ====================

    public function scopeForModel($query, string $modelClass, ?string $modelId = null)
    {
        $query->where('auditable_type', $modelClass);
        if ($modelId) {
            $query->where('auditable_id', $modelId);
        }
        return $query;
    }

    public function scopeByUser($query, string $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeByAction($query, string $action)
    {
        return $query->where('action', $action);
    }

    public function scopeRecent($query, int $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    // ==================== HELPERS ====================

    /**
     * Log an audit event.
     */
    public static function log(
        string $action,
        ?string $description = null,
        ?Model $auditable = null,
        ?array $oldValues = null,
        ?array $newValues = null
    ): self {
        $user = auth()->user();
        $request = request();

        return static::create([
            'user_id' => $user?->id,
            'user_name' => $user?->worker?->name ?? $user?->username ?? $user?->email ?? 'System',
            'action' => $action,
            'auditable_type' => $auditable ? get_class($auditable) : null,
            'auditable_id' => $auditable?->id,
            'description' => $description,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'url' => $request->fullUrl(),
        ]);
    }

    /**
     * Get the short model name for display.
     */
    public function getModelNameAttribute(): string
    {
        if (!$this->auditable_type) return '-';
        return class_basename($this->auditable_type);
    }

    /**
     * Get badge color for action type.
     */
    public function getActionBadgeAttribute(): array
    {
        return match ($this->action) {
            'created' => ['variant' => 'success', 'label' => 'Dibuat', 'icon' => 'fas fa-plus-circle'],
            'updated' => ['variant' => 'warning', 'label' => 'Diubah', 'icon' => 'fas fa-edit'],
            'deleted' => ['variant' => 'danger', 'label' => 'Dihapus', 'icon' => 'fas fa-trash'],
            'login' => ['variant' => 'primary', 'label' => 'Login', 'icon' => 'fas fa-sign-in-alt'],
            'logout' => ['variant' => 'secondary', 'label' => 'Logout', 'icon' => 'fas fa-sign-out-alt'],
            'imported' => ['variant' => 'info', 'label' => 'Import', 'icon' => 'fas fa-file-import'],
            'exported' => ['variant' => 'info', 'label' => 'Export', 'icon' => 'fas fa-file-export'],
            'approved' => ['variant' => 'success', 'label' => 'Disetujui', 'icon' => 'fas fa-check'],
            'rejected' => ['variant' => 'danger', 'label' => 'Ditolak', 'icon' => 'fas fa-times'],
            default => ['variant' => 'secondary', 'label' => ucfirst($this->action), 'icon' => 'fas fa-circle'],
        };
    }
}
