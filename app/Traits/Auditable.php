<?php

namespace App\Traits;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

trait Auditable
{
    /** @var array<string, mixed> */
    private array $auditOld = [];

    /** @var list<string> */
    private array $auditNewKeys = [];

    protected static function bootAuditable(): void
    {
        static::updating(static function (Model $model): void {
            /** @phpstan-ignore-next-line */
            $model->auditOld = array_intersect_key($model->getOriginal(), $model->getDirty());
            /** @phpstan-ignore-next-line */
            $model->auditNewKeys = array_keys($model->getDirty());
        });

        static::created(static function (Model $model): void {
            AuditLog::create([
                'user_id' => Auth::id(),
                'action' => 'created',
                'auditable_type' => $model->getMorphClass(),
                'auditable_id' => (string) $model->getKey(),
                'old_values' => null,
                'new_values' => $model->getAttributes(),
                'ip_address' => Request::ip(),
                'user_agent' => Request::userAgent(),
            ]);
        });

        static::updated(static function (Model $model): void {
            /** @phpstan-ignore-next-line */
            $newKeys = $model->auditNewKeys;

            if (empty($newKeys)) {
                return;
            }

            AuditLog::create([
                'user_id' => Auth::id(),
                'action' => 'updated',
                'auditable_type' => $model->getMorphClass(),
                'auditable_id' => (string) $model->getKey(),
                /** @phpstan-ignore-next-line */
                'old_values' => $model->auditOld,
                'new_values' => array_intersect_key($model->getAttributes(), array_flip($newKeys)),
                'ip_address' => Request::ip(),
                'user_agent' => Request::userAgent(),
            ]);
        });

        static::deleted(static function (Model $model): void {
            AuditLog::create([
                'user_id' => Auth::id(),
                'action' => 'deleted',
                'auditable_type' => $model->getMorphClass(),
                'auditable_id' => (string) $model->getKey(),
                'old_values' => $model->getOriginal(),
                'new_values' => null,
                'ip_address' => Request::ip(),
                'user_agent' => Request::userAgent(),
            ]);
        });
    }
}
