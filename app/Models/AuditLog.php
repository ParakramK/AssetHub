<?php

namespace App\Models;

use App\Enums\AuditAction;
use Database\Factories\AuditLogFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * Immutable record of who changed what, and who revealed a secret.
 *
 * Secrets (passwords, private keys, tokens) are never stored here:
 * see AuditLog::redact().
 *
 * @property string $id
 * @property string|null $user_id
 * @property string $action
 * @property string $auditable_type
 * @property string $auditable_id
 * @property array<string, mixed>|null $old_values
 * @property array<string, mixed>|null $new_values
 * @property string|null $ip_address
 * @property string|null $user_agent
 *
 * @method static AuditLogFactory factory($count = null, $state = [])
 *
 * @mixin \Eloquent
 */
class AuditLog extends Model
{
    /** @use HasFactory<AuditLogFactory> */
    use HasFactory, HasUuids;

    protected $fillable = [
        'user_id',
        'action',
        'auditable_type',
        'auditable_id',
        'old_values',
        'new_values',
        'ip_address',
        'user_agent',
    ];

    protected function casts(): array
    {
        return [
            'old_values' => 'array',
            'new_values' => 'array',
        ];
    }

    public function newUniqueId(): string
    {
        return (string) Str::uuid7();
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * @return MorphTo<Model, $this>
     */
    public function auditable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Record an audit entry. Never throws: auditing must not break the
     * action being audited, and must be a no-op before the table exists
     * (e.g. while running the very migration that creates it).
     */
    public static function record(
        Model $auditable,
        AuditAction|string $action,
        ?array $oldValues = null,
        ?array $newValues = null,
    ): ?self {
        if ($auditable instanceof self) {
            return null;
        }

        if (! Schema::hasTable('audit_logs')) {
            return null;
        }

        $request = request();

        return self::query()->create([
            'user_id' => Auth::id(),
            'action' => $action instanceof AuditAction ? $action->value : $action,
            'auditable_type' => $auditable::class,
            'auditable_id' => (string) $auditable->getKey(),
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => $request?->ip(),
            'user_agent' => $request !== null ? Str::limit((string) $request->userAgent(), 1000) : null,
        ]);
    }

    /**
     * Strip secrets and framework noise from attribute arrays before storage.
     *
     * @param  array<string, mixed>  $attributes
     * @return array<string, mixed>
     */
    public static function redact(Model $model, array $attributes): array
    {
        $sensitive = array_unique([
            ...$model->getHidden(),
            'password',
            'private_key',
            'remember_token',
        ]);

        return Arr::except($attributes, $sensitive);
    }
}
