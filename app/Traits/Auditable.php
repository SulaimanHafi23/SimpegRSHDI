<?php

namespace App\Traits;

use App\Models\AuditLog;

/**
 * Trait Auditable
 *
 * Automatically logs created, updated, and deleted events for the model.
 * Apply this trait to any model you want to track changes for.
 *
 * Usage: use App\Traits\Auditable; in your model class.
 *
 * You can customize which fields are excluded from audit by
 * defining `$auditExclude` on your model.
 */
trait Auditable
{
    /**
     * Boot the Auditable trait.
     */
    public static function bootAuditable(): void
    {
        // Log when a model is created
        static::created(function ($model) {
            if ($model->shouldAudit()) {
                $model->logAuditEvent('created', null, $model->getAuditableValues());
            }
        });

        // Log when a model is updated
        static::updated(function ($model) {
            if ($model->shouldAudit()) {
                $dirty = $model->getDirty();
                $cleanDirty = $model->filterAuditFields($dirty);

                if (!empty($cleanDirty)) {
                    $oldValues = [];
                    foreach (array_keys($cleanDirty) as $key) {
                        $oldValues[$key] = $model->getOriginal($key);
                    }

                    $model->logAuditEvent('updated', $oldValues, $cleanDirty);
                }
            }
        });

        // Log when a model is deleted (including soft delete)
        static::deleted(function ($model) {
            if ($model->shouldAudit()) {
                $model->logAuditEvent('deleted', $model->getAuditableValues(), null);
            }
        });
    }

    /**
     * Check if the model should be audited.
     */
    protected function shouldAudit(): bool
    {
        // Skip audit if running in console (seeders, migrations) unless explicitly enabled
        if (app()->runningInConsole() && !($this->auditInConsole ?? false)) {
            return false;
        }

        return true;
    }

    /**
     * Get the fields to exclude from auditing.
     */
    protected function getAuditExcludeFields(): array
    {
        return array_merge(
            $this->auditExclude ?? [],
            ['password', 'remember_token', 'updated_at', 'created_at', 'deleted_at']
        );
    }

    /**
     * Filter out excluded fields from the audit data.
     */
    protected function filterAuditFields(array $data): array
    {
        $exclude = $this->getAuditExcludeFields();
        return array_diff_key($data, array_flip($exclude));
    }

    /**
     * Get auditable values for this model.
     */
    protected function getAuditableValues(): array
    {
        $values = $this->attributesToArray();
        return $this->filterAuditFields($values);
    }

    /**
     * Generate a human-readable description for the audit event.
     */
    protected function getAuditDescription(string $action): string
    {
        $modelName = class_basename($this);
        $identifier = $this->getAuditIdentifier();

        return match ($action) {
            'created' => "{$modelName} \"{$identifier}\" telah dibuat",
            'updated' => "{$modelName} \"{$identifier}\" telah diubah",
            'deleted' => "{$modelName} \"{$identifier}\" telah dihapus",
            default => "{$action} pada {$modelName} \"{$identifier}\"",
        };
    }

    /**
     * Get a human-readable identifier for this model instance.
     */
    protected function getAuditIdentifier(): string
    {
        // Try common name fields
        if ($this->name) return $this->name;
        if ($this->title) return $this->title;
        if ($this->nip) return $this->nip;
        if ($this->email) return $this->email;

        return (string) $this->getKey();
    }

    /**
     * Log an audit event for this model.
     */
    protected function logAuditEvent(string $action, ?array $oldValues, ?array $newValues): void
    {
        try {
            AuditLog::log(
                action: $action,
                description: $this->getAuditDescription($action),
                auditable: $this,
                oldValues: $oldValues,
                newValues: $newValues,
            );
        } catch (\Exception $e) {
            // Don't break the application if audit logging fails
            \Log::warning('Audit log failed: ' . $e->getMessage());
        }
    }
}
