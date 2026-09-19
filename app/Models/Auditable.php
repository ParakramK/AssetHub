<?php

namespace App\Models;

use App\Enums\AuditAction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

/**
 * Attach to any business model to audit its lifecycle.
 *
 * Logs created / updated / deleted with redacted attribute diffs.
 * The AuditLog model itself must never use this trait.
 */
trait Auditable
{
    public static function bootAuditable(): void
    {
        static::created(function (Model $model) {
            AuditLog::record(
                $model,
                AuditAction::Created,
                null,
                AuditLog::redact($model, $model->getAttributes())
            );
        });

        static::updated(function (Model $model) {
            $changes = $model->getChanges();
            $changes = Arr::except($changes, ['updated_at']);

            if ($changes === []) {
                return;
            }

            AuditLog::record(
                $model,
                AuditAction::Updated,
                AuditLog::redact($model, Arr::only($model->getOriginal(), array_keys($changes))),
                AuditLog::redact($model, $changes)
            );
        });

        static::deleted(function (Model $model) {
            AuditLog::record(
                $model,
                AuditAction::Deleted,
                AuditLog::redact($model, $model->getOriginal()),
                null
            );
        });
    }
}
