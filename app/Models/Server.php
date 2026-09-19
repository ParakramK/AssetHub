<?php

namespace App\Models;

use App\Enums\ServerType;
use Database\Factories\ServerFactory;
use Eloquent;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * @property string $id
 * @property string|null $created_by
 * @property string $company_id
 * @property string $name
 * @property ServerType $type
 * @property string $ip_address
 * @property int|null $port
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read User|null $creator
 * @property-read Company $company
 * @property-read Collection<int, ServerCredential> $credentials
 * @property-read Collection<int, SshKey> $sshKeys
 *
 * @method static ServerFactory factory($count = null, $state = [])
 *
 * @mixin Eloquent
 */
class Server extends Model
{
    /** @use HasFactory<ServerFactory> */
    use Auditable, HasFactory, HasUuids;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'created_by',
        'company_id',
        'name',
        'type',
        'ip_address',
        'port',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => ServerType::class,
            'port' => 'integer',
        ];
    }

    public function newUniqueId(): string
    {
        return (string) Str::uuid7();
    }

    protected static function booted(): void
    {
        static::creating(function (Server $server) {
            if ($server->port === null && $server->type instanceof ServerType) {
                $server->port = $server->type->defaultPort();
            }
        });
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * @return BelongsTo<Company, $this>
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * @return HasMany<ServerCredential, $this>
     */
    public function credentials(): HasMany
    {
        return $this->hasMany(ServerCredential::class);
    }

    /**
     * @return HasMany<SshKey, $this>
     */
    public function sshKeys(): HasMany
    {
        return $this->hasMany(SshKey::class);
    }
}
